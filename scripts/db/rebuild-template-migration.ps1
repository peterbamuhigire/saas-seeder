$ErrorActionPreference = "Stop"

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$target = Join-Path $root "database\schema\current.sql"
$parts = @(Get-ChildItem (Join-Path $root "database\migrations") -Filter "*.sql" | Sort-Object Name)

$content = foreach ($part in $parts) {
    "-- ================================================================"
    "-- Source: $($part.Name)"
    "-- ================================================================"
    Get-Content -Raw -LiteralPath $part.FullName
}

Set-Content -LiteralPath $target -Value ($content -join [Environment]::NewLine) -Encoding UTF8
Write-Host "Rebuilt $target"
