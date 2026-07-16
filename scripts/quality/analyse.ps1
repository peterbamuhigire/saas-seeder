$ErrorActionPreference = "Stop"
$php = & "$PSScriptRoot\find-php.ps1"
$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$phpstan = Join-Path $root "vendor\bin\phpstan"
if (-not (Test-Path $phpstan) -and -not (Test-Path "$phpstan.bat")) {
    throw "PHPStan is required but vendor/bin/phpstan is missing. Run .\scripts\setup\install-dependencies.ps1."
}
& $php "$phpstan" analyse --memory-limit=512M
exit $LASTEXITCODE
