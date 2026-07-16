$ErrorActionPreference = "Stop"

$commands = @(
    (Get-Command composer -ErrorAction SilentlyContinue),
    (Get-Command composer.phar -ErrorAction SilentlyContinue)
) | Where-Object { $_ }

if ($commands.Count -gt 0) {
    Write-Output $commands[0].Source
    exit 0
}

$candidates = @(
    "C:\ProgramData\ComposerSetup\bin\composer.phar",
    "C:\wamp64\bin\composer\composer.phar",
    (Join-Path $env:APPDATA "Composer\latest.phar")
)

foreach ($candidate in $candidates) {
    if (Test-Path -LiteralPath $candidate) {
        Write-Output (Resolve-Path -LiteralPath $candidate).Path
        exit 0
    }
}

throw "Composer was not found. Add Composer to PATH or install composer.phar under the WAMP Composer directory."
