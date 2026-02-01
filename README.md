# SaaS Seeder Template

A production-ready authentication and RBAC system for kickstarting new web-based SaaS projects. Get from idea to working prototype in minutes, not days.

## 🎯 What is This?

SaaS Seeder is a **ready-to-use template** that gives you:

- ✅ **Complete authentication system** (session + JWT)
- ✅ **Role-Based Access Control (RBAC)** with permissions
- ✅ **Clean UI** powered by Tabler (admin + member panels)
- ✅ **RESTful API** with authentication endpoints
- ✅ **Security built-in** (CSRF, password hashing, session management)
- ✅ **Database schema** with stored procedures
- ✅ **Multi-tenant ready** (franchise-based isolation)

**Stop rebuilding the same auth system for every project.** Start here instead.

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.0+
- MySQL 8.0+
- Composer
- WAMP/XAMPP or similar (for local development)

### Installation (3 Steps)

```bash
# 1. Install dependencies
composer install

# 2. Setup database (Windows PowerShell)
.\setup-database.ps1

# 3. Start PHP development server
php -S localhost:8000 -t public/
```

### Create Super Admin User

After installation, create a super admin user using the development tool:

1. Visit: http://localhost:8000/super-user-dev.php
2. Fill in the form with your details
3. Click "Create Super Admin"

**⚠️ IMPORTANT:** The super-user-dev.php page uses the correct password hashing method (Argon2ID with salt and pepper) that matches the login system. Remove or restrict access to this file in production!

### Default Credentials (Legacy)

If you ran the migration script, these credentials may exist:
- **Username:** `root`
- **Password:** `password`

**Note:** Due to password hashing changes, you should create a new super admin using super-user-dev.php instead.

### Access

- **Login:** http://localhost:8000/sign-in.php
- **Super User Creator (DEV):** http://localhost:8000/super-user-dev.php
- **Admin Panel:** http://localhost:8000/adminpanel/
- **Member Panel:** http://localhost:8000/memberpanel/
- **API:** http://localhost:8000/api/v1/

---

## 📁 Project Structure

```
saas-seeder/
├── public/                   # Web root (DocumentRoot points here)
│   ├── sign-in.php          # Login page (complete auth logic)
│   ├── super-user-dev.php   # Super admin creator (DEV ONLY)
│   ├── logout.php           # Logout functionality
│   ├── forgot-password.php  # Password recovery
│   ├── access-denied.php    # Access denied page
│   ├── dashboard.php        # 🏫 FRANCHISE ADMIN DASHBOARD (root)
│   ├── skeleton.php         # Page template for franchise admin pages
│   │
│   ├── adminpanel/          # 🌐 SUPER ADMIN PANEL
│   │   └── index.php        # System admin dashboard
│   │
│   ├── memberpanel/         # 👤 END USER PANEL
│   │   └── index.php        # Member/student/customer dashboard
│   │
│   ├── assets/              # Shared CSS, JS, images
│   └── uploads/             # File uploads
│
├── src/
│   ├── config/              # Configuration files
│   │   ├── database.php     # Database connection
│   │   ├── autoloader.php   # PSR-4 autoloader
│   │   └── auth.php         # Auth functions & auto-routing
│   │
│   └── Auth/                # Authentication module
│       ├── Services/        # AuthService, TokenService, PermissionService
│       ├── Helpers/         # PasswordHelper, CSRFHelper, CookieHelper
│       ├── DTO/             # LoginDTO, AuthResult, AuthDTO
│       ├── Middleware/      # AuthMiddleware, PermissionMiddleware
│       └── Models/          # User, Role, Permission models
│
├── api/                     # RESTful API (outside public/ for security)
│   ├── bootstrap.php        # API initialization
│   └── v1/
│       ├── auth/            # Authentication endpoints
│       │   ├── login.php
│       │   ├── logout.php
│       │   └── refresh.php
│       └── public/          # Unauthenticated endpoints
│
├── docs/
│   ├── seeder-template/
│   │   ├── README.md        # Template guide
│   │   ├── migration.sql    # Database schema
│   │   └── copy-login-files.md
│   ├── AUTHENTICATION-GUIDE.md  # Complete auth docs
│   ├── API-DOCUMENTATION.md     # API reference
│   └── QUICK-REFERENCE.md       # Cheat sheet
│
├── .env                     # Environment variables
├── .env.example             # Environment template
├── composer.json            # PHP dependencies
├── setup-database.ps1       # Database setup script
└── README.md                # This file
```

---

## 🔐 Authentication System

### Features

- **Dual Authentication:** Session-based (web) + JWT (API)
- **Password Security:** Argon2ID hashing with salt and pepper for enhanced security
- **Session Management:** 30-minute timeout, auto-regeneration, prefixed session variables
- **CSRF Protection:** Token validation on all state-changing requests
- **Remember Me:** 30-day persistent sessions with encrypted cookies
- **Failed Login Tracking:** Automatic lockout after multiple failures
- **Stored Procedures:** Database-level auth logic for consistency
- **Role-Based Access Control:** Automatic routing and panel protection

### Three-Tier Panel Structure

**IMPORTANT:** This template uses a three-tier architecture:

1. **`/adminpanel/`** - Super Admin System
   - Manage multiple franchises/schools/organizations
   - System-wide settings and billing

2. **`/public/` (root)** - Franchise Admin Panel
   - Manage your franchise/school/restaurant
   - School principals, restaurant managers work here

3. **`/memberpanel/`** - End User Portal
   - Students, customers, patients access here
   - Self-service portal for end users

| User Type | Login Redirect | Primary Workspace | Example Role |
|-----------|----------------|-------------------|--------------|
| `super_admin` | `/adminpanel/` | System admin | SaaS operator |
| `owner` | `/dashboard.php` | Franchise admin (public/ root) | School principal |
| `staff` | `/dashboard.php` | Franchise admin (public/ root) | School admin staff |
| `member`/others | `/memberpanel/` | End user portal | Students/Customers |

**Access Rules:**
- Super admins can access ALL three tiers
- Franchise admins (owner/staff) can access public/ and memberpanel
- End users can ONLY access memberpanel

**See:** `docs/PANEL-STRUCTURE.md` for detailed architecture guide

---

## 🛡️ RBAC (Permissions)

### Permission Checking

```php
// Check permission (returns boolean)
if (hasPermissionGlobal('INVOICE_CREATE')) {
    // Show create button
}

// Require permission (throws exception if denied)
requirePermissionGlobal('INVOICE_DELETE');
// Code here only runs if permission granted
```

### Super Admin Bypass

Users with `user_type = 'super_admin'` automatically have ALL permissions.

---

## 📚 Documentation

- **[Quick Reference](docs/QUICK-REFERENCE.md)** - Cheat sheet
- **[Authentication Guide](docs/AUTHENTICATION-GUIDE.md)** - Complete auth docs
- **[API Documentation](docs/API-DOCUMENTATION.md)** - API reference
- **[Setup Progress](SETUP-PROGRESS.md)** - Setup status
- **[Next Steps](NEXT-STEPS.md)** - Getting started guide

---

## 🔧 Configuration

### Environment Variables (.env)

```env
# Database
DB_HOST=localhost
DB_NAME=saas_seeder
DB_USER=root
DB_PASSWORD=

# Cookie Security
COOKIE_DOMAIN=localhost
COOKIE_ENCRYPTION_KEY=your-32-character-encryption-key

# Password Security
PASSWORD_PEPPER=your-64-character-pepper-string

# Application
APP_ENV=development
```

**Note:** The `PASSWORD_PEPPER` is used alongside Argon2ID to add an extra layer of security to password hashing. If not set, a fallback value will be used for development (not recommended for production).

---

## 🔒 Security Checklist

Before going live:

- [ ] **Remove or restrict access to `super-user-dev.php`** (development tool only!)
- [ ] Change default `root` password
- [ ] Update `COOKIE_ENCRYPTION_KEY` with random 32-char string
- [ ] Set `PASSWORD_PEPPER` in `.env` with random 64-char string
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Enable HTTPS (SSL certificate)
- [ ] Set file permissions (`.env` should be 600)
- [ ] Implement rate limiting for API
- [ ] Set up regular database backups

---

## 🐛 Troubleshooting

### "Class not found" error
```bash
composer install
```

### Database connection failed
- Check `.env` credentials
- Verify MySQL is running
- Test: `mysql -u root -p saas_seeder`

### Session expired immediately
- Check `session.gc_maxlifetime` in `php.ini`
- Default timeout: 30 minutes

### CSRF validation failed
- Ensure session started before form rendering
- Check form has CSRF token field

---

## 🤝 Contributing

This is a template project. Fork it and customize for your needs!

---

## 📄 License

MIT License - Feel free to use for personal or commercial projects.

---

**Built with ❤️ for rapid SaaS development**

**Last Updated:** 2026-02-01
