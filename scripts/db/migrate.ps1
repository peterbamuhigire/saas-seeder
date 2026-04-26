param(
    [string]$Database = $env:DB_NAME,
    [string]$User = $env:DB_USER,
    [string]$Password = $env:DB_PASSWORD,
    [string]$HostName = $(if ($env:DB_HOST) { $env:DB_HOST } else { "localhost" }),
    [switch]$WithSeeds
)

$ErrorActionPreference = "Stop"

if (-not $Database) { $Database = "saas_seeder" }
if (-not $User) { $User = "root" }

$mysql = Get-Command mysql -ErrorAction SilentlyContinue
if (-not $mysql) {
    $candidate = "C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe"
    if (Test-Path $candidate) {
        $mysql = Get-Item $candidate
    } else {
        throw "mysql client not found. Add MySQL bin to PATH or update scripts/db/migrate.ps1."
    }
}

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$files = @(Get-ChildItem (Join-Path $root "database\migrations") -Filter "*.sql" | Sort-Object Name)
if ($WithSeeds) {
    $files += @(Get-ChildItem (Join-Path $root "database\seeds") -Filter "*.sql" | Sort-Object Name)
}

foreach ($file in $files) {
    Write-Host "Applying $($file.Name)"
    $startedAt = Get-Date
    $args = @("-h", $HostName, "-u", $User, $Database)
    if ($Password) { $args = @("-h", $HostName, "-u", $User, "-p$Password", $Database) }
    Get-Content -Raw -LiteralPath $file.FullName | & $mysql.Source @args
    if ($LASTEXITCODE -ne 0) {
        throw "Migration failed: $($file.FullName)"
    }

    $executionMs = [int]((Get-Date) - $startedAt).TotalMilliseconds
    $hash = (Get-FileHash -Algorithm SHA256 -LiteralPath $file.FullName).Hash.ToLowerInvariant()
    $migrationId = $file.BaseName.Replace("'", "''")
    $ledgerSql = "INSERT INTO tbl_schema_migrations (migration_id, checksum, applied_by, execution_ms) VALUES ('$migrationId', '$hash', CURRENT_USER(), $executionMs) ON DUPLICATE KEY UPDATE checksum = VALUES(checksum), applied_at = CURRENT_TIMESTAMP, applied_by = VALUES(applied_by), execution_ms = VALUES(execution_ms);"
    & $mysql.Source @args "--execute=$ledgerSql"

    $auditTable = & $mysql.Source @args "--batch" "--skip-column-names" "--execute=SHOW TABLES LIKE 'tbl_audit_log';"
    if ($auditTable -match 'tbl_audit_log') {
        $auditSql = "INSERT INTO tbl_audit_log (action, entity_type, entity_id, details, created_at) VALUES ('migration.applied', 'schema_migration', NULL, JSON_OBJECT('migration_id', '$migrationId', 'checksum', '$hash', 'execution_ms', $executionMs, 'request_id', 'migration-$migrationId'), NOW());"
        & $mysql.Source @args "--execute=$auditSql"
    }
}
