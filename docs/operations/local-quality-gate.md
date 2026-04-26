# Local Quality Gate

Run the full local gate without changing PATH:

```powershell
.\scripts\quality\check.ps1
```

Run the Composer gate when PHP and Composer are on PATH:

```powershell
composer check
```

The gate runs PHP lint, PHPStan, PHP-CS-Fixer dry-run, and PHPUnit.
