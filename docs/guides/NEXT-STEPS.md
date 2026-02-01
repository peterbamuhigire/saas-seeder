# SaaS Seeder Template - Next Steps

## 🎉 What We've Accomplished

We've successfully copied all the core authentication and RBAC files from Maduuka to the SaaS Seeder template:

### ✅ Files Copied (40+ files)

- **Config:** database.php, autoloader.php, auth.php
- **Services:** AuthService, TokenService, PermissionService
- **Helpers:** PasswordHelper, CSRFHelper, CookieHelper
- **DTOs:** LoginDTO, AuthResult, AuthDTO
- **Middleware:** AuthMiddleware, PermissionMiddleware, RoleMiddleware
- **Pages:** logout.php, forgot-password.php, access-denied.php

### ✅ Created Files

- `.env` and `.env.example` - Environment configuration
- `composer.json` - Dependency management
- `.gitignore` - Git ignore rules
- `scripts/setup/setup-database.ps1` - Database setup script
- `docs/operations/SETUP-PROGRESS.md` - Detailed progress report

---

## 🚀 Quick Start (3 Steps)

### Step 1: Install Dependencies

```bash
composer install
```

This will install:

- `vlucas/phpdotenv` - Environment variable loader
- `firebase/php-jwt` - JWT token handling

### Step 2: Create Database

```powershell
# Run the setup script
.\scripts\setup\setup-database.ps1
```

This will:

1. Create the `saas_seeder` database
2. Run the migration from `docs/seeder-template/migration.sql`
3. Create the default super user

**Default Credentials:**

- Username: `root`
- Password: `password`

### Step 3: Update sign-in.php

The `public/sign-in.php` file needs to be updated with the authentication logic.

**Key changes needed:**

1. Update require paths (add `../` prefix for files outside public/)
2. Remove Maduuka-specific logic (distributor panel, SystemMethods)
3. Update routing for adminpanel/memberpanel
4. Test the login flow

---

## 📋 Remaining Tasks

### Priority 1: Update sign-in.php

**Current status:** Existing UI present, needs backend logic

**Required changes:**

```php
// Change these paths
require_once 'src/config/database.php';      // ❌ Old
require_once '../src/config/database.php';   // ✅ New

require_once 'vendor/autoload.php';          // ❌ Old
require_once '../vendor/autoload.php';       // ✅ New

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);     // ❌ Old
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); // ✅ New
```

**Routing logic:**

```php
// Update user type routing
if ($_SESSION['user_type'] === 'super_admin' || $_SESSION['user_type'] === 'owner') {
    header('Location: ./adminpanel/');
} else {
    header('Location: ./memberpanel/');
}
```

### Priority 2: Copy API Endpoints (Optional)

If you need RESTful API authentication:

```
api/v1/auth/login.php
api/v1/auth/logout.php
api/v1/auth/refresh.php
```

Create the `api/v1/auth/` directory and copy from Maduuka.

### Priority 3: Test Login Flow

1. Start PHP development server:

   ```bash
   php -S localhost:8000 -t public/
   ```

2. Access: <http://localhost:8000/sign-in.php>

3. Login with: root / password

4. Should redirect to: <http://localhost:8000/adminpanel/>

5. Test logout functionality

---

## 🔧 Configuration

### Database (.env)

Run `scripts/setup/setup-database.ps1` to create the SaaS Seeder database, apply the migration, and seed the default super admin.

```env
DB_HOST=localhost
DB_NAME=saas_seeder
DB_USER=root
DB_PASSWORD=
```

### Cookie Security

Update the cookie encryption key in production:

```env
COOKIE_ENCRYPTION_KEY=your-32-character-encryption-key-here
```

### JWT Secret (Optional)

If using JWT tokens, the system will auto-generate a secret on first use,
or you can set it manually:

```env
JWT_SECRET_KEY=your-jwt-secret-key-here
```

### Cookie Security

Update the cookie encryption key in production:

```env
COOKIE_ENCRYPTION_KEY=your-32-character-encryption-key-here
```

### JWT Secret (Optional)

If using JWT tokens, the system will auto-generate a secret on first use,
or you can set it manually:

```env
JWT_SECRET_KEY=your-jwt-secret-key-here
```

---

## 📁 Directory Structure

```
saas-seeder/
├── docs/                   # All documentation lives here: overview, api, guides, summaries, implementation, operations
├── scripts/
│   ├── setup/              # install-dependencies.ps1, setup-database.ps1
│   ├── db/                 # fix-database.ps1, seed.ps1
│   ├── server/             # start-server.ps1
│   └── utils/              # dir_map.ps1
├── public/                 # Web root
├── src/                    # Auth services, helpers, middleware
├── api/                    # RESTful endpoints
├── seeder-template/        # Migration + copy checklist
├── .env                    # Environment overrides
├── composer.json           # Dependency lockfile
└── README.md               # Points to docs/overview
```

---

## 🎯 After Login Works

Once you have a working login system, you can:

### 1. Customize Branding

- Update `public/adminpanel/includes/` for admin branding
- Update `public/memberpanel/includes/` for member branding
- Replace logos and colors

### 2. Create Your First Module

```
src/Modules/YourModule/
├── Controllers/
├── Services/
├── Models/
└── DTO/
```

### 3. Add Real Permissions

Insert your application-specific permissions:

```sql
INSERT INTO tbl_permissions (name, code, module, description)
VALUES
('View Invoices', 'INVOICE_VIEW', 'SALES', 'View invoice list'),
('Create Invoice', 'INVOICE_CREATE', 'SALES', 'Create new invoices');
```

### 4. Create Roles

```sql
INSERT INTO tbl_global_roles (code, name, description)
VALUES ('ACCOUNTANT', 'Accountant', 'Financial staff role');

-- Assign permissions to role
INSERT INTO tbl_global_role_permissions (global_role_id, permission_id)
VALUES (2, 6), (2, 7);  -- INVOICE_VIEW, INVOICE_CREATE
```

### 5. Build Your Dashboard

Replace placeholder stats in:

- `public/adminpanel/index.php`
- `public/memberpanel/index.php`

---

## 🐛 Troubleshooting

### Database Connection Errors

```
SQLSTATE[HY000] [2002] No such file or directory
```

**Solution:** Check MySQL is running and `.env` DB credentials are correct

### Autoloader Errors

```
Class 'App\Config\Database' not found
```

**Solution:** Run `composer install` to generate autoloader

### Session Errors

```
Failed to write session data
```

**Solution:** Ensure `session.save_path` is writable in php.ini

### CSRF Token Errors

**Solution:** Check that session_start() is called before form rendering

---

## 📚 Additional Resources

- **Complete Template Guide:** `docs/seeder-template/README.md`
- **Migration Script:** `docs/seeder-template/migration.sql`
- **File Copy Checklist:** `docs/seeder-template/copy-login-files.md`
- **Progress Report:** `docs/operations/SETUP-PROGRESS.md`

---

## 🎓 Understanding the Auth System

### Authentication Flow

1. User submits login form
2. AuthService validates credentials using stored procedures
3. PermissionService loads user permissions
4. Session created with user data
5. User redirected based on user_type

### Permission Checking

```php
// Check permission in UI
if (hasPermissionGlobal('INVOICE_CREATE')) {
    // Show create button
}

// Require permission (throws exception if denied)
requirePermissionGlobal('INVOICE_DELETE');
```

### Automatic Panel Routing

The `src/config/auth.php` file automatically:

- Redirects non-admins from adminpanel
- Allows admins to access both panels
- Routes users based on user_type on login

---

## ✅ Checklist

Before considering the template ready:

- [ ] Dependencies installed (`composer install`)
- [ ] Database created and migrated
- [ ] sign-in.php updated with auth logic
- [ ] Successful login with root/password
- [ ] Redirect to adminpanel works
- [ ] Logout functionality tested
- [ ] Session timeout tested (30 min)
- [ ] Access denied page displays correctly
- [ ] CSRF protection working
- [ ] Environment variables properly set

---

**Last Updated:** 2026-02-01
**Status:** 75% Complete
**Next Priority:** Update sign-in.php with authentication logic
