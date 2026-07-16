param(
    [string]$Database,
    [string]$User,
    [string]$Password,
    [string]$HostName,
    [int]$Port,
    [switch]$WithSeeds
)

$ErrorActionPreference = "Stop"
$root = (Resolve-Path (Join-Path $PSScriptRoot "..\..")).Path
. (Join-Path $root "scripts\setup\import-dotenv.ps1")
Import-DotEnvFile -Path (Join-Path $root ".env")

if (-not $Database) { $Database = if ($env:DB_NAME) { $env:DB_NAME } else { "saas_seeder" } }
if (-not $User) { $User = if ($env:DB_USER) { $env:DB_USER } else { "root" } }
if (-not $Password) { $Password = $env:DB_PASSWORD }
if (-not $HostName) { $HostName = if ($env:DB_HOST) { $env:DB_HOST } else { "localhost" } }
if (-not $Port) { $Port = if ($env:DB_PORT) { [int]$env:DB_PORT } else { 3306 } }

$mysql = & (Join-Path $PSScriptRoot "find-mysql.ps1")
$connectionArgs = @("-h", $HostName, "-P", $Port, "-u", $User, "--default-character-set=utf8mb4")
if ($Password) { $connectionArgs += "-p$Password" }

$databaseIdentifier = $Database.Replace('`', '``')
& $mysql @connectionArgs "--execute=CREATE DATABASE IF NOT EXISTS ``$databaseIdentifier`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($LASTEXITCODE -ne 0) {
    throw "Could not create or connect to the configured database."
}

$databaseArgs = $connectionArgs + @($Database)
$files = @(Get-ChildItem (Join-Path $root "database\migrations") -Filter "*.sql" | Sort-Object Name)
if ($WithSeeds) {
    $files += @(Get-ChildItem (Join-Path $root "database\seeds") -Filter "*.sql" | Sort-Object Name)
}

foreach ($file in $files) {
    $hash = (Get-FileHash -Algorithm SHA256 -LiteralPath $file.FullName).Hash.ToLowerInvariant()
    $migrationId = $file.BaseName.Replace("'", "''")
    $existing = $null

    $ledgerExists = & $mysql @databaseArgs "--batch" "--skip-column-names" "--execute=SHOW TABLES LIKE 'tbl_schema_migrations';"
    if ($ledgerExists -match 'tbl_schema_migrations') {
        $existing = & $mysql @databaseArgs "--batch" "--skip-column-names" "--execute=SELECT checksum FROM tbl_schema_migrations WHERE migration_id = '$migrationId' LIMIT 1;"
    }

    if ($existing) {
        if ($existing.Trim() -ne $hash) {
            throw "Applied migration checksum differs from disk: $($file.Name)"
        }

        Write-Host "Already applied: $($file.Name)" -ForegroundColor DarkGray
        continue
    }

    Write-Host "Applying: $($file.Name)" -ForegroundColor Cyan
    $startedAt = Get-Date
    Get-Content -Raw -LiteralPath $file.FullName | & $mysql @databaseArgs
    if ($LASTEXITCODE -ne 0) {
        throw "Migration failed: $($file.FullName)"
    }

    $executionMs = [int]((Get-Date) - $startedAt).TotalMilliseconds
    $ledgerSql = "INSERT INTO tbl_schema_migrations (migration_id, checksum, applied_by, execution_ms) VALUES ('$migrationId', '$hash', CURRENT_USER(), $executionMs);"
    & $mysql @databaseArgs "--execute=$ledgerSql"
    if ($LASTEXITCODE -ne 0) {
        throw "Could not record migration in the schema ledger: $($file.Name)"
    }
}

Write-Host "Database is current: $Database" -ForegroundColor Green
