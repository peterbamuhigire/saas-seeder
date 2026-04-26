$ErrorActionPreference = "Stop"
$php = & "$PSScriptRoot\find-php.ps1"
& $php "$PSScriptRoot\lint-php.php"
exit $LASTEXITCODE
