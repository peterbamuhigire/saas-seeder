$ErrorActionPreference = "Stop"

$php = & "$PSScriptRoot\find-php.ps1"
$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$fixer = Join-Path $root "vendor\bin\php-cs-fixer"

if (-not (Test-Path -LiteralPath $fixer) -and -not (Test-Path -LiteralPath "$fixer.bat")) {
    throw "PHP CS Fixer is required but vendor/bin/php-cs-fixer is missing. Run .\scripts\setup\install-dependencies.ps1."
}

& $php $fixer fix --dry-run --diff --show-progress=none
exit $LASTEXITCODE
