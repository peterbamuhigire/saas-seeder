$ErrorActionPreference = "Stop"

$candidates = @()

if ($env:MYSQL_BINARY) {
    $candidates += $env:MYSQL_BINARY
}

$pathCommand = Get-Command mysql -ErrorAction SilentlyContinue
if ($pathCommand) {
    $candidates += $pathCommand.Source
}

$wampRoot = "C:\wamp64\bin\mysql"
if (Test-Path -LiteralPath $wampRoot) {
    $candidates += Get-ChildItem -LiteralPath $wampRoot -Directory |
        Sort-Object Name -Descending |
        ForEach-Object { Join-Path $_.FullName "bin\mysql.exe" }
}

$mysql = $candidates |
    Where-Object { $_ -and (Test-Path -LiteralPath $_ -PathType Leaf) } |
    Select-Object -First 1

if (-not $mysql) {
    throw "MySQL client not found. Set MYSQL_BINARY, add mysql to PATH, or install MySQL under C:\wamp64\bin\mysql."
}

return (Resolve-Path -LiteralPath $mysql).Path
