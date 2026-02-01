# Session Variable Prefix System

## Overview

All session variables in the SaaS Seeder Template use the prefix `saas_app_` to avoid conflicts and make it easy to identify and customize for specific applications.

**Example:**
```php
// Instead of: $_SESSION['user_id']
// We use: $_SESSION['saas_app_user_id']
```

---

## Why Use a Prefix?

### 1. **Avoid Conflicts**
Prevents session variable name collisions when:
- Multiple apps run on the same domain
- Integrating third-party libraries
- Sharing sessions between subdomains

### 2. **Easy Customization**
When creating a new SaaS app, simply find/replace:
- Find: `saas_app_`
- Replace: `yourapp_` (e.g., `invoice_`, `crm_`, `academy_`)

### 3. **Clear Namespace**
Makes it obvious which session variables belong to your app vs. third-party code.

---

## How It Works

### Session Helper Functions

The template provides helper functions in `src/config/session.php`:

```php
// Set a session variable
setSession('user_id', 123);
// Stores as: $_SESSION['saas_app_user_id'] = 123

// Get a session variable
$userId = getSession('user_id');
// Retrieves: $_SESSION['saas_app_user_id']

// Check if session variable exists
if (hasSession('user_id')) {
    // Variable exists
}

// Remove a session variable
unsetSession('user_id');
// Removes: $_SESSION['saas_app_user_id']
```

---

## Session Variables Used

### Authentication Variables

| Helper Function | Actual Session Key | Purpose |
|-----------------|-------------------|---------|
| `getSession('user_id')` | `saas_app_user_id` | User's database ID |
| `getSession('username')` | `saas_app_username` | Username |
| `getSession('user_type')` | `saas_app_user_type` | User type (super_admin, owner, staff) |
| `getSession('franchise_id')` | `saas_app_franchise_id` | Current franchise context |
| `getSession('auth_token')` | `saas_app_auth_token` | JWT authentication token |
| `getSession('last_activity')` | `saas_app_last_activity` | Timestamp for session timeout |

### User Information Variables

| Helper Function | Actual Session Key | Purpose |
|-----------------|-------------------|---------|
| `getSession('full_name')` | `saas_app_full_name` | User's full name |
| `getSession('email')` | `saas_app_email` | User's email |
| `getSession('role_name')` | `saas_app_role_name` | Display name for user's role |
| `getSession('franchise_name')` | `saas_app_franchise_name` | Franchise/org name |
| `getSession('franchise_country')` | `saas_app_franchise_country` | Franchise country |
| `getSession('currency')` | `saas_app_currency` | Franchise currency |

### System Variables

| Helper Function | Actual Session Key | Purpose |
|-----------------|-------------------|---------|
| `getSession('redirect_after_login')` | `saas_app_redirect_after_login` | URL to redirect after login |
| `getSession('force_password_change')` | `saas_app_force_password_change` | Whether user must change password |

---

## Customizing for Your App

### Step 1: Choose Your Prefix

Decide on a prefix that represents your app:
- **Invoice App:** `invoice_`
- **CRM App:** `crm_`
- **Academy App:** `academy_`
- **Healthcare App:** `medic_`

### Step 2: Global Find/Replace

Use your IDE's find/replace feature:

**Find:** `saas_app_`
**Replace with:** `yourapp_`

**Files to update:**
- `src/config/session.php` (update `SESSION_PREFIX` constant)
- All PHP files using session variables
- All documentation mentioning session variables

### Step 3: Verify

Check these key locations:
```php
// src/config/session.php
define('SESSION_PREFIX', 'yourapp_'); // ✓ Updated

// Example usage remains the same
setSession('user_id', 123);
// Now stores as: $_SESSION['yourapp_user_id']
```

---

## Usage Examples

### Setting Session Variables (Login)

```php
// After successful login
setSession('user_id', $userId);
setSession('username', $username);
setSession('user_type', 'staff');
setSession('franchise_id', $franchiseId);
setSession('last_activity', time());
```

### Getting Session Variables

```php
// Check if user is logged in
if (hasSession('user_id')) {
    $userId = getSession('user_id');
    $userType = getSession('user_type', 'guest'); // With default value
}
```

### Checking User Type

```php
$userType = getSession('user_type');

if ($userType === 'super_admin') {
    // Admin functionality
} elseif ($userType === 'staff') {
    // Staff functionality
}
```

### Session Timeout Check

```php
if (hasSession('last_activity')) {
    $timeout = time() - getSession('last_activity');

    if ($timeout > 1800) { // 30 minutes
        clearPrefixedSession();
        header('Location: sign-in.php?msg=session_expired');
        exit();
    }

    setSession('last_activity', time());
}
```

### Logout

```php
// Clear all prefixed session variables
clearPrefixedSession();
session_destroy();
header('Location: sign-in.php');
```

---

## Helper Functions Reference

### `initSession()`
Starts session with secure settings:
```php
initSession();
// - HttpOnly cookies
// - Secure flag (HTTPS)
// - SameSite: Strict
// - 30-minute garbage collection
```

### `setSession(string $key, mixed $value)`
Set a prefixed session variable:
```php
setSession('user_id', 123);
setSession('preferences', ['theme' => 'dark']);
```

### `getSession(string $key, mixed $default = null)`
Get a prefixed session variable:
```php
$userId = getSession('user_id');
$theme = getSession('theme', 'light'); // With default
```

### `hasSession(string $key): bool`
Check if prefixed session variable exists:
```php
if (hasSession('user_id')) {
    // User is logged in
}
```

### `unsetSession(string $key)`
Remove a specific prefixed session variable:
```php
unsetSession('temp_data');
```

### `getAllSession(): array`
Get all prefixed session data (keys without prefix):
```php
$sessionData = getAllSession();
// Returns: ['user_id' => 123, 'username' => 'john', ...]
```

### `clearPrefixedSession()`
Clear all variables with the prefix:
```php
clearPrefixedSession();
// Only removes saas_app_* variables
// Preserves other session variables (e.g., from third-party libs)
```

### `regenerateSession()`
Regenerate session ID for security:
```php
regenerateSession();
// Call after login, privilege elevation, etc.
```

---

## Security Benefits

### 1. **Namespace Isolation**
Your app's session variables are isolated from:
- Third-party libraries
- Legacy code
- Other applications on the same domain

### 2. **Easy Cleanup**
`clearPrefixedSession()` only clears your app's variables:
```php
// Doesn't affect third-party session variables
clearPrefixedSession();
```

### 3. **Session Regeneration**
Helper function for secure session ID regeneration:
```php
regenerateSession(); // Prevents session fixation attacks
```

---

## Migration from Non-Prefixed Sessions

If you have existing code using raw `$_SESSION`:

### Before (Old):
```php
$_SESSION['user_id'] = 123;
$userId = $_SESSION['user_id'];
if (isset($_SESSION['user_id'])) { }
unset($_SESSION['user_id']);
```

### After (New):
```php
setSession('user_id', 123);
$userId = getSession('user_id');
if (hasSession('user_id')) { }
unsetSession('user_id');
```

### Quick Migration Script:

Use find/replace with regex:

**Pattern 1 - Set:**
- Find: `\$_SESSION\['([^']+)'\]\s*=\s*([^;]+);`
- Replace: `setSession('$1', $2);`

**Pattern 2 - Get:**
- Find: `\$_SESSION\['([^']+)'\]`
- Replace: `getSession('$1')`

**Pattern 3 - Isset:**
- Find: `isset\(\$_SESSION\['([^']+)'\]\)`
- Replace: `hasSession('$1')`

---

## Best Practices

### ✅ DO:
- Use helper functions (`setSession`, `getSession`, etc.)
- Choose meaningful, app-specific prefixes
- Document custom session variables
- Clear session on logout (`clearPrefixedSession()`)

### ❌ DON'T:
- Mix prefixed and non-prefixed session variables
- Access `$_SESSION` directly (use helpers)
- Use generic prefixes like `app_` or `data_`
- Store sensitive data unencrypted in session

---

## Debugging

### View All Session Data

```php
// Get all prefixed session variables
$sessionData = getAllSession();
print_r($sessionData);

// Or inspect raw $_SESSION
foreach ($_SESSION as $key => $value) {
    if (strpos($key, SESSION_PREFIX) === 0) {
        echo "$key => $value\n";
    }
}
```

### Check Session Prefix

```php
echo "Session Prefix: " . SESSION_PREFIX;
// Output: Session Prefix: saas_app_
```

---

## FAQ

### Q: Can I use multiple prefixes in one app?
**A:** Not recommended. Stick to one prefix per app for consistency.

### Q: What if I don't want a prefix?
**A:** Set `SESSION_PREFIX = ''` in `src/config/session.php`, but this loses the namespace benefits.

### Q: Do I need to update the database?
**A:** No. The prefix only affects session storage (in memory/files), not the database.

### Q: Can I change the prefix after deployment?
**A:** Yes, but all users will be logged out since their session keys will change.

---

**Last Updated:** 2026-02-01
**Version:** 1.0
**File:** `src/config/session.php`
