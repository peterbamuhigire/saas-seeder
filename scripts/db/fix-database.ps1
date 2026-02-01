# ================================================================
# DATABASE FIX SCRIPT - Fix Collation and Create Franchises Table
# ================================================================
# This script runs the fix-collation-and-create-franchises.sql file
# Run this if you're getting collation errors or missing franchises table
# ================================================================

# Load .env file
$envFile = ".env"
$env = @{}

if (Test-Path $envFile) {
    Get-Content $envFile | ForEach-Object {
        if ($_ -match '^\s*([^#][^=]+)\s*=\s*(.+)\s*$') {
            $key = $matches[1].Trim()
            $value = $matches[2].Trim()
            $env[$key] = $value
        }
    }
    Write-Host "Loaded environment variables from .env" -ForegroundColor Green
} else {
    Write-Host "Error: .env file not found!" -ForegroundColor Red
    exit 1
}

# Get database credentials from .env or use defaults
$dbHost = if ($env.ContainsKey('DB_HOST')) { $env['DB_HOST'] } else { 'localhost' }
$dbName = if ($env.ContainsKey('DB_NAME')) { $env['DB_NAME'] } else { 'saas_seeder' }
$dbUser = if ($env.ContainsKey('DB_USER')) { $env['DB_USER'] } else { 'root' }
$dbPassword = if ($env.ContainsKey('DB_PASSWORD')) { $env['DB_PASSWORD'] } else { '' }
$dbPort = if ($env.ContainsKey('DB_PORT')) { $env['DB_PORT'] } else { '3306' }

Write-Host "`n================================================================" -ForegroundColor Cyan
Write-Host "  SaaS Seeder - Database Fix Script" -ForegroundColor Cyan
Write-Host "================================================================`n" -ForegroundColor Cyan

Write-Host "Database Configuration:" -ForegroundColor Yellow
Write-Host "  Host: $dbHost"
Write-Host "  Port: $dbPort"
Write-Host "  Database: $dbName"
Write-Host "  User: $dbUser"
Write-Host ""

# Path to SQL file
$sqlFile = "docs\seeder-template\fix-collation-and-create-franchises.sql"

if (-not (Test-Path $sqlFile)) {
    Write-Host "Error: SQL file not found at $sqlFile" -ForegroundColor Red
    exit 1
}

Write-Host "Running database fix script..." -ForegroundColor Yellow

# Build MySQL command
$mysqlCmd = "mysql"
$mysqlArgs = @(
    "-h", $dbHost,
    "-P", $dbPort,
    "-u", $dbUser
)

if ($dbPassword -ne '') {
    $mysqlArgs += "-p$dbPassword"
}

$mysqlArgs += @(
    $dbName,
    "-e", "source $sqlFile"
)

# Execute MySQL command
try {
    Write-Host "Executing: $mysqlCmd $($mysqlArgs -join ' ')" -ForegroundColor Gray
    & $mysqlCmd @mysqlArgs

    if ($LASTEXITCODE -eq 0) {
        Write-Host "`n================================================================" -ForegroundColor Green
        Write-Host "  SUCCESS! Database fixes applied successfully!" -ForegroundColor Green
        Write-Host "================================================================" -ForegroundColor Green
        Write-Host ""
        Write-Host "Changes made:" -ForegroundColor Yellow
        Write-Host "  1. Fixed collation mismatches in tbl_users"
        Write-Host "  2. Created tbl_franchises table"
        Write-Host "  3. Created default 'system' franchise"
        Write-Host "  4. Updated sp_authenticate_user stored procedure"
        Write-Host ""
        Write-Host "You can now use the application without collation errors!" -ForegroundColor Green
        Write-Host ""
    } else {
        Write-Host "`nError: MySQL command failed with exit code $LASTEXITCODE" -ForegroundColor Red
        Write-Host "Please check the error messages above." -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "`nError running MySQL command:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Write-Host ""
    Write-Host "Make sure:" -ForegroundColor Yellow
    Write-Host "  1. MySQL is running"
    Write-Host "  2. MySQL command-line client is in your PATH"
    Write-Host "  3. Database credentials in .env are correct"
    exit 1
}

Write-Host "Press any key to continue..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
