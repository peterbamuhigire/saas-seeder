param(
    [ValidateRange(1024, 65535)]
    [int]$Port = 8000,
    [string]$HostName = "127.0.0.1"
)

$ErrorActionPreference = "Stop"
$php = & (Join-Path $PSScriptRoot "..\quality\find-php.ps1")
$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$docRoot = Join-Path $root "public"
$router = Join-Path $PSScriptRoot "router.php"

Write-Host "Starting the SaaS Seeder development server"
Write-Host "PHP: $php"
Write-Host "Document root: $docRoot"
Write-Host "Login: http://${HostName}:$Port/sign-in.php"
Write-Host "API login: http://${HostName}:$Port/api/v1/auth/login"
Write-Host "Press Ctrl+C to stop the server."

& $php -S "${HostName}:$Port" -t $docRoot $router
exit $LASTEXITCODE
