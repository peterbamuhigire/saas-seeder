# Start PHP Development Server
# Run this script to start the built-in PHP server

$phpPath = "C:\wamp64\bin\php\php8.3.28\php.exe"
$port = 8000
$docRoot = "public"

Write-Host "Starting PHP development server..." -ForegroundColor Green
Write-Host "PHP: $phpPath" -ForegroundColor Cyan
Write-Host "Port: $port" -ForegroundColor Cyan
Write-Host "Document Root: $docRoot" -ForegroundColor Cyan
Write-Host ""
Write-Host "Server URLs:" -ForegroundColor Yellow
Write-Host "  Login:       http://localhost:$port/sign-in.php" -ForegroundColor White
Write-Host "  Admin Panel: http://localhost:$port/adminpanel/" -ForegroundColor White
Write-Host "  API:         http://localhost:$port/api/v1/" -ForegroundColor White
Write-Host ""
Write-Host "Default credentials: root / password" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host "----------------------------------------" -ForegroundColor Gray
Write-Host ""

if (Test-Path $phpPath) {
    & $phpPath -S localhost:$port -t $docRoot
} else {
    Write-Host "✗ PHP not found at: $phpPath" -ForegroundColor Red
    Write-Host "Please update the phpPath variable in this script." -ForegroundColor Yellow
}
