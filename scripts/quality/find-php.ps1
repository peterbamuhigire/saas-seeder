$ErrorActionPreference = "Stop"

$candidates = [System.Collections.Generic.List[string]]::new()
if ($env:PHP_BINARY) {
    $candidates.Add($env:PHP_BINARY)
}

$pathCommand = Get-Command php -ErrorAction SilentlyContinue
if ($pathCommand) {
    $candidates.Add($pathCommand.Source)
}

$wampRoot = "C:\wamp64\bin\php"
if (Test-Path -LiteralPath $wampRoot) {
    Get-ChildItem -LiteralPath $wampRoot -Directory -Filter "php8.3*" |
        Sort-Object Name -Descending |
        ForEach-Object { $candidates.Add((Join-Path $_.FullName "php.exe")) }
}

foreach ($candidate in ($candidates | Select-Object -Unique)) {
    if (-not (Test-Path -LiteralPath $candidate)) {
        continue
    }

    $version = & $candidate -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;"
    if ($LASTEXITCODE -eq 0 -and $version -eq "8.3") {
        Write-Output (Resolve-Path -LiteralPath $candidate).Path
        exit 0
    }
}

throw "PHP 8.3 was not found. Set PHP_BINARY, add PHP to PATH, or install a WAMP PHP 8.3 runtime."
