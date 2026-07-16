param(
    [string]$Database,
    [string]$User,
    [string]$Password,
    [string]$HostName,
    [int]$Port
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
$args = @("-h", $HostName, "-P", $Port, "-u", $User, "--default-character-set=utf8mb4", $Database)
if ($Password) { $args = @("-h", $HostName, "-P", $Port, "-u", $User, "-p$Password", "--default-character-set=utf8mb4", $Database) }

$issues = Get-Content -Raw -LiteralPath (Join-Path $root "database\schema\checks.sql") | & $mysql @args --batch --skip-column-names
if ($LASTEXITCODE -ne 0) {
    throw "Schema validation query failed."
}
if ($issues) {
    throw "Schema validation failed:`n$($issues -join "`n")"
}

$migrationCount = & $mysql @args --batch --skip-column-names "--execute=SELECT COUNT(*) FROM tbl_schema_migrations;"
$tableCount = & $mysql @args --batch --skip-column-names "--execute=SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE();"
Write-Host "Schema valid: $tableCount tables, $migrationCount recorded migrations." -ForegroundColor Green
