$ErrorActionPreference = "Stop"
$php = & "$PSScriptRoot\find-php.ps1"
$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$phpstan = Join-Path $root "vendor\bin\phpstan"
if (-not (Test-Path $phpstan) -and -not (Test-Path "$phpstan.bat")) {
    Write-Host "PHPStan is not installed; run composer install/update to enable static analysis."
    exit 0
}
& $php "$phpstan" analyse --memory-limit=512M
exit $LASTEXITCODE
