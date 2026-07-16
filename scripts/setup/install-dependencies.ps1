$ErrorActionPreference = "Stop"

$php = & (Join-Path $PSScriptRoot "..\quality\find-php.ps1")
$composer = & (Join-Path $PSScriptRoot "find-composer.ps1")
$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$version = & $php -r "echo PHP_VERSION;"

Write-Host "Installing locked Composer dependencies with PHP $version"

if ([IO.Path]::GetExtension($composer) -eq ".phar") {
    & $php $composer install --no-interaction --prefer-dist --working-dir=$root
} else {
    & $composer install --no-interaction --prefer-dist --working-dir=$root
}

if ($LASTEXITCODE -ne 0) {
    throw "Composer dependency installation failed with exit code $LASTEXITCODE."
}

Write-Host "Dependencies installed. Run .\scripts\quality\check.ps1 next."
