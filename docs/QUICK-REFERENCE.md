# Quick Reference Guide - SaaS Seeder Template

## 🚀 Getting Started (3 Steps)

```bash
# 1. Install dependencies
composer install

# 2. Setup database (PowerShell)
.\setup-database.ps1

# 3. Start server
php -S localhost:8000 -t public/
```

**Default Login:** `root` / `password`

---

## 📁 Directory Structure

```
saas-seeder/
├── public/              # Web root (Apache/Nginx points here)
│   ├── sign-in.php     # Login page
│   ├── logout.php      # Logout
│   ├── adminpanel/     # Admin dashboard
│   └── memberpanel/    # Member dashboard
├── src/
│   ├── config/         # Configuration
│   └── Auth/           # Authentication module
├── api/                # RESTful API
├── docs/               # Documentation
├── .env                # Environment variables
└── composer.json       # Dependencies
```

---

## 🔐 Common Auth Functions

### Check if user is logged in
```php
if (isLoggedIn()) {
    // User is authenticated
}
```

### Require authentication
```php
requireAuth(); // Redirects to sign-in if not logged in
```

### Require guest (redirect if logged in)
```php
requireGuest(); // Redirects to dashboard if already logged in
```

### Check permission
```php
if (hasPermissionGlobal('INVOICE_CREATE')) {
    // User has permission
}
```

### Require permission
```php
requirePermissionGlobal('INVOICE_DELETE'); // Throws exception if denied
```

### Manual logout
```php
logout(); // Clears session and redirects
```

---

## 🎫 Session Variables

```php
$_SESSION['user_id']           // User ID
$_SESSION['username']          // Username
$_SESSION['user_type']         // super_admin, owner, staff
$_SESSION['franchise_id']      // Franchise ID (NULL for super_admin)
$_SESSION['full_name']         // User's full name
$_SESSION['auth_token']        // JWT token (if using API)
$_SESSION['last_activity']     // Timestamp for timeout
```

---

## 🗄️ Database Tables

### Authentication
- `tbl_users` - User accounts
- `tbl_user_sessions` - Active sessions
- `tbl_login_attempts` - Failed login tracking

### RBAC (Permissions)
- `tbl_permissions` - Permission definitions
- `tbl_global_roles` - Role definitions
- `tbl_global_role_permissions` - Role-permission mapping
- `tbl_user_roles` - User-role assignments
- `tbl_user_permissions` - User-specific overrides

---

## 🔑 Environment Variables (.env)

```env
# Database
DB_HOST=localhost
DB_NAME=saas_seeder
DB_USER=root
DB_PASSWORD=

# Security
COOKIE_DOMAIN=localhost
COOKIE_ENCRYPTION_KEY=your-32-char-key
APP_ENV=development

# JWT (optional)
JWT_SECRET_KEY=auto-generated
```

---

## 🛠️ Common SQL Queries

### Create new user
```sql
INSERT INTO tbl_users (username, email, password_hash, first_name, last_name, user_type, status, franchise_id)
VALUES ('johndoe', 'john@example.com', '$2y$10$...', 'John', 'Doe', 'staff', 'active', 1);
```

### Add permission
```sql
INSERT INTO tbl_permissions (name, code, module, description)
VALUES ('View Reports', 'REPORT_VIEW', 'REPORTS', 'Access report dashboard');
```

### Create role
```sql
INSERT INTO tbl_global_roles (code, name, description, is_system)
VALUES ('MANAGER', 'Manager', 'Team manager role', 0);
```

### Assign role to user
```sql
INSERT INTO tbl_user_roles (franchise_id, user_id, global_role_id, assigned_by)
VALUES (1, 5, 2, 1);
```

### Assign permission to role
```sql
INSERT INTO tbl_global_role_permissions (global_role_id, permission_id)
VALUES (2, 10);
```

---

## 📡 API Quick Reference

### Login
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"root","password":"password"}'
```

### Authenticated Request
```bash
curl -X GET http://localhost:8000/api/v1/users/me \
  -H "Authorization: Bearer {token}"
```

### Logout
```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer {token}"
```

---

## 🐞 Troubleshooting

### Class not found
```bash
composer install
```

### Database connection failed
- Check `.env` credentials
- Verify MySQL is running
- Test: `mysql -u root -p saas_seeder`

### Session expired
- Default timeout: 30 minutes
- Check `$_SESSION['last_activity']`
- Clear browser cookies

### CSRF validation failed
- Ensure session started before form
- Check `<input type="hidden" name="csrf_token" value="...">`
- Verify POST includes token

### Permission denied
- Check user has required permission:
  ```sql
  CALL sp_get_user_permissions(user_id, franchise_id);
  ```
- Verify role assignments in `tbl_user_roles`

---

## 🎨 UI Customization

### Update branding
```
public/adminpanel/includes/head.php      # Admin header
public/memberpanel/includes/head.php     # Member header
```

### Update navigation
```
public/adminpanel/includes/menus/admin.php
public/memberpanel/includes/menus/member.php
```

### Update styles
```
public/assets/css/custom.css
```

---

## 📦 Composer Packages

```json
{
  "vlucas/phpdotenv": "^5.5",    // Environment variables
  "firebase/php-jwt": "^6.8"      // JWT tokens
}
```

---

## 🔒 Security Checklist

- [ ] Change default `root` password
- [ ] Update `COOKIE_ENCRYPTION_KEY` in `.env`
- [ ] Set `APP_ENV=production` in production
- [ ] Enable HTTPS in production
- [ ] Restrict CORS origins in `api/bootstrap.php`
- [ ] Set strong `JWT_SECRET_KEY`
- [ ] Review file permissions (`.env` should be 600)
- [ ] Enable error logging (not display) in production
- [ ] Implement rate limiting for API
- [ ] Regular database backups

---

## 🚦 User Type Routing

| User Type | Default Route | Panel Access |
|-----------|---------------|--------------|
| `super_admin` | `/adminpanel/` | Both panels |
| `owner` | `/adminpanel/` | Both panels |
| `staff` | `/memberpanel/` | Member only |
| `distributor` | `/memberpanel/` | Member only |

---

## 🧪 Testing Login

### Web (Browser)
1. Navigate to: `http://localhost:8000/sign-in.php`
2. Enter: `root` / `password`
3. Should redirect to: `http://localhost:8000/adminpanel/`

### API (cURL)
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"root","password":"password"}'
```

---

## 📚 Documentation Files

- **README.md** - Project overview
- **SETUP-PROGRESS.md** - Setup status report
- **NEXT-STEPS.md** - Quick start guide
- **AUTHENTICATION-GUIDE.md** - Complete auth system docs
- **API-DOCUMENTATION.md** - API endpoint reference
- **QUICK-REFERENCE.md** - This file

---

## 🆘 Getting Help

1. Check documentation in `docs/`
2. Review error logs: `tail -f /path/to/error.log`
3. Enable debug mode: Set `APP_ENV=development` in `.env`
4. Check PHP error logs: `php.ini` → `error_log` setting

---

## 🎯 Next Steps After Login Works

1. ✅ Customize branding (logo, colors, name)
2. ✅ Create your first business module
3. ✅ Add real permissions for your app
4. ✅ Create roles and assign to users
5. ✅ Build dashboard widgets
6. ✅ Add your SaaS features

---

**Last Updated:** 2026-02-01
**Quick Lookup:** Authentication, Database, API, Troubleshooting
