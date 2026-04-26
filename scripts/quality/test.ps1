$ErrorActionPreference = "Stop"
$php = & "$PSScriptRoot\find-php.ps1"
$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
& $php (Join-Path $root "vendor\bin\phpunit") --colors=never
exit $LASTEXITCODE
