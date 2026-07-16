$ErrorActionPreference = "Stop"
$php = & "$PSScriptRoot\find-php.ps1"
$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$phpunit = Join-Path $root "vendor\bin\phpunit"
if (-not (Test-Path -LiteralPath $phpunit) -and -not (Test-Path -LiteralPath "$phpunit.bat")) {
    throw "PHPUnit is required but vendor/bin/phpunit is missing. Run .\scripts\setup\install-dependencies.ps1."
}

& $php $phpunit --colors=never
exit $LASTEXITCODE
