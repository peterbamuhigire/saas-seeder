# SaaS Seeder Template — Evaluation Report

**Date:** 2026-03-29
**Branch:** main
**Evaluator:** Claude Opus 4.6 (automated audit against skill standards)

## Scope

Full re-evaluation of the SaaS Seeder template after critical bug fixes and UX improvements applied on 2026-03-29. Evaluated against 10 skill standards:

| Category | Skills |
|----------|--------|
| Security | php-security, vibe-security-skill, web-app-security-audit, dual-auth-rbac, code-safety-scanner |
| Architecture | multi-tenant-saas-architecture, modular-saas-architecture, php-modern-standards, mysql-best-practices |
| UI/UX | webapp-gui-design, form-ux-design, ux-principles-101 |

## Reports

1. [Security Audit](./security-audit.md) — 8 FIXED, 52 PASS, 9 FAIL, 10 WARN
2. [Architecture & Code Quality](./architecture-audit.md) — 3 FIXED, 22 PASS, 10 FAIL, 12 WARN
3. [UI/UX Audit](./ux-audit.md) — 3 FIXED, 78 PASS, 10 FAIL, 13 WARN

## Overall Scorecard

| Category | PASS | FIXED | FAIL | WARN |
|----------|------|-------|------|------|
| Security | 52 | 8 | 9 | 10 |
| Architecture | 22 | 3 | 10 | 12 |
| UI/UX | 78 | 3 | 10 | 13 |
| **Total** | **152** | **14** | **29** | **35** |

## What Was Fixed (This Session)

### Critical Bugs
- `super-user-dev.php` — added APP_ENV=production guard, delegated to UserService
- `CookieHelper.php:25` — fixed operator precedence bug (`??` vs `===`)
- `api/v1/auth/login.php` — rewrote to use existing AuthService (was referencing 4 non-existent functions)

### Security Hardening
- `TokenService` — removed runtime `.env` writes, now requires JWT_SECRET_KEY in env
- `CookieHelper` — removed runtime `.env` writes, now requires COOKIE_ENCRYPTION_KEY in env
- `TokenService` — reduced JWT access token TTL from 24h to 15min
- `TokenService::validateToken()` — fixed bug where `$decoded` was referenced in catch block
- `register.php` — now uses UserService for password hashing via centralized path

### New: UserService (Single Source of Truth)
- All user creation now goes through `src/Auth/Services/UserService.php`
- Validates input, checks duplicates, enforces business rules, hashes via PasswordHelper

### UI/UX Overhaul (15 files)
- `sign-up.php` — replaced non-functional Tabler demo with proper placeholder
- `topbar.php` — consistent brand, sign-out dropdown, mobile hamburger, user avatar
- Menu files — removed "Sign In" from authenticated menus, added active states
- All forms — fixed label `for`/`id` associations, proper `autocomplete` attributes
- `access-denied.php` — fixed "Maduuka" branding, added `prefers-reduced-motion`
- `skeleton.php` — fixed invalid `id=",main-body"`, removed Tabler demo modal
- Dashboard pages — added proper empty states with CTAs
- Removed duplicate `tabler.min.js` loads, dead `href="#"` links, nested footers

## Remaining Issues (Priority Order)

### Must Fix Before Production

1. **PasswordHelper fallback pepper** — should throw, not silently degrade
2. **No login rate limiting / account lockout** — brute force trivial
3. **Session hardening** — 5 missing ini directives (strict_mode, use_only_cookies, etc.)
4. **No security headers** — zero CSP, HSTS, X-Frame-Options, etc.
5. **CORS wildcard** — `Access-Control-Allow-Origin: *` on API
6. **composer.lock gitignored** — non-reproducible builds
7. **CDN without SRI** — SweetAlert2 loaded without integrity hashes
8. **migration.sql incomplete** — missing tbl_franchises, tbl_franchise_role_overrides, wrong collation
9. **Default seed user has bcrypt hash** — incompatible with PasswordHelper

### Should Fix

10. `declare(strict_types=1)` missing from ~23 of 25 src files
11. Two competing PermissionService classes
12. No foreign keys in migration.sql
13. No audit trail infrastructure
14. Skip-to-content links missing from all pages
15. `aria-required="true"` missing from form inputs
