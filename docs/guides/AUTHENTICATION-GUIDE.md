# Authentication System Guide

## Overview

The SaaS Seeder Template uses a dual authentication system supporting both **session-based** and **JWT token-based** authentication, with a comprehensive RBAC (Role-Based Access Control) permission system.

---

## Authentication Flow

### Session-Based Login (Web Pages)

```
User → sign-in.php
  ↓
Submit credentials (username, password)
  ↓
AuthService::authenticate()
  ↓
sp_authenticate_user (Stored Procedure)
  ↓
Password verification (bcrypt)
  ↓
sp_get_user_data (Stored Procedure)
  ↓
Create session + Set $_SESSION variables
  ↓
Redirect based on user_type:
  - super_admin/owner → adminpanel/
  - staff/others → memberpanel/
```

### JWT Token Login (API)

```
POST /api/v1/auth/login
  ↓
{username, password} in JSON body
  ↓
AuthService::authenticate()
  ↓
Generate JWT token (TokenService)
  ↓
Store session in tbl_user_sessions
  ↓
Return JSON: {success, token, user_data}
```

---

## User Types & Routing

| User Type | Login Redirect | Panel Access |
|-----------|----------------|--------------|
| `super_admin` | `./adminpanel/` | Admin panel + Member panel |
| `owner` | `./adminpanel/` | Admin panel + Member panel |
| `staff` | `./memberpanel/` | Member panel only |
| `distributor` | `./memberpanel/` | Member panel only |

**Auto-routing enforcement:**
- Non-admins accessing `/adminpanel/` → Redirected to `/memberpanel/`
- Admins can access both panels

---

## Session Management

### Session Variables Set on Login

```php
$_SESSION['user_id']           // User primary key
$_SESSION['franchise_id']      // Franchise context (can be NULL for super_admin)
$_SESSION['username']          // Username
$_SESSION['user_type']         // User type (super_admin, owner, staff, etc.)
$_SESSION['auth_token']        // JWT token (if generated)
$_SESSION['last_activity']     // Unix timestamp for timeout tracking
$_SESSION['full_name']         // User's full name
$_SESSION['role_name']         // Display name for role
$_SESSION['franchise_name']    // Franchise name
$_SESSION['currency']          // Franchise currency
$_SESSION['franchise_country'] // Franchise country
```

### Session Timeout

- **Default:** 30 minutes (1800 seconds)
- **Automatic:** Session cleared on timeout
- **Manual:** User can logout via `logout.php`

---

## Permission System (RBAC)

### Permission Checking in PHP

```php
// Check if user has permission (returns boolean)
if (hasPermissionGlobal('INVOICE_CREATE')) {
    // Show create button
}

// Require permission (throws exception if denied)
requirePermissionGlobal('INVOICE_DELETE');
// Code here only runs if user has permission
```

### Permission Checking in UI

```php
<?php if (hasPermissionGlobal('USER_MANAGE')): ?>
  <button>Manage Users</button>
<?php endif; ?>
```

### Super Admin Bypass

- `super_admin` user type **automatically has all permissions**
- No need to assign roles/permissions to super admins
- Stored procedure `sp_get_user_permissions` handles this

---

## Password Management

### Password Hashing

Uses PHP's `password_hash()` with **bcrypt** (BCRYPT algorithm).

```php
// Hash password (automatically generates salt)
$hash = password_hash($plainPassword, PASSWORD_BCRYPT);

// Verify password
if (password_verify($plainPassword, $storedHash)) {
    // Password correct
}
```

### Force Password Change

Users with `force_password_change = 1` are redirected to `change-password.php` after login.

### Forgot Password Flow

```
User → forgot-password.php
  ↓
Enter email
  ↓
Generate reset token (sp_create_password_reset_token)
  ↓
Send email with reset link
  ↓
User clicks link → reset-password.php
  ↓
Enter new password
  ↓
Update password_hash, clear reset token
```

---

## CSRF Protection

All forms include CSRF tokens to prevent Cross-Site Request Forgery attacks.

### Usage

```php
// In controller (before form)
$csrfHelper = new CSRFHelper();
$csrfToken = $csrfHelper->generateToken();

// In form HTML
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

// In form handler (POST)
$csrfHelper->validateToken($_POST['csrf_token'] ?? '');
```

**If validation fails:** Exception thrown, form rejected.

---

## Cookie Management

### Remember Me Feature

When user checks "Remember me":
1. Generate long-lived session token (30 days)
2. Store in `tbl_user_sessions` with `remember_me = 1`
3. Set encrypted cookie `remember_token`

```php
// Create remember me cookie
$cookieHelper->createSecureCookie('remember_token', $token, 86400 * 30);
```

### Cookie Security

- **HttpOnly:** Prevents JavaScript access
- **Secure:** Only sent over HTTPS (in production)
- **SameSite:** Prevents CSRF via cookies
- **Encrypted:** Cookie value is encrypted with `COOKIE_ENCRYPTION_KEY`

---

## Stored Procedures

### sp_authenticate_user

**Purpose:** Authenticate user by username/email and franchise

**Input:**
- `p_username` - Username or email
- `p_franchise_id` - Franchise context (NULL for super admins)

**Output:**
- `p_user_id` - User ID (0 if not found)
- `p_status` - Status code (SUCCESS, USER_NOT_FOUND, ACCOUNT_INACTIVE)
- `p_password_hash` - Stored password hash for verification

### sp_get_user_data

**Purpose:** Retrieve user profile with roles and permissions

**Input:**
- `p_user_id` - User ID

**Output:**
- User record with aggregated roles and permissions
- Returns franchise details (name, currency, country, language)

### sp_get_user_permissions

**Purpose:** Get all permission codes for a user

**Input:**
- `p_user_id` - User ID
- `p_franchise_id` - Franchise context

**Output:**
- Comma-separated permission codes (e.g., `"USER_VIEW,USER_CREATE,INVOICE_VIEW"`)

**Super Admin Bypass:**
- If `user_type = 'super_admin'`, returns ALL permissions from `tbl_permissions`

### sp_create_user_session

**Purpose:** Create session record for tracking

**Input:**
- `p_user_id`, `p_franchise_id`, `p_token`, `p_ip_address`, `p_user_agent`, `p_expires_at`, `p_remember_me`

**Output:**
- Inserts record into `tbl_user_sessions`

### sp_log_failed_login

**Purpose:** Track failed login attempts

**Input:**
- `p_username`, `p_ip_address`, `p_user_agent`, `p_attempt_time`

**Output:**
- Inserts record into `tbl_login_attempts`

### sp_increment_failed_attempts

**Purpose:** Increment failed login counter

**Input:**
- `p_user_id`

**Output:**
- Increments `failed_login_attempts` in `tbl_users`

### sp_reset_failed_attempts

**Purpose:** Reset failed login counter after successful login

**Input:**
- `p_user_id`

**Output:**
- Sets `failed_login_attempts = 0` in `tbl_users`

---

## Error Codes

| Code | Meaning | Action |
|------|---------|--------|
| `SUCCESS` | Login successful | Redirect to panel |
| `USER_NOT_FOUND` | Username/email not found | Show "No account found" |
| `INVALID_PASSWORD` | Password incorrect | Show "Invalid password" |
| `ACCOUNT_INACTIVE` | User status != 'active' | Show "Account inactive" |
| `ACCOUNT_LOCKED` | Too many failed attempts | Show "Account locked" |
| `ACCOUNT_SUSPENDED` | Account suspended | Show "Account suspended" |
| `SESSION_ERROR` | Session creation failed | Show "Unable to create session" |

---

## Security Features

### 1. Password Security
- **Bcrypt hashing** with automatic salt generation
- **No plaintext storage** - passwords never stored in plain text
- **Failed attempt tracking** - Lockout after multiple failures

### 2. Session Security
- **Session regeneration** on login (prevents session fixation)
- **Timeout tracking** - Auto-logout after 30 minutes of inactivity
- **Secure cookies** - HttpOnly, Secure, SameSite

### 3. CSRF Protection
- **Token validation** on all state-changing requests
- **Session-based tokens** - Unique per session
- **Automatic invalidation** on session timeout

### 4. SQL Injection Protection
- **Prepared statements** - All queries use PDO prepared statements
- **Stored procedures** - Critical auth logic in database
- **Input sanitization** - All user input validated and escaped

### 5. XSS Protection
- **Output escaping** - All user input escaped with `htmlspecialchars()`
- **Content Security Policy** - Can be added via headers
- **No inline JavaScript** - Separate script tags

---

## API Authentication

### Login Endpoint

```
POST /api/v1/auth/login
Content-Type: application/json

{
  "username": "root",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "username": "root",
      "user_type": "super_admin",
      "franchise_id": null
    }
  }
}
```

### Using JWT Token

```
GET /api/v1/protected-resource
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

### Logout Endpoint

```
POST /api/v1/auth/logout
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

### Refresh Token

```
POST /api/v1/auth/refresh
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

## Customization

### Adding New User Types

1. Update `user_type` enum in `tbl_users`:
   ```sql
   ALTER TABLE tbl_users MODIFY user_type ENUM('super_admin','owner','distributor','staff','manager') NOT NULL;
   ```

2. Update routing in `src/config/auth.php`:
   ```php
   if ($_SESSION['user_type'] === 'manager') {
       header('Location: ./managerpanel/');
   }
   ```

### Adding New Permissions

```sql
INSERT INTO tbl_permissions (name, code, module, description)
VALUES ('Delete Invoice', 'INVOICE_DELETE', 'SALES', 'Permanently delete invoices');
```

### Creating New Roles

```sql
-- Create role
INSERT INTO tbl_global_roles (code, name, description, is_system)
VALUES ('SALES_MANAGER', 'Sales Manager', 'Manage sales operations', 0);

-- Assign permissions to role
INSERT INTO tbl_global_role_permissions (global_role_id, permission_id)
VALUES
  (2, 10), -- INVOICE_VIEW
  (2, 11), -- INVOICE_CREATE
  (2, 12); -- INVOICE_UPDATE
```

### Assigning Roles to Users

```sql
INSERT INTO tbl_user_roles (franchise_id, user_id, global_role_id, assigned_by)
VALUES (1, 5, 2, 1); -- Assign SALES_MANAGER role to user 5
```

---

## Troubleshooting

### "Session expired" error
- Check session timeout (default 30 min)
- Verify `session.gc_maxlifetime` in php.ini
- Ensure cookies are enabled in browser

### "Account inactive" error
- Check `status` field in `tbl_users`
- Should be `'active'` for login
- Update: `UPDATE tbl_users SET status = 'active' WHERE id = X;`

### "Database connection failed" error
- Verify `.env` database credentials
- Check MySQL server is running
- Test connection: `php -r "new PDO('mysql:host=localhost;dbname=saas_seeder', 'root', '');"`

### "Class not found" errors
- Run `composer install`
- Check `vendor/` directory exists
- Verify autoloader: `require_once '../vendor/autoload.php';`

### CSRF token validation fails
- Ensure session is started before form rendering
- Check CSRF token is in form: `<input type="hidden" name="csrf_token" value="...">`
- Verify POST data includes `csrf_token`

---

**Last Updated:** 2026-02-01
**Version:** 1.0
