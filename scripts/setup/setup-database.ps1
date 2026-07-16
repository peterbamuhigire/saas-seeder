param(
    [switch]$WithSeeds
)

$ErrorActionPreference = "Stop"
$root = (Resolve-Path (Join-Path $PSScriptRoot "..\..")).Path

& (Join-Path $root "scripts\db\migrate.ps1") -WithSeeds:$WithSeeds
& (Join-Path $root "scripts\db\validate-schema.ps1")
