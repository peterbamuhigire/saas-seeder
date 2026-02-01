# Install Composer Dependencies
# Run this script to install required PHP packages

$phpPath = "C:\wamp64\bin\php\php8.3.28\php.exe"
$composerPath = "C:\ProgramData\ComposerSetup\bin\composer.phar"

Write-Host "Installing Composer dependencies..." -ForegroundColor Green
Write-Host "PHP: $phpPath" -ForegroundColor Cyan
Write-Host ""

if (Test-Path $phpPath) {
    & $phpPath $composerPath install

    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✓ Dependencies installed successfully!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Next steps:" -ForegroundColor Cyan
        Write-Host "1. Run: .\start-server.ps1" -ForegroundColor White
        Write-Host "2. Open: http://localhost:8000/sign-in.php" -ForegroundColor White
        Write-Host "3. Login with: root / password" -ForegroundColor White
    } else {
        Write-Host ""
        Write-Host "✗ Installation failed!" -ForegroundColor Red
    }
} else {
    Write-Host "✗ PHP not found at: $phpPath" -ForegroundColor Red
    Write-Host "Please update the phpPath variable in this script." -ForegroundColor Yellow
}
