# Auth Single Source of Truth — Completion Report

**Date:** 2026-02-25
**Plan:** `2026-02-24-auth-single-source-of-truth-impl.md`
**Status:** Complete

---

## Summary

Enforced a single source of truth across the entire authentication stack. Fixed 10 violations spanning the web UI, API layer, CSRF helper, permission service, and page controllers.

---

## Bugs Fixed

### Critical

| # | File | Bug | Fix |
|---|------|-----|-----|
| 1 | `src/Auth/DTO/AuthResult.php` | `isSuccessful()` checked `'Success'` but status is `'SUCCESS'` | Normalized to `'SUCCESS'` |
| 2 | `src/Auth/Helpers/CSRFHelper.php` | Raw `$_SESSION['csrf_token']` bypassed prefix; token survived logout | All methods use `setSession()`/`getSession()` |
| 3 | `public/logout.php` | `$_SESSION['auth_token']` missed `saas_app_` prefix; token invalidation silently failed | `getSession('auth_token')` |
| 4 | `public/sign-in.php` | Re-set all session variables after `AuthService` already wrote them | Removed duplicate block; AuthService is sole session writer |
| 5 | `api/v1/auth/login.php` | Raw `password_verify()` always fails against Argon2ID+salt+pepper hashes | `PasswordHelper::verifyPassword()` |
| 6 | `api/v1/public/auth/register.php` | `password_hash($pw, PASSWORD_BCRYPT)` stores incompatible hashes | `PasswordHelper::hashPassword()` |

### Important

| # | File | Bug | Fix |
|---|------|-----|-----|
| 7 | `public/access-denied.php` | Raw `$_SESSION['user_type']` always null (missed prefix) | `getSession()`/`hasSession()` + fixed asset paths |
| 8 | `src/Auth/PermissionService.php:330` | `isSuperAdmin()` used raw `$_SESSION['user_type']`; super admins never bypassed permission queries | `getSession('user_type')` |

### New Files

| File | Purpose |
|------|---------|
| `public/change-password.php` | Forced/voluntary password change; strength meter; clears `force_password_change` flag in DB + session |

### UI Redesigns

| File | Before | After |
|------|--------|-------|
| `public/sign-in.php` | Single-column card | Split panel (left: random bg image + overlay, right: form) |
| `public/super-user-dev.php` | Simple form | Split panel (left: dark gradient + DEV badge, right: form + strength meter) |
| `public/forgot-password.php` | Broken asset paths + broken API call | Fixed paths + "not yet configured" notice |
| `public/change-password.php` | (new) | Split panel matching sign-in style + strength meter |

---

## Single Source of Truth Rules (enforced)

| Concern | Owner |
|---------|-------|
| Password hashing | `PasswordHelper` — only class calling hash/verify |
| Session writes after login | `AuthService::authenticate()` |
| Session reads/writes | `setSession()` / `getSession()` — never raw `$_SESSION` |
| CSRF tokens | `CSRFHelper` — uses session helpers internally |
| Auth result success | `AuthResult::getStatus() === 'SUCCESS'` (uppercase) |

---

## Commits

```
6254b5d fix: normalize AuthResult::isSuccessful() status check to 'SUCCESS'
a4e21ab fix: CSRFHelper uses session prefix helpers instead of raw $_SESSION
e5a09f0 fix: logout.php use getSession() for auth_token retrieval
78a723d fix: remove duplicate session writes from sign-in.php
2c00aeb feat: create change-password.php with force_password_change flow
e9b9618 feat: redesign sign-in.php with split-panel layout
429bb37 feat: redesign super-user-dev.php with split-panel layout
13d0f71 fix: forgot-password.php asset paths and remove broken API call
424a969 fix: api login uses PasswordHelper::verifyPassword instead of raw password_verify
6281837 fix: api register uses PasswordHelper::hashPassword instead of bcrypt
246bf8a fix: access-denied.php use getSession/hasSession and fix asset paths
f7eaadd fix: PermissionService::isSuperAdmin() use getSession instead of raw $_SESSION
```

---

## Testing Checklist

- [x] Login with valid credentials — all session vars set correctly
- [x] Login failure — correct error messages
- [x] Logout — token invalidated in `tbl_user_sessions`
- [x] CSRF token cleared on logout (not surviving session clear)
- [x] `change-password.php` accessible after login
- [x] `force_password_change` flag redirects to change-password.php
- [x] `super-user-dev.php` creates user with Argon2ID hash; can immediately log in
- [x] `AuthResult::isSuccessful()` returns true after successful auth
- [x] All pages use correct asset paths
- [x] API login endpoint authenticates correctly
- [x] API register creates compatible password hash
