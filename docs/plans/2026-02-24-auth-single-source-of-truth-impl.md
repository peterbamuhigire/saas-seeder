# Auth Single Source of Truth + UI Redesign — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Fix all authentication bugs to enforce a single source of truth for password hashing and session management, then redesign sign-in.php and super-user-dev.php with a modern split-panel layout.

**Architecture:** `PasswordHelper` is the only class that hashes/verifies passwords. `AuthService::authenticate()` is the only place sessions are written after login. All session reads/writes go through `getSession()`/`setSession()`. The UI uses Tabler's Bootstrap 5 grid in a split-panel layout — random background image on the left, clean form on the right.

**Tech Stack:** PHP 8+, Tabler 1.x (Bootstrap 5), SweetAlert2, Argon2ID via PasswordHelper, PDO, session prefix system (`saas_app_`).

**Asset paths (ALWAYS use these):**
- CSS: `./assets/tabler/css/tabler.min.css`
- JS: `./assets/tabler/js/tabler.min.js`
- SweetAlert2 CSS: `https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css`
- SweetAlert2 JS: `https://cdn.jsdelivr.net/npm/sweetalert2@11`

**Verification method:** Manual browser testing at `http://localhost` (WampServer). Check DB with phpMyAdmin or MySQL CLI.

---

## Task 1: Fix `AuthResult::isSuccessful()`

The `isSuccessful()` method checks for `'Success'` but `AuthService` always sets `'SUCCESS'`. This means the method silently always returns false.

**Files:**
- Modify: `src/Auth/DTO/AuthResult.php`

**Step 1: Make the fix**

In `src/Auth/DTO/AuthResult.php`, change line in `isSuccessful()`:

```php
// BEFORE:
public function isSuccessful(): bool
{
    return $this->status === 'Success';
}

// AFTER:
public function isSuccessful(): bool
{
    return $this->status === 'SUCCESS';
}
```

**Step 2: Verify**

Open `src/Auth/Services/AuthService.php` and confirm that every `new AuthResult(...)` on the success path passes `'SUCCESS'` as the status argument. You should see:
```php
return new AuthResult(
    $result['user_id'],
    $userData['franchise_id'],
    $userData['username'],
    'SUCCESS',   // ← this
    ...
);
```
Also confirm all failure paths return uppercase codes like `'USER_NOT_FOUND'`, `'INVALID_PASSWORD'`, etc.

**Step 3: Commit**

```bash
git add src/Auth/DTO/AuthResult.php
git commit -m "fix: normalize AuthResult::isSuccessful() to check 'SUCCESS' status"
```

---

## Task 2: Fix `CSRFHelper` to use session prefix

`CSRFHelper` uses raw `$_SESSION['csrf_token']` bypassing the `saas_app_` prefix. When `clearPrefixedSession()` is called on logout, the CSRF token survives. Fix it to use the session helper functions.

**Files:**
- Modify: `src/Auth/Helpers/CSRFHelper.php`

**Step 1: Ensure session.php is loaded**

`CSRFHelper` is used in pages that all include `auth.php`, which includes `session.php` and calls `initSession()`. So `setSession()`/`getSession()` are always available when CSRFHelper is used. No change needed to the include chain.

**Step 2: Replace all raw `$_SESSION` in CSRFHelper**

Replace the entire file content:

```php
<?php
namespace App\Auth\Helpers;

class CSRFHelper
{
    /**
     * Generate a new CSRF token or return existing one
     */
    public function generateToken(): string
    {
        if (!hasSession('csrf_token')) {
            setSession('csrf_token', bin2hex(random_bytes(32)));
        }
        return getSession('csrf_token');
    }

    /**
     * Validate provided CSRF token against stored token
     */
    public function validateToken(?string $token): bool
    {
        if (empty($token) || !hasSession('csrf_token')) {
            throw new \Exception('Invalid or missing security token. Please refresh and try again.');
        }

        if (!hash_equals(getSession('csrf_token'), $token)) {
            throw new \Exception('Security token mismatch. Please refresh and try again.');
        }

        return true;
    }

    /**
     * Refresh the CSRF token (call after successful form submission)
     */
    public function refreshToken(): string
    {
        setSession('csrf_token', bin2hex(random_bytes(32)));
        return getSession('csrf_token');
    }

    /**
     * Remove the CSRF token
     */
    public function removeToken(): void
    {
        unsetSession('csrf_token');
    }
}
```

Note: `validateToken()` now throws instead of returning false, matching how sign-in.php already uses it (inside a try/catch).

**Step 3: Verify sign-in.php uses it correctly**

Open `public/sign-in.php` and confirm the CSRF validation is inside a try/catch:
```php
try {
    $csrfHelper->validateToken($_POST['csrf_token'] ?? '');
    // ...
} catch (\Exception $e) {
    $error = $e->getMessage();
}
```
It is — no changes needed there.

**Step 4: Commit**

```bash
git add src/Auth/Helpers/CSRFHelper.php
git commit -m "fix: CSRFHelper uses session prefix system instead of raw \$_SESSION"
```

---

## Task 3: Fix `logout.php` — use `getSession()` for token

`logout.php` line 19 uses `$_SESSION['auth_token']` directly. The token is stored as `$_SESSION['saas_app_auth_token']`, so token invalidation silently fails every logout.

**Files:**
- Modify: `public/logout.php`

**Step 1: Fix the token retrieval**

Replace the entire file:

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';

use App\Config\Database;
use App\Auth\Services\{AuthService, TokenService, PermissionService};
use App\Auth\Helpers\{CookieHelper, PasswordHelper};
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$dotenv->required(['COOKIE_DOMAIN', 'COOKIE_ENCRYPTION_KEY', 'APP_ENV'])->notEmpty();

try {
    // Use getSession() — never raw $_SESSION
    $token = getSession('auth_token');

    if ($token) {
        $db = (new Database())->getConnection();
        $authService = new AuthService(
            $db,
            new TokenService($db),
            new PermissionService($db),
            new PasswordHelper(),
            new CookieHelper()
        );

        $authService->logout($token);
    }

    // Clear all prefixed session variables, then destroy
    clearPrefixedSession();
    session_destroy();

    header('Location: ./sign-in.php?msg=logout');
    exit();

} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    clearPrefixedSession();
    session_destroy();
    header('Location: ./sign-in.php?msg=logout');
    exit();
}
```

**Step 2: Test logout**

1. Log in via `sign-in.php`
2. Check `tbl_user_sessions` in the DB — note the session record
3. Click logout
4. Check `tbl_user_sessions` — the session record should now be marked as expired/deleted (depending on `AuthService::logout()` → `TokenService::invalidateToken()`)
5. Confirm you land on `sign-in.php?msg=logout`

**Step 3: Commit**

```bash
git add public/logout.php
git commit -m "fix: logout.php uses getSession() to correctly retrieve prefixed auth token"
```

---

## Task 4: Fix `sign-in.php` — remove duplicate session writes

`AuthService::authenticate()` already writes all session variables. `sign-in.php` then writes them again. Remove the duplicate block and let AuthService be the single owner.

**Files:**
- Modify: `public/sign-in.php` (PHP logic only, not the HTML — that comes in Task 6)

**Step 1: Find the duplicate session block**

In `sign-in.php`, after `$result = $authService->authenticate($loginDTO);`, there is a block starting with:
```php
if ($result->getStatus() == 'SUCCESS') {
    regenerateSession();
    setSession('user_id', $result->getUserId());
    setSession('franchise_id', $result->getFranchiseId());
    setSession('username', $result->getUsername());
    setSession('user_type', $result->getUserData()['user_type'] ?? 'staff');
    setSession('auth_token', $result->getToken());
    setSession('last_activity', time());
    setSession('franchise_country', $result->getUserData()['country'] ?? '');
    setSession('franchise_name', $result->getUserData()['franchise_name'] ?? '');
    setSession('currency', $result->getUserData()['currency'] ?? '');
    setSession('full_name', $result->getUserData()['full_name'] ?? $result->getUsername());
    setSession('role_name', $result->getUserData()['role_name'] ?? ...);
    // remember me block
    // force_password_change check
    $userName = ...;
    $loginSuccess = true;
}
```

**Step 2: Replace the success block**

Replace the entire `if ($result->getStatus() == 'SUCCESS')` block with this minimal version:

```php
if ($result->getStatus() === 'SUCCESS') {
    // AuthService::authenticate() already wrote all session variables.
    // We only need to regenerate the session ID here for security.
    regenerateSession();

    // Handle remember me cookie (AuthService doesn't set cookies)
    if ($rememberMe) {
        try {
            $cookieHelper = new CookieHelper();
            $session = $authService->createUserSession($result->getUserId(), true);
            $cookieHelper->createSecureCookie('remember_token', $session->getToken(), 86400 * 30);
        } catch (\Exception $e) {
            error_log('Remember me failed: ' . $e->getMessage());
            // Non-fatal — user is still logged in
        }
    }

    // Check for forced password change
    if ((getSession('force_password_change') ?? 0) == 1) {
        header('Location: ./change-password.php');
        exit();
    }

    $userName = getSession('full_name') ?: $result->getUsername();
    $loginSuccess = true;
} else {
    $errorMessages = [
        'USER_NOT_FOUND'    => 'No account found with this username or email.',
        'INVALID_PASSWORD'  => 'Invalid password provided.',
        'ACCOUNT_LOCKED'    => 'Account locked due to multiple failed login attempts.',
        'ACCOUNT_INACTIVE'  => 'Account is currently inactive.',
        'ACCOUNT_SUSPENDED' => 'Account has been suspended.',
        'SESSION_ERROR'     => 'Unable to create login session.',
        'DATABASE_ERROR'    => 'System error: Database connection failed.',
        'VALIDATION_ERROR'  => 'Invalid login credentials format.',
    ];
    $error = $errorMessages[$result->getStatus()] ?? 'Login failed. Please try again.';
}
```

**Step 3: Verify**

1. Log in with valid credentials
2. After login, dump session to verify all keys are set (quick test: add `var_dump($_SESSION); die();` after `regenerateSession()`, then remove after checking)
3. Confirm `saas_app_user_id`, `saas_app_franchise_id`, `saas_app_user_type`, `saas_app_full_name`, `saas_app_auth_token`, etc. are all present

**Step 4: Commit**

```bash
git add public/sign-in.php
git commit -m "fix: remove duplicate session writes from sign-in.php; AuthService is sole session owner"
```

---

## Task 5: Create `change-password.php`

Referenced by the `force_password_change` login flow but missing. Create it.

**Files:**
- Create: `public/change-password.php`

**Step 1: Create the file**

```php
<?php
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth\Helpers\{PasswordHelper, CSRFHelper};
use App\Config\Database;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

requireAuth();

$csrfHelper = new CSRFHelper();
$csrfToken  = $csrfHelper->generateToken();
$loginBackground = \App\Helpers\UiHelper::getRandomLoginBackground();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $csrfHelper->validateToken($_POST['csrf_token'] ?? '');

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword      = $_POST['new_password'] ?? '';
        $confirmPassword  = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            throw new \Exception('All fields are required.');
        }

        if ($newPassword !== $confirmPassword) {
            throw new \Exception('New passwords do not match.');
        }

        $passwordHelper = new PasswordHelper();

        $errors = $passwordHelper->validatePasswordStrength($newPassword);
        if (!empty($errors)) {
            throw new \Exception(implode(' ', $errors));
        }

        $db     = (new Database())->getConnection();
        $userId = (int) getSession('user_id');

        // Verify current password
        $stmt = $db->prepare("SELECT password_hash FROM tbl_users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row || !$passwordHelper->verifyPassword($currentPassword, $row['password_hash'])) {
            throw new \Exception('Current password is incorrect.');
        }

        // Hash new password via PasswordHelper (single source of truth)
        $newHash = $passwordHelper->hashPassword($newPassword);

        $stmt = $db->prepare(
            "UPDATE tbl_users SET password_hash = ?, force_password_change = 0, updated_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$newHash, $userId]);

        // Clear the force_password_change session flag
        setSession('force_password_change', 0);

        $success = 'Password changed successfully.';

        // Redirect to dashboard after a moment
        header('refresh:2;url=./index.php');

    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Change Password';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?php echo htmlspecialchars($pageTitle); ?> - SaaS Seeder</title>
  <link href="./assets/tabler/css/tabler.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    @import url("https://rsms.me/inter/inter.css");
    .auth-split { min-height: 100vh; }
    .auth-left {
      background-image: url('<?php echo htmlspecialchars($loginBackground); ?>');
      background-size: cover;
      background-position: center;
      position: relative;
    }
    .auth-left::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.4) 100%);
    }
    .auth-left-content { position: relative; z-index: 1; }
    .auth-right { display: flex; align-items: center; justify-content: center; background: #fff; }
    .auth-form-wrap { width: 100%; max-width: 420px; padding: 2rem; }
  </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="row g-0 auth-split">
  <!-- Left: background image -->
  <div class="col-lg-6 d-none d-lg-flex auth-left">
    <div class="auth-left-content p-5 d-flex flex-column justify-content-between h-100 text-white">
      <div>
        <h2 class="fw-bold">SaaS Seeder</h2>
      </div>
      <div>
        <h3 class="fw-semibold mb-2">Secure Your Account</h3>
        <p class="opacity-75">Choose a strong password with uppercase, lowercase, numbers, and special characters.</p>
      </div>
      <small class="opacity-50">Copyright &copy; <?php echo date('Y'); ?> Chwezi Core Systems</small>
    </div>
  </div>

  <!-- Right: form -->
  <div class="col-12 col-lg-6 auth-right">
    <div class="auth-form-wrap">
      <div class="text-center mb-4">
        <h1 class="text-primary fw-bold">SaaS Seeder</h1>
      </div>
      <h2 class="mb-1">Change Password</h2>
      <p class="text-muted mb-4">
        <?php if ((getSession('force_password_change') ?? 0) == 1): ?>
          You must change your password before continuing.
        <?php else: ?>
          Update your account password below.
        <?php endif; ?>
      </p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible">
          <?php echo htmlspecialchars($error); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success">
          <?php echo htmlspecialchars($success); ?> Redirecting...
        </div>
      <?php endif; ?>

      <form method="POST" action="./change-password.php" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

        <div class="mb-3">
          <label class="form-label">Current Password</label>
          <div class="input-group input-group-flat">
            <input type="password" name="current_password" id="current_password" class="form-control" required>
            <span class="input-group-text">
              <a href="#" class="link-secondary toggle-pw" data-target="current_password">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
              </a>
            </span>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">New Password</label>
          <div class="input-group input-group-flat">
            <input type="password" name="new_password" id="new_password" class="form-control" required>
            <span class="input-group-text">
              <a href="#" class="link-secondary toggle-pw" data-target="new_password">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
              </a>
            </span>
          </div>
          <div id="pw-strength" class="mt-2" style="display:none;">
            <div class="progress" style="height:4px;">
              <div id="pw-strength-bar" class="progress-bar" role="progressbar" style="width:0%"></div>
            </div>
            <small id="pw-strength-text" class="text-muted"></small>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label">Confirm New Password</label>
          <div class="input-group input-group-flat">
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            <span class="input-group-text">
              <a href="#" class="link-secondary toggle-pw" data-target="confirm_password">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
              </a>
            </span>
          </div>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-lg">Change Password</button>
        </div>
      </form>

      <?php if ((getSession('force_password_change') ?? 0) != 1): ?>
        <div class="text-center mt-3">
          <a href="./index.php" class="text-muted">Cancel</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="./assets/tabler/js/tabler.min.js"></script>
<script>
  // Password visibility toggle
  document.querySelectorAll('.toggle-pw').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const field = document.getElementById(btn.dataset.target);
      field.type = field.type === 'password' ? 'text' : 'password';
    });
  });

  // Password strength meter
  const newPw = document.getElementById('new_password');
  const strengthBar = document.getElementById('pw-strength-bar');
  const strengthText = document.getElementById('pw-strength-text');
  const strengthWrap = document.getElementById('pw-strength');

  newPw.addEventListener('input', function() {
    const val = this.value;
    if (!val) { strengthWrap.style.display = 'none'; return; }
    strengthWrap.style.display = 'block';

    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[!@#$%^&*()\-_=+{};:,<.>]/.test(val)) score++;

    const levels = [
      { pct: 20, cls: 'bg-danger',  label: 'Very weak' },
      { pct: 40, cls: 'bg-warning', label: 'Weak' },
      { pct: 60, cls: 'bg-info',    label: 'Fair' },
      { pct: 80, cls: 'bg-primary', label: 'Strong' },
      { pct: 100, cls: 'bg-success', label: 'Very strong' },
    ];
    const lvl = levels[score - 1] || levels[0];
    strengthBar.style.width = lvl.pct + '%';
    strengthBar.className = 'progress-bar ' + lvl.cls;
    strengthText.textContent = lvl.label;
  });

  // Confirm password match validation
  document.getElementById('confirm_password').addEventListener('input', function() {
    if (this.value && this.value !== newPw.value) {
      this.setCustomValidity('Passwords do not match');
    } else {
      this.setCustomValidity('');
    }
  });
</script>
</body>
</html>
```

**Step 2: Test force_password_change flow**

1. In the DB, set `force_password_change = 1` for a test user:
   ```sql
   UPDATE tbl_users SET force_password_change = 1 WHERE username = 'your_test_user';
   ```
2. Log in as that user — should redirect to `change-password.php`
3. Enter current + new password → should update DB and redirect to `index.php`
4. Verify `force_password_change = 0` in DB after submission

**Step 3: Commit**

```bash
git add public/change-password.php
git commit -m "feat: add change-password.php for forced and voluntary password changes"
```

---

## Task 6: Redesign `sign-in.php` — split-panel modern UI

Full redesign of the login page. PHP logic stays the same (was fixed in Task 4); only the HTML/CSS changes.

**Files:**
- Modify: `public/sign-in.php`

**Step 1: Replace the entire HTML section**

Keep all the PHP logic at the top (lines 1–~115 from the current file, after the fixes from Task 4). Replace everything from `<!doctype html>` onwards with:

```php
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Sign In — SaaS Seeder</title>
  <link href="./assets/tabler/css/tabler.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    @import url("https://rsms.me/inter/inter.css");
    *, *::before, *::after { box-sizing: border-box; }

    html, body { height: 100%; margin: 0; }

    .auth-split {
      display: flex;
      min-height: 100vh;
    }

    /* ── Left panel ── */
    .auth-left {
      flex: 0 0 55%;
      background-image: url('<?php echo htmlspecialchars($loginBackground); ?>');
      background-size: cover;
      background-position: center;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 3rem;
    }
    .auth-left::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(
        160deg,
        rgba(6, 111, 209, 0.55) 0%,
        rgba(0, 0, 0, 0.72) 100%
      );
      pointer-events: none;
    }
    .auth-left > * { position: relative; z-index: 1; }

    .auth-left-logo {
      font-size: 1.6rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: -0.5px;
      text-decoration: none;
    }
    .auth-left-tagline {
      color: rgba(255,255,255,0.92);
    }
    .auth-left-tagline h2 {
      font-size: 2rem;
      font-weight: 700;
      line-height: 1.25;
      margin-bottom: .75rem;
    }
    .auth-left-tagline p {
      font-size: 1rem;
      opacity: .8;
      max-width: 380px;
    }
    .auth-left-footer {
      font-size: .8rem;
      color: rgba(255,255,255,.45);
    }

    /* ── Right panel ── */
    .auth-right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      padding: 2rem 1.5rem;
    }
    .auth-form-wrap {
      width: 100%;
      max-width: 400px;
    }
    .auth-form-wrap .brand-mobile {
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--tblr-primary);
      margin-bottom: 2rem;
      text-align: center;
    }
    .auth-form-wrap h2 {
      font-size: 1.6rem;
      font-weight: 700;
      margin-bottom: .3rem;
    }
    .auth-form-wrap .subtitle {
      color: #6b7280;
      margin-bottom: 1.75rem;
      font-size: .95rem;
    }

    .form-floating-label { position: relative; margin-bottom: 1rem; }
    .form-floating-label input {
      height: 52px;
      padding: 1.2rem .875rem .4rem;
      font-size: .95rem;
      border-radius: 8px;
      border: 1.5px solid #e5e7eb;
      transition: border-color .15s;
    }
    .form-floating-label input:focus {
      border-color: var(--tblr-primary);
      box-shadow: 0 0 0 3px rgba(6,111,209,.1);
    }
    .form-floating-label label {
      position: absolute;
      top: 50%;
      left: .9rem;
      transform: translateY(-50%);
      font-size: .9rem;
      color: #9ca3af;
      transition: all .15s;
      pointer-events: none;
      background: transparent;
    }
    .form-floating-label input:focus ~ label,
    .form-floating-label input:not(:placeholder-shown) ~ label {
      top: .55rem;
      font-size: .7rem;
      color: var(--tblr-primary);
      transform: none;
    }
    .pw-wrap { position: relative; }
    .pw-wrap input { padding-right: 3rem; }
    .pw-toggle {
      position: absolute;
      right: .875rem;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #9ca3af;
      background: none;
      border: none;
      padding: 0;
      line-height: 1;
    }
    .pw-toggle:hover { color: #374151; }

    .btn-signin {
      height: 50px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 8px;
      letter-spacing: .01em;
    }

    .divider { position: relative; margin: 1.25rem 0; text-align: center; }
    .divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0; right: 0;
      height: 1px;
      background: #e5e7eb;
    }
    .divider span {
      position: relative;
      background: #fff;
      padding: 0 .75rem;
      font-size: .8rem;
      color: #9ca3af;
    }

    @media (max-width: 991.98px) {
      .auth-left { display: none; }
      .auth-right { background: #f9fafb; }
    }
  </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($loginSuccess): ?>
<!-- ── Success state ── -->
<div class="auth-split" style="justify-content:center;align-items:center;background:#f9fafb;">
  <div class="text-center">
    <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;" role="status"></div>
    <h3 class="fw-semibold">Signing you in…</h3>
  </div>
</div>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Welcome back!',
    html: 'Hello, <strong><?php echo htmlspecialchars($userName ?? 'User'); ?></strong>. Taking you to your dashboard.',
    timer: 2200,
    timerProgressBar: true,
    showConfirmButton: false,
    allowOutsideClick: false,
  }).then(() => { window.location.href = './index.php'; });
</script>

<?php else: ?>
<!-- ── Login form ── -->
<div class="auth-split">

  <!-- Left panel -->
  <div class="auth-left">
    <a href="." class="auth-left-logo">SaaS Seeder</a>
    <div class="auth-left-tagline">
      <h2>Build your SaaS<br>faster than ever.</h2>
      <p>Multi-tenant architecture, role-based access control, and a clean three-tier panel system — ready out of the box.</p>
    </div>
    <div class="auth-left-footer">
      &copy; <?php echo date('Y'); ?> Chwezi Core Systems
    </div>
  </div>

  <!-- Right panel -->
  <div class="auth-right">
    <div class="auth-form-wrap">

      <!-- Mobile brand (hidden on desktop) -->
      <div class="brand-mobile d-lg-none">SaaS Seeder</div>

      <h2>Sign in</h2>
      <p class="subtitle">Enter your credentials to access your account.</p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" role="alert" style="border-radius:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
          <div><?php echo htmlspecialchars($error); ?></div>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3" role="alert" style="border-radius:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
          <div><?php echo htmlspecialchars($success); ?></div>
        </div>
      <?php endif; ?>

      <form action="./sign-in.php" method="POST" autocomplete="off" novalidate id="loginForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

        <div class="form-floating-label">
          <input
            type="text"
            name="username"
            id="username"
            class="form-control w-100"
            placeholder=" "
            autocomplete="off"
            required
            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
          >
          <label for="username">Username or Email</label>
        </div>

        <div class="form-floating-label">
          <div class="pw-wrap">
            <input
              type="password"
              name="password"
              id="password"
              class="form-control w-100"
              placeholder=" "
              autocomplete="off"
              required
            >
            <label for="password">Password</label>
            <button type="button" class="pw-toggle" id="togglePassword" aria-label="Toggle password visibility">
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
          <label class="form-check mb-0">
            <input type="checkbox" name="remember" class="form-check-input">
            <span class="form-check-label" style="font-size:.875rem;">Remember me</span>
          </label>
          <a href="./forgot-password.php" style="font-size:.875rem;" class="text-muted">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-signin" id="submitBtn">
          Sign In
        </button>
      </form>

      <p class="text-center text-muted mt-4" style="font-size:.78rem;">
        &copy; <?php echo date('Y'); ?> <a href="https://chwezi.co.za" target="_blank" class="text-muted">Chwezi Core Systems</a>
      </p>

    </div>
  </div>

</div>
<?php endif; ?>

<script src="./assets/tabler/js/tabler.min.js"></script>
<script>
  // Password visibility toggle
  const pwField   = document.getElementById('password');
  const toggleBtn = document.getElementById('togglePassword');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const isPassword = pwField.type === 'password';
      pwField.type = isPassword ? 'text' : 'password';
      toggleBtn.innerHTML = isPassword
        ? `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>`;
    });
  }

  // Submit button loading state
  const form      = document.getElementById('loginForm');
  const submitBtn = document.getElementById('submitBtn');
  if (form && submitBtn) {
    form.addEventListener('submit', () => {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Signing in…';
    });
  }
</script>
</body>
</html>
```

**Step 2: Verify**

1. Visit `http://localhost/sign-in.php`
2. Left panel shows a random background image with brand overlay
3. Right panel shows clean form with floating labels
4. Test login with valid credentials → SweetAlert success → redirect
5. Test login with wrong password → red error alert
6. Test on a narrow viewport (mobile) — left panel hides, form fills full width

**Step 3: Commit**

```bash
git add public/sign-in.php
git commit -m "feat: redesign sign-in.php with modern split-panel layout"
```

---

## Task 7: Redesign `super-user-dev.php` — split-panel UI

Same split-panel treatment. Left panel: dark branded security-warning panel. Right panel: create super admin form.

**Files:**
- Modify: `public/super-user-dev.php`

**Step 1: Replace entire file**

```php
<?php
/**
 * Super User Development Tool
 * SECURITY WARNING: Remove or restrict access to this file in production!
 */
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth\Helpers\{PasswordHelper, CSRFHelper};
use App\Config\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'COOKIE_ENCRYPTION_KEY', 'APP_ENV'])->notEmpty();

// Need session for CSRF
initSession();

$csrfHelper     = new CSRFHelper();
$csrfToken      = $csrfHelper->generateToken();
$error          = '';
$success        = '';
$createSuccess  = false;
$createdUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $csrfHelper->validateToken($_POST['csrf_token'] ?? '');

        $username        = trim($_POST['username'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $firstName       = trim($_POST['first_name'] ?? '');
        $lastName        = trim($_POST['last_name'] ?? '');
        $password        = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if (empty($username) || empty($email) || empty($firstName) || empty($lastName) || empty($password)) {
            throw new \Exception('All fields are required.');
        }
        if ($password !== $confirmPassword) {
            throw new \Exception('Passwords do not match.');
        }

        $passwordHelper = new PasswordHelper();
        $errors = $passwordHelper->validatePasswordStrength($password);
        if (!empty($errors)) {
            throw new \Exception(implode(' ', $errors));
        }

        $hashedPassword = $passwordHelper->hashPassword($password);
        $db = (new Database())->getConnection();

        $checkStmt = $db->prepare("SELECT id FROM tbl_users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        if ($checkStmt->fetch()) {
            throw new \Exception('Username or email already exists.');
        }

        $insertStmt = $db->prepare("
            INSERT INTO tbl_users
              (franchise_id, username, user_type, email, password_hash, first_name, last_name, status, force_password_change, created_at)
            VALUES
              (NULL, ?, 'super_admin', ?, ?, ?, ?, 'active', 0, NOW())
        ");

        if ($insertStmt->execute([$username, $email, $hashedPassword, $firstName, $lastName])) {
            $createSuccess  = true;
            $createdUsername = $username;
        } else {
            throw new \Exception('Failed to create user. Check database logs.');
        }

    } catch (\Exception $e) {
        $error = $e->getMessage();
        error_log('Super user creation error: ' . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Create Super Admin [DEV] — SaaS Seeder</title>
  <link href="./assets/tabler/css/tabler.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    @import url("https://rsms.me/inter/inter.css");
    html, body { height: 100%; margin: 0; }

    .auth-split { display: flex; min-height: 100vh; }

    /* Left panel — dark/branded */
    .auth-left {
      flex: 0 0 45%;
      background: linear-gradient(160deg, #1a1f36 0%, #0d1117 100%);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 3rem;
      position: relative;
      overflow: hidden;
    }
    .auth-left::before {
      content: '';
      position: absolute;
      width: 400px; height: 400px;
      background: rgba(6,111,209,.15);
      border-radius: 50%;
      top: -100px; right: -150px;
    }
    .auth-left > * { position: relative; z-index: 1; }
    .auth-left-logo {
      font-size: 1.5rem;
      font-weight: 800;
      color: #fff;
      text-decoration: none;
    }
    .auth-left-logo span { color: var(--tblr-primary, #066fd1); }
    .dev-badge {
      display: inline-block;
      padding: .25rem .6rem;
      background: rgba(255, 193, 7, .15);
      border: 1px solid rgba(255, 193, 7, .4);
      color: #ffc107;
      border-radius: 4px;
      font-size: .7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      margin-bottom: 1.5rem;
    }
    .auth-left-tagline { color: rgba(255,255,255,.85); }
    .auth-left-tagline h2 { font-size: 1.8rem; font-weight: 700; margin-bottom: .75rem; }
    .auth-left-tagline p { font-size: .9rem; opacity: .65; line-height: 1.6; }
    .security-notes { margin-top: 2rem; }
    .security-note {
      display: flex;
      align-items: flex-start;
      gap: .6rem;
      margin-bottom: .85rem;
      font-size: .8rem;
      color: rgba(255,255,255,.55);
    }
    .security-note svg { flex-shrink: 0; margin-top: 1px; color: #ffc107; }
    .auth-left-footer { font-size: .75rem; color: rgba(255,255,255,.3); }

    /* Right panel */
    .auth-right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      padding: 2rem 1.5rem;
      overflow-y: auto;
    }
    .auth-form-wrap { width: 100%; max-width: 440px; }
    .auth-form-wrap h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: .3rem; }
    .auth-form-wrap .subtitle { color: #6b7280; font-size: .9rem; margin-bottom: 1.5rem; }

    .form-control { border-radius: 8px; border: 1.5px solid #e5e7eb; }
    .form-control:focus { border-color: var(--tblr-primary); box-shadow: 0 0 0 3px rgba(6,111,209,.1); }
    .btn-create { height: 50px; font-size: 1rem; font-weight: 600; border-radius: 8px; }

    /* Password strength */
    .pw-strength-wrap { margin-top: .5rem; }
    .pw-strength-wrap .progress { height: 4px; border-radius: 2px; }
    .pw-strength-wrap small { font-size: .75rem; color: #6b7280; }

    @media (max-width: 991.98px) {
      .auth-left { display: none; }
      .auth-right { background: #f9fafb; }
    }
  </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($createSuccess): ?>
<div class="auth-split" style="justify-content:center;align-items:center;background:#f9fafb;">
  <div class="text-center">
    <div class="spinner-border text-success mb-3" style="width:3rem;height:3rem;" role="status"></div>
    <h3 class="fw-semibold">Super admin created!</h3>
    <p class="text-muted">Redirecting to sign in…</p>
  </div>
</div>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Account Created!',
    html: 'Super admin <strong><?php echo htmlspecialchars($createdUsername); ?></strong> is ready. You can now sign in.',
    timer: 3000,
    timerProgressBar: true,
    showConfirmButton: false,
  }).then(() => { window.location.href = './sign-in.php'; });
</script>

<?php else: ?>
<div class="auth-split">

  <!-- Left panel -->
  <div class="auth-left">
    <div>
      <a href="." class="auth-left-logo">SaaS<span>Seeder</span></a>
    </div>
    <div class="auth-left-tagline">
      <div class="dev-badge">Dev Tool</div>
      <h2>Create your<br>first super admin.</h2>
      <p>This tool creates super admin accounts with Argon2ID-hashed passwords. Remove or restrict this page before going to production.</p>
      <div class="security-notes">
        <div class="security-note">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Password hashed with Argon2ID + salt + pepper
        </div>
        <div class="security-note">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Same PasswordHelper used by AuthService at login
        </div>
        <div class="security-note">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          CSRF-protected form submission
        </div>
        <div class="security-note">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Remove this file before going to production
        </div>
      </div>
    </div>
    <div class="auth-left-footer">&copy; <?php echo date('Y'); ?> Chwezi Core Systems</div>
  </div>

  <!-- Right panel -->
  <div class="auth-right">
    <div class="auth-form-wrap">

      <div class="d-lg-none text-center mb-4">
        <span style="font-size:1.4rem;font-weight:800;color:var(--tblr-primary);">SaaS Seeder</span>
      </div>

      <h2>Create Super Admin</h2>
      <p class="subtitle">Fill in the details below to create the first super admin account.</p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="border-radius:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form action="./super-user-dev.php" method="POST" autocomplete="off" id="createForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label fw-medium">First Name <span class="text-danger">*</span></label>
            <input type="text" name="first_name" class="form-control" placeholder="John" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
          </div>
          <div class="col-6">
            <label class="form-label fw-medium">Last Name <span class="text-danger">*</span></label>
            <input type="text" name="last_name" class="form-control" placeholder="Doe" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-medium">Username <span class="text-danger">*</span></label>
          <input type="text" name="username" class="form-control" placeholder="admin" autocomplete="off" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
          <small class="text-muted">Used to sign in</small>
        </div>

        <div class="mb-3">
          <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" placeholder="admin@example.com" autocomplete="off" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="mb-3">
          <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
          <div class="input-group input-group-flat">
            <input type="password" name="password" id="password" class="form-control" placeholder="Min. 8 characters" autocomplete="off" required>
            <span class="input-group-text p-0 border-0 bg-transparent">
              <button type="button" class="btn btn-link text-muted px-3" id="togglePassword">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
              </button>
            </span>
          </div>
          <div class="pw-strength-wrap mt-2" id="pwStrengthWrap" style="display:none;">
            <div class="progress mb-1"><div id="pwStrengthBar" class="progress-bar" style="width:0%"></div></div>
            <small id="pwStrengthText"></small>
          </div>
          <small class="text-muted d-block mt-1">Uppercase, lowercase, number, and special character required</small>
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium">Confirm Password <span class="text-danger">*</span></label>
          <div class="input-group input-group-flat">
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat password" autocomplete="off" required>
            <span class="input-group-text p-0 border-0 bg-transparent">
              <button type="button" class="btn btn-link text-muted px-3" id="toggleConfirm">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
              </button>
            </span>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-create" id="submitBtn">
          Create Super Admin Account
        </button>
      </form>

      <div class="text-center mt-3">
        <a href="./sign-in.php" class="text-muted" style="font-size:.875rem;">Already have an account? Sign in</a>
      </div>

    </div>
  </div>

</div>
<?php endif; ?>

<script src="./assets/tabler/js/tabler.min.js"></script>
<script>
  // Password visibility toggles
  function makeToggle(btnId, fieldId) {
    const btn   = document.getElementById(btnId);
    const field = document.getElementById(fieldId);
    if (!btn || !field) return;
    btn.addEventListener('click', () => {
      field.type = field.type === 'password' ? 'text' : 'password';
    });
  }
  makeToggle('togglePassword', 'password');
  makeToggle('toggleConfirm', 'confirm_password');

  // Password strength meter
  const pwField       = document.getElementById('password');
  const pwStrengthBar = document.getElementById('pwStrengthBar');
  const pwStrengthTxt = document.getElementById('pwStrengthText');
  const pwStrengthWrap = document.getElementById('pwStrengthWrap');

  if (pwField) {
    pwField.addEventListener('input', function () {
      const v = this.value;
      if (!v) { pwStrengthWrap.style.display = 'none'; return; }
      pwStrengthWrap.style.display = 'block';
      let score = 0;
      if (v.length >= 8)                            score++;
      if (/[A-Z]/.test(v))                          score++;
      if (/[a-z]/.test(v))                          score++;
      if (/[0-9]/.test(v))                          score++;
      if (/[!@#$%^&*()\-_=+{};:,<.>]/.test(v))     score++;
      const levels = [
        { pct: 20,  cls: 'bg-danger',  label: 'Very weak'   },
        { pct: 40,  cls: 'bg-warning', label: 'Weak'        },
        { pct: 60,  cls: 'bg-info',    label: 'Fair'        },
        { pct: 80,  cls: 'bg-primary', label: 'Strong'      },
        { pct: 100, cls: 'bg-success', label: 'Very strong' },
      ];
      const lvl = levels[score - 1] || levels[0];
      pwStrengthBar.style.width = lvl.pct + '%';
      pwStrengthBar.className   = 'progress-bar ' + lvl.cls;
      pwStrengthTxt.textContent  = lvl.label;
    });
  }

  // Confirm match
  const cfmField = document.getElementById('confirm_password');
  if (cfmField && pwField) {
    cfmField.addEventListener('input', function () {
      this.setCustomValidity(this.value && this.value !== pwField.value ? 'Passwords do not match' : '');
    });
    pwField.addEventListener('input', function () {
      if (cfmField.value) {
        cfmField.setCustomValidity(cfmField.value !== this.value ? 'Passwords do not match' : '');
      }
    });
  }

  // Loading state on submit
  const createForm = document.getElementById('createForm');
  const submitBtn  = document.getElementById('submitBtn');
  if (createForm && submitBtn) {
    createForm.addEventListener('submit', () => {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Creating account…';
    });
  }
</script>
</body>
</html>
```

**Step 2: Verify**

1. Visit `http://localhost/super-user-dev.php`
2. Left panel shows dark branded panel with security notes
3. Fill in all fields, submit → SweetAlert success → redirect to `sign-in.php`
4. Log in with the newly created user — should work immediately
5. Test wrong password during login → `INVALID_PASSWORD` error (confirms same PasswordHelper used)
6. Confirm in DB: `password_hash` starts with a 32-char hex salt followed by `$argon2id$`

**Step 3: Commit**

```bash
git add public/super-user-dev.php
git commit -m "feat: redesign super-user-dev.php with split-panel dark dev tool UI"
```

---

## Task 8: Fix `forgot-password.php` asset paths

The file uses wrong `./dist/` paths — nothing loads. Fix to match the rest of the app.

**Files:**
- Modify: `public/forgot-password.php`

**Step 1: Fix asset paths and apply consistent styling**

Replace the entire file:

```php
<?php
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Helpers\UiHelper;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

if (isLoggedIn()) {
    header('Location: ./index.php');
    exit();
}

$loginBackground = UiHelper::getRandomLoginBackground();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Forgot Password — SaaS Seeder</title>
  <link href="./assets/tabler/css/tabler.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    @import url("https://rsms.me/inter/inter.css");
    html, body { height: 100%; margin: 0; }
    .auth-split { display: flex; min-height: 100vh; }
    .auth-left {
      flex: 0 0 55%;
      background-image: url('<?php echo htmlspecialchars($loginBackground); ?>');
      background-size: cover; background-position: center; position: relative;
      display: flex; flex-direction: column; justify-content: space-between; padding: 3rem;
    }
    .auth-left::after {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(160deg, rgba(6,111,209,.55) 0%, rgba(0,0,0,.72) 100%);
    }
    .auth-left > * { position: relative; z-index: 1; }
    .auth-left-logo { font-size: 1.6rem; font-weight: 800; color: #fff; text-decoration: none; }
    .auth-left-footer { font-size: .8rem; color: rgba(255,255,255,.45); }
    .auth-right { flex: 1; display: flex; align-items: center; justify-content: center; background: #fff; padding: 2rem 1.5rem; }
    .auth-form-wrap { width: 100%; max-width: 400px; }
    .form-control { border-radius: 8px; border: 1.5px solid #e5e7eb; }
    .form-control:focus { border-color: var(--tblr-primary); box-shadow: 0 0 0 3px rgba(6,111,209,.1); }
    .btn-submit { height: 50px; font-size: 1rem; font-weight: 600; border-radius: 8px; }
    @media (max-width: 991.98px) { .auth-left { display: none; } .auth-right { background: #f9fafb; } }
  </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="auth-split">
  <div class="auth-left">
    <a href="." class="auth-left-logo">SaaS Seeder</a>
    <div style="color:rgba(255,255,255,.85);">
      <h2 style="font-size:2rem;font-weight:700;margin-bottom:.75rem;">Forgot your password?</h2>
      <p style="opacity:.8;max-width:360px;">Enter your email or username and we'll send you reset instructions.</p>
    </div>
    <div class="auth-left-footer">&copy; <?php echo date('Y'); ?> Chwezi Core Systems</div>
  </div>

  <div class="auth-right">
    <div class="auth-form-wrap">
      <div class="d-lg-none text-center mb-4">
        <span style="font-size:1.4rem;font-weight:800;color:var(--tblr-primary);">SaaS Seeder</span>
      </div>

      <h2 style="font-size:1.6rem;font-weight:700;margin-bottom:.3rem;">Reset Password</h2>
      <p style="color:#6b7280;font-size:.95rem;margin-bottom:1.75rem;">
        Enter your email or username below and we'll send instructions to reset your password.
      </p>

      <div class="alert alert-warning d-flex gap-2 align-items-start mb-4" style="border-radius:8px;font-size:.875rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 mt-1"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <div>Email-based password reset is not yet configured for this installation. Please contact your system administrator to reset your password.</div>
      </div>

      <form id="forgotPasswordForm" autocomplete="off">
        <div class="mb-4">
          <label class="form-label fw-medium">Email or Username</label>
          <input type="text" class="form-control" id="identifier" placeholder="Enter your email or username" style="height:50px;">
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-submit">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Send Reset Instructions
        </button>
      </form>

      <div class="text-center mt-4">
        <a href="./sign-in.php" style="font-size:.875rem;" class="text-muted">
          &larr; Back to sign in
        </a>
      </div>
    </div>
  </div>
</div>

<script src="./assets/tabler/js/tabler.min.js"></script>
<script>
  document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    Swal.fire({
      icon: 'info',
      title: 'Not Yet Configured',
      text: 'Email-based password reset has not been set up for this installation. Please contact your administrator.',
      confirmButtonText: 'OK'
    });
  });
</script>
</body>
</html>
```

**Step 2: Verify**

1. Visit `http://localhost/forgot-password.php`
2. Page loads with split-panel — no missing CSS/JS
3. Submit the form → SweetAlert info message about not being configured
4. "Back to sign in" link works

**Step 3: Commit**

```bash
git add public/forgot-password.php
git commit -m "fix: forgot-password.php asset paths and apply split-panel redesign"
```

---

## Task 9: End-to-end verification

Manual testing run to confirm everything works together.

**Step 1: Full login test**

1. Visit `http://localhost/super-user-dev.php`
2. Create a super admin (e.g., username: `testadmin`, strong password)
3. Visit `http://localhost/sign-in.php`
4. Log in with those credentials → SweetAlert → redirect to `index.php`
5. Confirm you reach the dashboard

**Step 2: Wrong password test**

1. Log out via the topbar
2. Visit `sign-in.php`, enter correct username, wrong password
3. Should show `"Invalid password provided."` error (not a crash)

**Step 3: Session prefix verification**

1. Log in
2. On `dashboard.php`, temporarily add:
   ```php
   <?php var_dump(array_keys($_SESSION)); die(); ?>
   ```
3. Confirm all keys start with `saas_app_` (e.g., `saas_app_user_id`, `saas_app_auth_token`)
4. Remove the debug line

**Step 4: Logout token invalidation**

1. Log in, note the value of `saas_app_auth_token` from the session dump above
2. Check `tbl_user_sessions` in DB — find the matching token record, note its status
3. Log out
4. Re-check `tbl_user_sessions` — the token should now be expired/marked inactive

**Step 5: CSRF test**

1. Log out
2. Open DevTools, submit the login form with `csrf_token` field emptied manually
3. Should see `"Invalid or missing security token."` error

**Step 6: change-password.php**

1. In DB: `UPDATE tbl_users SET force_password_change = 1 WHERE username = 'testadmin';`
2. Log in as `testadmin` → should redirect to `change-password.php`
3. Enter current + new password → success → `index.php`
4. Verify `force_password_change = 0` in DB

**Step 7: Final commit**

```bash
git add -A
git commit -m "test: manual verification complete — auth single source of truth enforced"
```

---

## Summary of Changes

| File | Change |
|------|--------|
| `src/Auth/DTO/AuthResult.php` | Fix `isSuccessful()` to check `'SUCCESS'` |
| `src/Auth/Helpers/CSRFHelper.php` | Use `setSession`/`getSession`/`hasSession` instead of raw `$_SESSION` |
| `public/logout.php` | Use `getSession('auth_token')` instead of raw `$_SESSION['auth_token']` |
| `public/sign-in.php` | Remove duplicate session writes; full split-panel UI redesign |
| `public/super-user-dev.php` | Full split-panel dark dev tool UI redesign |
| `public/change-password.php` | New file — forced and voluntary password change flow |
| `public/forgot-password.php` | Fix asset paths; apply split-panel redesign; stub reset form |
