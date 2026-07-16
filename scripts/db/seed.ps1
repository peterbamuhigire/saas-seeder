[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'

Write-Host 'Applying versioned migrations and idempotent seed files...'

& (Join-Path $PSScriptRoot 'migrate.ps1') -WithSeeds
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

& (Join-Path $PSScriptRoot 'validate-schema.ps1')
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

Write-Host 'Database migrations and seeds are valid.'
