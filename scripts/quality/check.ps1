$ErrorActionPreference = "Stop"
& "$PSScriptRoot\lint-php.ps1"
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
& "$PSScriptRoot\analyse.ps1"
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
& "$PSScriptRoot\test.ps1"
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
