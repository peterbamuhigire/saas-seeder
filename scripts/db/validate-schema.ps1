param(
    [string]$Database = $env:DB_NAME,
    [string]$User = $env:DB_USER,
    [string]$Password = $env:DB_PASSWORD,
    [string]$HostName = $(if ($env:DB_HOST) { $env:DB_HOST } else { "localhost" })
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
        throw "mysql client not found."
    }
}

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$checks = Join-Path $root "database\schema\checks.sql"
$args = @("-h", $HostName, "-u", $User, $Database)
if ($Password) { $args = @("-h", $HostName, "-u", $User, "-p$Password", $Database) }

Get-Content -Raw -LiteralPath $checks | & $mysql.Source @args
if ($LASTEXITCODE -ne 0) {
    throw "Schema validation failed."
}
