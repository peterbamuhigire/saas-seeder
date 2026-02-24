# Auth Single Source of Truth + UI Redesign

**Date:** 2026-02-24
**Approach:** A — Fix bugs + clean refactor
**Scope:** Authentication unification, bug fixes, sign-in.php + super-user-dev.php UI redesign

---

## Problem Summary

Six bugs were found that break the "single source of truth" principle for authentication:

1. `logout.php` uses raw `$_SESSION['auth_token']` — misses the `saas_app_` prefix, so token invalidation silently fails every time.
2. `AuthResult::isSuccessful()` checks `'Success'` but `AuthService` sets status `'SUCCESS'` — the helper method is permanently broken.
3. `CSRFHelper` uses raw `$_SESSION['csrf_token']` — bypasses the prefix system; CSRF token survives `clearPrefixedSession()`.
4. `sign-in.php` re-sets all session variables AFTER `AuthService::authenticate()` already set them — duplicate writes, two sources of truth for session state.
5. `change-password.php` is missing but referenced by the login flow (`force_password_change` flag).
6. `forgot-password.php` has wrong asset paths and calls an API endpoint that does not exist.

---

## Single Source of Truth Rules (post-fix)

| Concern | Owner | Rule |
|---------|-------|------|
| Password hashing | `PasswordHelper` | Only class that calls `password_hash` / `password_verify` |
| Session writes after login | `AuthService::authenticate()` | sign-in.php only calls authenticate(), never writes sessions itself |
| Session reads/writes | `setSession()` / `getSession()` | Never use `$_SESSION[...]` directly |
| CSRF tokens | `CSRFHelper` | Must use `setSession()` / `getSession()` internally |
| Auth result success check | `AuthResult::getStatus() === 'SUCCESS'` | Normalize to all-caps `'SUCCESS'` everywhere |

---

## Changes Required

### 1. `src/Auth/Helpers/CSRFHelper.php`
- Replace all `$_SESSION['csrf_token']` with `setSession('csrf_token', ...)` / `getSession('csrf_token')`
- Requires session.php to be loaded (it already is via auth.php)

### 2. `src/Auth/DTO/AuthResult.php`
- Fix `isSuccessful()`: change check from `'Success'` to `'SUCCESS'`

### 3. `public/logout.php`
- Replace `$_SESSION['auth_token']` with `getSession('auth_token')`

### 4. `public/sign-in.php`
- Remove the duplicate `setSession()` block after `$authService->authenticate()`
- AuthService already set all sessions; sign-in.php just reads from $result to build the welcome message
- Redesign UI: split-panel layout (left = random background image, right = form)
- Add animated entrance, floating labels, password strength indicator

### 5. `public/super-user-dev.php`
- Redesign UI: split-panel layout matching sign-in.php style
- Left panel: branded dark panel with security warning
- Right panel: create super admin form
- Add real-time password strength meter

### 6. `public/change-password.php` (new file)
- Simple authenticated page requiring `requireAuth()`
- Checks `force_password_change` session flag
- Uses `PasswordHelper` for hashing (single source of truth)
- Validates: current password, new password strength, confirm match
- On success: updates DB, clears `force_password_change` flag, redirects to dashboard

### 7. `public/forgot-password.php`
- Fix asset paths: `./dist/css/tabler.css` → `./assets/tabler/css/tabler.min.css`
- Fix JS paths similarly
- Show a "password reset via email is not yet configured" message for now
- Apply split-panel redesign to match sign-in.php

---

## UI Design — Split Panel Layout

Both `sign-in.php` and `super-user-dev.php` use:

```
┌─────────────────────┬─────────────────────┐
│                     │                     │
│  LEFT PANEL         │  RIGHT PANEL        │
│  Full-height        │  White/light bg     │
│  background image   │                     │
│  (random from       │  Logo/Brand name    │
│   login-bg/)        │                     │
│                     │  Page title         │
│  Brand overlay      │                     │
│  with tagline       │  Form fields        │
│  at bottom          │  (floating labels)  │
│                     │                     │
│                     │  Submit button      │
│                     │                     │
│                     │  Footer links       │
│                     │                     │
└─────────────────────┴─────────────────────┘
```

- Uses Tabler's `row g-0` split layout
- Left panel: `col-lg-6 d-none d-lg-block` — hidden on mobile
- Right panel: `col-12 col-lg-6` — full width on mobile, half on desktop
- Left panel background: random `login-bg/bgroundN.jpg` via `UiHelper::getRandomLoginBackground()`
- Gradient overlay on left panel for readability
- Right panel: centered vertically, clean white card feel
- Password fields: show/hide toggle
- CSRF protection retained on all forms
- SweetAlert2 for success/error feedback

---

## Testing Plan

After implementation, verify:
- [ ] Login with valid credentials works and all session vars are set correctly
- [ ] Login failure shows correct error messages
- [ ] Logout properly invalidates the token (check `tbl_user_sessions`)
- [ ] CSRF token survives page reload but is cleared on logout
- [ ] `change-password.php` forces password change when flag is set
- [ ] `super-user-dev.php` creates user with Argon2ID hash; can immediately log in
- [ ] Password hash created in super-user-dev.php is verifiable by AuthService
- [ ] `AuthResult::isSuccessful()` returns true after successful auth
- [ ] All pages use correct asset paths
