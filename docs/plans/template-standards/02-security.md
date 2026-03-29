# Phase 2: Security Hardening

**Clears:** 9 FAILs, 5 WARNs
**Depends on:** Phase 1 (audit log table must exist)
**Files:** `src/config/session.php`, `src/Auth/Helpers/PasswordHelper.php`, `src/Auth/Services/AuthService.php`, `src/Auth/Helpers/CookieHelper.php`, `api/bootstrap.php`, `public/includes/security-headers.php` (new), `public/sign-in.php`

---

## Findings Addressed

| ID | Type | Issue |
|----|------|-------|
| S-5 | FAIL | `session.use_strict_mode` not set |
| S-6 | FAIL | `session.use_only_cookies` not set |
| S-7 | FAIL | `session.use_trans_sid` not set |
| S-8 | FAIL | `session.sid_length` not configured |
| S-9 | FAIL | `session.sid_bits_per_character` not configured |
| S-30 | FAIL | PASSWORD_PEPPER fallback to hardcoded value |
| S-42 | FAIL | No login rate limiting |
| S-43 | FAIL | No API registration rate limiting |
| S-45-49 | FAIL | No security headers (5 headers) |
| S-50 | FAIL | CORS wildcard |
| S-19 | WARN | Password trimming |
| S-25 | WARN | AES-256-CBC without HMAC |
| S-26 | WARN | Salt 16 bytes vs 32 |
| S-41 | WARN | Missing JWT iss/aud claims |
| S-56 | WARN | DB error message leakage |

---

## Task 1: Session hardening directives

**FILE:** `src/config/session.php`

**TASK:** Add the 5 missing session ini directives before `session_start()`.

**CODE:** Add after the existing `ini_set` block (around line 27):
```php
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
ini_set('session.sid_length', '48');
ini_set('session.sid_bits_per_character', '6');
```

**ALSO:** Add the same 5 directives to `api/bootstrap.php` in its session configuration block.

**VALIDATION:**
- [ ] `php -l src/config/session.php`
- [ ] `php -l api/bootstrap.php`

---

## Task 2: PasswordHelper — throw on missing pepper

**FILE:** `src/Auth/Helpers/PasswordHelper.php`

**TASK:** Replace the fallback pepper with a RuntimeException, matching the pattern used by TokenService and CookieHelper.

**CODE:** Change line 23 from:
```php
$this->pepper = $pepper ?: 'fallback_pepper_value_for_dev';
```
To:
```php
if (!$pepper) {
    throw new \RuntimeException(
        'PASSWORD_PEPPER is not set in .env. '
        . 'Generate one with: php -r "echo bin2hex(random_bytes(32));"'
    );
}
$this->pepper = $pepper;
```

**ALSO:** Increase salt from 16 to 32 bytes (line 34):
```php
$salt = bin2hex(random_bytes(32)); // 64-char hex salt
```
Update `verifyPassword` to extract 64-char salt:
```php
$salt = substr($storedHash, 0, 64);
$hash = substr($storedHash, 64);
```

**CONSTRAINTS:**
- This is a BREAKING CHANGE for existing password hashes. Add a backward-compatible check in `verifyPassword` that tries 32-char salt first, then 64-char.
- Document the migration path in CLAUDE.md

**VALIDATION:**
- [ ] `php -l src/Auth/Helpers/PasswordHelper.php`
- [ ] New hashes use 64-char salt prefix
- [ ] Old 32-char salt hashes still verify correctly

---

## Task 3: Rate limiting on login

**FILE:** `src/Auth/Services/AuthService.php`

**TASK:** Check `failed_login_attempts` before allowing authentication. Lock account after 5 failures within 15 minutes.

**THINK STEP-BY-STEP:**
1. Before password verification, query `tbl_users.failed_login_attempts` and `tbl_users.locked_until`
2. If `locked_until > NOW()`, return `ACCOUNT_LOCKED` status
3. If `failed_login_attempts >= 5`, set `locked_until = NOW() + 15 minutes` and return `ACCOUNT_LOCKED`
4. On successful login, reset both counters
5. Add `locked_until DATETIME DEFAULT NULL` column to `tbl_users` (in Phase 1 migration)

**ALSO:** Add IP-based rate limiting to `api/bootstrap.php` using a simple file-based or session-based counter (5 requests per minute per IP for auth endpoints). This is a template — production would use Redis/Memcached.

**VALIDATION:**
- [ ] After 5 failed logins, 6th attempt returns `ACCOUNT_LOCKED`
- [ ] After 15 minutes, login works again
- [ ] Successful login resets the counter

---

## Task 4: Security headers

**FILE:** `public/includes/security-headers.php` (new)

**TASK:** Create a shared include that sets all security headers. Include it from `head.php`.

**CODE:**
```php
<?php
// Security headers — included early in every page
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

$appEnv = $_ENV['APP_ENV'] ?? 'development';
if ($appEnv === 'production') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// CSP — restrictive by default, projects override as needed
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://rsms.me; img-src 'self' data:; font-src 'self' https://rsms.me; connect-src 'self'");
```

**ALSO:** Add equivalent headers to `api/bootstrap.php` (simpler — no CSP needed, just the 4 core headers).

**VALIDATION:**
- [ ] `curl -I http://localhost:8000/sign-in.php` shows all 5 headers
- [ ] `curl -I http://localhost:8000/api/v1/auth/login.php` shows 4 headers

---

## Task 5: CORS configuration

**FILE:** `api/bootstrap.php`

**TASK:** Replace `Access-Control-Allow-Origin: *` with configurable origins from `.env`.

**CODE:**
```php
$allowedOrigins = array_filter(array_map('trim',
    explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:8000')
));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} elseif ($_ENV['APP_ENV'] ?? 'development' === 'development') {
    header('Access-Control-Allow-Origin: *'); // Dev only
}
```

**ALSO:** Add `CORS_ALLOWED_ORIGINS=http://localhost:8000` to `.env.example`.

**VALIDATION:**
- [ ] `php -l api/bootstrap.php`
- [ ] In production, only listed origins get CORS headers

---

## Task 6: Stop trimming passwords

**FILE:** `public/sign-in.php`

**TASK:** Remove `trim()` from password extraction (line 60). Passwords should be used exactly as entered.

**CODE:** Change `$password = trim($_POST['password'] ?? '');` to `$password = $_POST['password'] ?? '';`

**ALSO:** Same change in `public/super-user-dev.php` if still trimming.

**VALIDATION:**
- [ ] Passwords with leading/trailing spaces are accepted

---

## Task 7: JWT issuer/audience claims

**FILE:** `src/Auth/Services/TokenService.php`

**TASK:** Add `iss` and `aud` claims to JWT payload and validate them on decode.

**CODE:** In `generateToken()`, add to payload:
```php
'iss' => $_ENV['APP_URL'] ?? 'saas-seeder',
'aud' => $_ENV['APP_URL'] ?? 'saas-seeder',
```

In `validateToken()`, after decode, verify:
```php
if (($decoded->iss ?? '') !== ($_ENV['APP_URL'] ?? 'saas-seeder')) return false;
if (($decoded->aud ?? '') !== ($_ENV['APP_URL'] ?? 'saas-seeder')) return false;
```

**ALSO:** Add `APP_URL=http://localhost:8000` to `.env.example`.

**VALIDATION:**
- [ ] `php -l src/Auth/Services/TokenService.php`
- [ ] Tokens from other issuers are rejected

---

## Task 8: Cookie encryption upgrade

**FILE:** `src/Auth/Helpers/CookieHelper.php`

**TASK:** Switch from AES-256-CBC to AES-256-GCM for authenticated encryption.

**CODE:**
```php
private function encryptValue(string $value): string
{
    $iv = random_bytes(12); // GCM uses 12-byte nonce
    $encrypted = openssl_encrypt($value, 'AES-256-GCM', $this->encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
    return base64_encode($iv . $tag . $encrypted);
}

private function decryptValue(string $value): ?string
{
    try {
        $decoded = base64_decode($value);
        $iv = substr($decoded, 0, 12);
        $tag = substr($decoded, 12, 16);
        $encrypted = substr($decoded, 28);
        $result = openssl_decrypt($encrypted, 'AES-256-GCM', $this->encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
        return $result !== false ? $result : null;
    } catch (\Exception $e) {
        return null;
    }
}
```

**CONSTRAINTS:**
- Old CBC-encrypted cookies will fail to decrypt. This is acceptable — users will be logged out and need to re-authenticate. Cookie TTL is short.

**VALIDATION:**
- [ ] `php -l src/Auth/Helpers/CookieHelper.php`
- [ ] Encrypt → decrypt round-trip works
- [ ] Tampered ciphertext returns null (not garbage)

---

## Task 9: Database error message sanitization

**FILE:** `src/Config/Database.php`

**TASK:** Catch PDOException and throw a generic message, logging the full error.

**CODE:**
```php
} catch(\PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    throw new \RuntimeException('Database connection failed. Check server configuration.');
}
```

**VALIDATION:**
- [ ] `php -l src/Config/Database.php`
- [ ] Connection failure shows generic message, not host/credentials

---

## Status

| Task | Status |
|------|--------|
| 1: Session hardening | not-started |
| 2: PasswordHelper pepper + salt | not-started |
| 3: Rate limiting | not-started |
| 4: Security headers | not-started |
| 5: CORS configuration | not-started |
| 6: Stop trimming passwords | not-started |
| 7: JWT iss/aud claims | not-started |
| 8: Cookie encryption upgrade | not-started |
| 9: DB error sanitization | not-started |
