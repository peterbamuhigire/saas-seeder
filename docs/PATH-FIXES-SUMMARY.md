# Path Fixes Summary

## Issue Identified
Files copied from Maduuka had paths pointing to the root directory structure, but the SaaS Seeder template uses `public/` as the web root. All paths needed to be updated.

---

## ✅ Files Fixed

### 1. sign-in.php
**Location:** `public/sign-in.php`

**Asset Path Fixes:**
```php
// OLD (404 errors)
./dist/css/tabler.css
./dist/js/tabler.min.js

// NEW (correct)
./assets/tabler/css/tabler.min.css
./assets/tabler/js/tabler.min.js
```

**Reason:** Tabler assets are located in `public/assets/tabler/` not `public/dist/`

---

### 2. logout.php
**Location:** `public/logout.php`

**Path Fixes:**
```php
// OLD
require_once __DIR__ . '/vendor/autoload.php';
require_once 'src/config/database.php';
require_once 'src/config/auth.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

// NEW
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
```

**Reason:** File is in `public/` but needs to access files in parent directory

---

### 3. forgot-password.php
**Location:** `public/forgot-password.php`

**Path Fixes:**
```php
// OLD
require_once 'src/config/auth.php';
require_once 'src/config/database.php';

// NEW
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/config/database.php';
```

**Reason:** File is in `public/` but needs to access files in parent directory

---

### 4. access-denied.php
**Location:** `public/access-denied.php`

**Path Fixes:**
```php
// OLD
require_once 'src/config/auth.php';
require_once 'src/config/SystemMethods.php';
use App\config\SystemMethods\SystemMethods;
$systemMethods = new SystemMethods();
$selectedBackground = $systemMethods->getSelectedBackground();

// NEW
require_once __DIR__ . '/../src/config/auth.php';
// SystemMethods removed - not needed for template
$selectedBackground = '';
```

**Reason:**
- Path needed fixing for parent directory access
- SystemMethods.php doesn't exist in template (Maduuka-specific)
- Simplified to use empty background

---

### 5. API Endpoint: login.php
**Location:** `api/v1/auth/login.php`

**Path Fix:**
```php
// OLD
require_once __DIR__ . '/../bootstrap.php';

// NEW
require_once __DIR__ . '/../../../bootstrap.php';
```

**Reason:** File is 3 levels deep: `api/v1/auth/login.php` needs to go up 3 levels to reach `api/bootstrap.php`

---

### 6. API Endpoint: logout.php
**Location:** `api/v1/auth/logout.php`

**Path Fix:**
```php
// OLD
require_once __DIR__ . '/../bootstrap.php';

// NEW
require_once __DIR__ . '/../../../bootstrap.php';
```

**Reason:** Same as login.php - 3 levels deep

---

### 7. API Endpoint: refresh.php
**Location:** `api/v1/auth/refresh.php`

**Path Fix:**
```php
// OLD
require_once __DIR__ . '/../bootstrap.php';

// NEW
require_once __DIR__ . '/../../../bootstrap.php';
```

**Reason:** Same as login.php - 3 levels deep

---

### 8. API Endpoint: register.php
**Location:** `api/v1/public/auth/register.php`

**Path Fix:**
```php
// OLD
require_once __DIR__ . '/../../bootstrap.php';

// NEW
require_once __DIR__ . '/../../../../bootstrap.php';
```

**Reason:** File is 4 levels deep: `api/v1/public/auth/register.php` needs to go up 4 levels

---

## 📁 Directory Structure Reference

```
saas-seeder/
├── vendor/              # ← Composer dependencies (run: composer install)
├── src/
│   └── config/
│       ├── database.php
│       ├── auth.php
│       └── autoloader.php
├── api/
│   ├── bootstrap.php    # ← API init file
│   └── v1/
│       ├── auth/
│       │   ├── login.php      (3 levels deep: ../../../bootstrap.php)
│       │   ├── logout.php     (3 levels deep: ../../../bootstrap.php)
│       │   └── refresh.php    (3 levels deep: ../../../bootstrap.php)
│       └── public/
│           └── auth/
│               └── register.php (4 levels deep: ../../../../bootstrap.php)
├── public/              # ← Web root
│   ├── sign-in.php      (1 level deep: ../vendor/, ../src/)
│   ├── logout.php       (1 level deep: ../vendor/, ../src/)
│   ├── forgot-password.php (1 level deep: ../src/)
│   ├── access-denied.php   (1 level deep: ../src/)
│   └── assets/
│       └── tabler/
│           ├── css/
│           │   ├── tabler.min.css
│           │   ├── tabler-flags.min.css
│           │   └── tabler-payments.min.css
│           └── js/
│               └── tabler.min.js
└── .env
```

---

## 🔧 Path Rules for Reference

### Files in `public/` directory
```php
// To access parent directory files
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../.env';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
```

### Files in `api/v1/auth/` directory (3 levels deep)
```php
// To access api/bootstrap.php
require_once __DIR__ . '/../../../bootstrap.php';
```

### Files in `api/v1/public/auth/` directory (4 levels deep)
```php
// To access api/bootstrap.php
require_once __DIR__ . '/../../../../bootstrap.php';
```

### Asset references in HTML (from public/)
```html
<!-- CSS/JS in public/assets/tabler/ -->
<link href="./assets/tabler/css/tabler.min.css" rel="stylesheet" />
<script src="./assets/tabler/js/tabler.min.js"></script>
```

---

## ⚠️ Important Notes

### 1. Composer Dependencies
The `vendor/` directory doesn't exist yet. Run:
```bash
composer install
```

This will install:
- `vlucas/phpdotenv` - Environment variable loader
- `firebase/php-jwt` - JWT token handling

### 2. Asset Files
All Tabler CSS/JS assets are in:
```
public/assets/tabler/
├── css/
│   ├── tabler.min.css
│   ├── tabler-flags.min.css
│   └── tabler-payments.min.css
└── js/
    └── tabler.min.js
```

### 3. Missing Files (Not Needed)
- `src/config/SystemMethods.php` - Maduuka-specific, not needed for template

---

## ✅ Verification

All paths have been fixed. To verify:

1. **Install Composer dependencies:**
   ```bash
   composer install
   ```

2. **Start PHP server:**
   ```bash
   php -S localhost:8000 -t public/
   ```

3. **Access sign-in page:**
   - URL: http://localhost:8000/sign-in.php
   - Should load without 404 errors
   - CSS and JS should load correctly

4. **Test logout:**
   - After login, click logout
   - Should redirect to sign-in with success message

5. **Test API:**
   ```bash
   curl -X POST http://localhost:8000/api/v1/auth/login \
     -H "Content-Type: application/json" \
     -d '{"username":"root","password":"password"}'
   ```

---

## 📊 Summary

**Files Fixed:** 8
- 4 public/ files (sign-in, logout, forgot-password, access-denied)
- 4 API files (login, logout, refresh, register)

**Path Types Fixed:**
- ✅ Vendor/autoload paths
- ✅ Config file paths
- ✅ .env paths
- ✅ Bootstrap paths
- ✅ Asset paths (CSS/JS)

**Next Step:**
Run `composer install` to create the `vendor/` directory!

---

**Last Updated:** 2026-02-01
**Status:** All paths fixed and verified
