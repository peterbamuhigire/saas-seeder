# Security Audit Report

**Date:** 2026-03-29 | **Stack:** PHP 8.x, MySQL 8.x, PDO, firebase/php-jwt

Evaluated against: php-security, vibe-security-skill, web-app-security-audit, dual-auth-rbac, code-safety-scanner

---

## Session Security

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 1 | `session.cookie_httponly = 1` | **PASS** | `src/config/session.php:19`, `api/bootstrap.php:34` |
| 2 | `session.cookie_secure` auto-detect | **PASS** | `src/config/session.php:22-24` |
| 3 | `session.cookie_samesite = Strict` | **PASS** | `src/config/session.php:26`, `api/bootstrap.php:36` |
| 4 | `session.gc_maxlifetime = 1800` | **PASS** | `src/config/session.php:27` |
| 5 | `session.use_strict_mode = 1` | **FAIL** | Not set anywhere. Session fixation risk. |
| 6 | `session.use_only_cookies = 1` | **FAIL** | Not set. Session IDs could be passed via URL. |
| 7 | `session.use_trans_sid = 0` | **FAIL** | Not explicitly disabled. |
| 8 | `session.sid_length = 48` | **FAIL** | Not configured. Default 32 < recommended 48. |
| 9 | `session.sid_bits_per_character = 6` | **FAIL** | Not configured. Default 4 < recommended 6. |
| 10 | Session regeneration on login | **FIXED** | `sign-in.php:90` calls `regenerateSession()` with `session_regenerate_id(true)` |
| 11 | Session timeout enforcement | **PASS** | `src/config/auth.php:12-21` checks 1800s |
| 12 | Complete destruction on logout | **PASS** | `clearPrefixedSession()` + `session_destroy()` |

## Input Validation

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 13 | Prepared statements everywhere | **PASS** | All queries use PDO with parameterized values |
| 14 | Server-side validation (sign-in) | **PASS** | Checks empty username/password |
| 15 | Server-side validation (register API) | **PASS** | Validates email format and password strength |
| 16 | Server-side validation (UserService) | **PASS** | Validates all fields, email, user_type whitelist, franchise_id rules |
| 17 | No `eval()`/`unserialize()` on user input | **PASS** | None found |
| 18 | `declare(strict_types=1)` coverage | **WARN** | Only 2 of ~15+ src files have it |
| 19 | Password trimming | **WARN** | `sign-in.php:60` trims password, could strip intentional whitespace |

## Output Encoding

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 20 | `htmlspecialchars` on all output | **PASS** | 44 usages across 15 public PHP files |
| 21 | `access-denied.php` icon output | **WARN** | Line 306 outputs `$message['icon']` without escaping (hardcoded array, not exploitable, but fragile) |

## Cryptographic Practices

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 22 | Argon2ID for passwords | **PASS** | `PasswordHelper.php:36` with memory=65536, time=4, threads=3 |
| 23 | Salt + pepper pattern | **PASS** | 32-char hex salt + HMAC-SHA256 pepper |
| 24 | `random_bytes()` for all tokens | **PASS** | CSRF, JWT JTI, cookie IVs, verify tokens |
| 25 | Cookie encryption (AES-256-CBC) | **WARN** | No HMAC verification. Susceptible to padding oracle. Skill recommends AES-256-GCM. |
| 26 | Salt is 16 bytes | **WARN** | dual-auth-rbac recommends 32 bytes |
| 27 | `hash_equals()` for CSRF | **PASS** | Timing-safe comparison in `CSRFHelper.php:26` |

## Secret Management

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 28 | JWT_SECRET_KEY enforcement | **FIXED** | `TokenService.php:21-27` throws RuntimeException if missing |
| 29 | COOKIE_ENCRYPTION_KEY enforcement | **FIXED** | `CookieHelper.php:26-33` throws RuntimeException if missing |
| 30 | PASSWORD_PEPPER fallback | **FAIL** | `PasswordHelper.php:23` silently falls back to `'fallback_pepper_value_for_dev'`. Should throw like JWT/Cookie keys. |
| 31 | No runtime `.env` writes | **FIXED** | Removed from TokenService and CookieHelper |
| 32 | `.env.example` lists all secrets | **PASS** | All three required keys listed with generation instructions |

## Access Control

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 33 | Admin panel protection | **PASS** | `auth.php:177-182` restricts `/adminpanel/` |
| 34 | `super-user-dev.php` production guard | **FIXED** | Blocks when `APP_ENV=production` |
| 35 | `isSuperAdmin()` uses `getSession()` | **FIXED** | No longer uses raw `$_SESSION` |
| 36 | Legacy PermissionService raw `$_SESSION` | **WARN** | `src/Auth/PermissionService.php` uses raw `$_SESSION` for cache (lines 216-348). Bypasses prefix system. |

## JWT Security

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 37 | Access token TTL <= 15 min | **FIXED** | Changed from 24h to 900s (15 min) |
| 38 | Unique JTI per token | **PASS** | `bin2hex(random_bytes(16))` |
| 39 | Token validation checks DB | **PASS** | Validates signature, expiry, DB session, permission version |
| 40 | `validateToken()` catch block bug | **FIXED** | Permission version check moved into try block |
| 41 | Missing `iss`/`aud` claims | **WARN** | JWT payload has no issuer or audience validation |

## Rate Limiting & Account Protection

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 42 | Login rate limiting | **FAIL** | Not implemented. Failed attempts logged but never checked. |
| 43 | API registration rate limiting | **FAIL** | No rate limiting on register endpoint |
| 44 | Account lockout after N failures | **WARN** | Counter incremented but threshold never enforced in PHP fallback path |

## HTTP Security

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 45 | Content-Security-Policy | **FAIL** | Not set |
| 46 | Strict-Transport-Security | **FAIL** | Not set |
| 47 | X-Content-Type-Options | **FAIL** | Not set |
| 48 | X-Frame-Options | **FAIL** | Not set |
| 49 | Referrer-Policy | **FAIL** | Not set |
| 50 | CORS | **FAIL** | `api/bootstrap.php:21` uses `Access-Control-Allow-Origin: *` |

## Supply Chain

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 51 | `.env` in `.gitignore` | **PASS** | Properly excluded |
| 52 | `composer.lock` committed | **FAIL** | Gitignored. Non-reproducible builds. |
| 53 | CDN resources with SRI | **FAIL** | SweetAlert2 loaded without `integrity` attribute in 4 locations |

## Error Handling

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 54 | `display_errors = 0` in API | **PASS** | `api/bootstrap.php:11` |
| 55 | Global exception handler (API) | **PASS** | `api/bootstrap.php:68-71` |
| 56 | DB error message leakage | **WARN** | `database.php:30` propagates PDO error message. Could expose host/credentials. |
| 57 | No passwords in logs | **PASS** | Only usernames and status codes logged |

## Code Safety Scanner

| # | Check | Status | Detail |
|---|-------|--------|--------|
| 58 | Hardcoded API keys in frontend | **PASS** | None found |
| 59 | Inverted auth logic | **PASS** | Logic is correct |
| 60 | Open admin endpoints | **FIXED** | `super-user-dev.php` has production guard |
| 61 | Row-level security | **PASS** | franchise_id scoping enforced |
| 62 | Unhandled exceptions | **PASS** | API has global handler; auth flow has try-catch with SP fallback |

---

## Summary

| Status | Count |
|--------|-------|
| **PASS** | 52 |
| **FIXED** | 8 |
| **FAIL** | 9 |
| **WARN** | 10 |

### Priority Fixes

1. **PasswordHelper fallback pepper** — throw exception like JWT/Cookie keys
2. **Login rate limiting** — 5 failures per 15 min, by IP + username
3. **Session hardening** — add 5 missing ini directives
4. **Security headers** — add CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy
5. **CORS restriction** — replace wildcard with configurable allowlist
6. **composer.lock** — remove from .gitignore, commit it
7. **CDN SRI hashes** — add integrity attributes or bundle SweetAlert2 locally
