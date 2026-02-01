# SaaS Seeder Database Setup Script
# Run this script to create the database and import the migration

$mysqlPath = "C:\wamp64\bin\mysql\mysql8.0.39\bin\mysql.exe"
$phpPath = "C:\wamp64\bin\php\php8.3.28\php.exe"
$dbName = "saas_seeder"
$migrationFile = "docs\seeder-template\migration.sql"

Write-Host "Creating database: $dbName" -ForegroundColor Green

# Create database
& $mysqlPath -u root -e "CREATE DATABASE IF NOT EXISTS $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if ($LASTEXITCODE -eq 0) {
    Write-Host "Database created successfully!" -ForegroundColor Green

    Write-Host "Running migration script..." -ForegroundColor Green

    # Run migration
    & $mysqlPath -u root $dbName < $migrationFile

    if ($LASTEXITCODE -eq 0) {
        Write-Host "Migration completed successfully!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Default login credentials:" -ForegroundColor Cyan
        Write-Host "  Username: root" -ForegroundColor White
        Write-Host "  Password: password" -ForegroundColor White
        Write-Host ""
        Write-Host "You can now start your PHP server and access sign-in.php" -ForegroundColor Green
    } else {
        Write-Host "Migration failed!" -ForegroundColor Red
    }
} else {
    Write-Host "Database creation failed!" -ForegroundColor Red
}
