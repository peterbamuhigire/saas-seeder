$candidates = @(
    $env:PHP_BINARY,
    "C:\wamp64\bin\php\php8.3.28\php.exe",
    "C:\wamp64\bin\php\php8.3.0\php.exe"
)
$candidates = @($candidates | Where-Object { $_ -and (Test-Path $_) })

if ($candidates.Count -gt 0) {
    Write-Output $candidates[0]
    exit 0
}

$cmd = Get-Command php -ErrorAction SilentlyContinue
if ($cmd) {
    Write-Output $cmd.Source
    exit 0
}

throw "PHP 8.3 executable not found."
