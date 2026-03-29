# Architecture & Code Quality Audit

**Date:** 2026-03-29

Evaluated against: multi-tenant-saas-architecture, modular-saas-architecture, php-modern-standards, mysql-best-practices

---

## 1. Multi-Tenant SaaS Architecture

| # | Requirement | Status | Notes |
|---|------------|--------|-------|
| 1.1 | Three-tier panel structure | **PASS** | `/public/`, `/adminpanel/`, `/memberpanel/` correctly separated |
| 1.2 | `franchise_id` in every query | **PASS** | Template queries consistently use `franchise_id` |
| 1.3 | Server-side franchise_id extraction | **PASS** | All code uses `getSession('franchise_id')`, never `$_POST` |
| 1.4 | Session prefix system | **PASS** | `SESSION_PREFIX = 'saas_app_'` with full helpers |
| 1.5 | `isSuperAdmin()` uses `getSession()` | **FIXED** | No longer uses raw `$_SESSION` |
| 1.6 | Argon2ID + salt + pepper | **PASS** | Correctly implemented in PasswordHelper |
| 1.7 | AuthService single source of truth | **PASS** | All session writes go through `AuthService::authenticate()` |
| 1.8 | `AuthResult::getStatus() === 'SUCCESS'` | **PASS** | Uppercase, strict comparison |
| 1.9 | CSRF uses session helpers | **FIXED** | Uses `setSession()`/`getSession()` throughout |
| 1.10 | Admin panel access enforcement | **PASS** | `auth.php:177-182` blocks non-admins |
| 1.11 | Audit trail for privileged operations | **FAIL** | No `auditLog()`, no audit table in migration |
| 1.12 | HTTPS auto-detection | **PASS** | `session.php:22-24` |
| 1.13 | Session regeneration on login | **WARN** | Called in `sign-in.php` but not inside `AuthService::authenticate()` |
| 1.14 | Account lockout enforcement | **WARN** | Counter incremented but never checked |
| 1.15 | `session.use_strict_mode` | **FAIL** | Not set |

## 2. Modular SaaS Architecture

| # | Requirement | Status | Notes |
|---|------------|--------|-------|
| 2.1 | Module registry infrastructure | **WARN** | No `tbl_modules`, `tbl_franchise_modules`. Acceptable for template but stub is absent. |
| 2.2 | `hasModuleAccess()` / `requireModuleAccess()` | **WARN** | Not implemented |
| 2.3 | Dynamic navigation by enabled modules | **N/A** | No modules yet |

## 3. PHP Modern Standards

| # | Requirement | Status | Notes |
|---|------------|--------|-------|
| 3.1 | `declare(strict_types=1)` | **FAIL** | Only 2 of ~25 files: `UserService.php`, `src/Auth/PermissionService.php` |
| 3.2 | Full type hints (params + returns) | **FAIL** | `Database.php` methods, `session.php` functions, `PermissionService` methods all missing types |
| 3.3 | PSR-4 autoloading | **PASS** | `"App\\": "src/"` in composer.json |
| 3.4 | `final` by default | **FAIL** | Only `UserService` is `final`. 11 other classes are not. |
| 3.5 | `readonly` for DTOs | **FAIL** | `AuthResult`, `LoginDTO` should be `final readonly` |
| 3.6 | Constructor promotion | **FAIL** | `AuthResult` uses old-style property assignment |
| 3.7 | Match expressions | **PASS** | No problematic switch statements |
| 3.8 | Early returns | **PASS** | Used consistently |
| 3.9 | Strict comparison | **PASS** | `===` and `in_array(..., true)` used correctly |
| 3.10 | Password hashing centralized | **FIXED** | All creation via UserService/PasswordHelper |
| 3.11 | `filter_var` for email | **PASS** | `UserService.php:74`, `register.php:41` |
| 3.12 | Prepared statements | **PASS** | All SQL parameterized |
| 3.13 | Output encoding | **PASS** | `htmlspecialchars()` throughout |
| 3.14 | PHP 8.0+ minimum | **PASS** | `composer.json` requires `>=8.0` |
| 3.15 | Code quality tooling | **FAIL** | No PHPStan, Pint, or test configuration |
| 3.16 | Cross-platform file naming | **WARN** | `src/config/database.php` lowercase but namespace is `App\Config\Database`. Would fail on Linux. |

## 4. MySQL Best Practices

| # | Requirement | Status | Notes |
|---|------------|--------|-------|
| 4.1 | `utf8mb4_unicode_ci` collation | **FAIL** | `migration.sql` omits `COLLATE`. Defaults to `utf8mb4_0900_ai_ci` on MySQL 8.4. Only fix script corrects this. |
| 4.2 | `ROW_FORMAT=DYNAMIC` | **FAIL** | Not specified on any table |
| 4.3 | `ENGINE=InnoDB` | **PASS** | All tables use InnoDB |
| 4.4 | `BIGINT UNSIGNED` for PKs | **PASS** | All PKs correct |
| 4.5 | Smallest sufficient data types | **PASS** | Appropriate types used |
| 4.6 | Foreign keys | **FAIL** | Zero FK constraints in `migration.sql`. Only fix script adds one FK. |
| 4.7 | Composite indexes follow ESR rule | **PASS** | Compound indexes structured correctly |
| 4.8 | Parameterized queries | **PASS** | All PHP uses prepared statements |
| 4.9 | `tbl_user_sessions.franchise_id` type | **FAIL** | `BIGINT DEFAULT NULL` (signed) vs all others `BIGINT UNSIGNED` |
| 4.10 | `tbl_franchises` in main migration | **FAIL** | Only exists in separate fix script |
| 4.11 | `tbl_franchise_role_overrides` exists | **FAIL** | Referenced in PHP code but never created. Will cause runtime PDOException. |
| 4.12 | Default seed user hash | **FAIL** | `migration.sql:343` uses bcrypt, incompatible with PasswordHelper (Argon2ID) |
| 4.13 | `sp_get_user_permissions` includes overrides | **WARN** | SP only joins roles->permissions. Does not consult franchise overrides or user-level overrides. |
| 4.14 | Application DB user (not root) | **WARN** | Defaults to `root` with no password |

## 5. Cross-Cutting Concerns

| # | Issue | Status | Notes |
|---|-------|--------|-------|
| 5.1 | Two competing PermissionService classes | **WARN** | `src/Auth/PermissionService.php` vs `src/Auth/Services/PermissionService.php` — different implementations, different consumers |
| 5.2 | Legacy PermissionService uses raw `$_SESSION` | **WARN** | Bypasses session prefix system for cache storage |
| 5.3 | Empty interface stubs | **WARN** | 5 interface files exist but no classes implement them |
| 5.4 | Non-functional middleware stubs | **WARN** | `AuthMiddleware`, `PermissionMiddleware`, `RoleMiddleware` reference non-existent methods |
| 5.5 | `tbl_distributors` referenced but missing | **WARN** | `AuthService.php:224` joins this table but no migration creates it |
| 5.6 | `Database` not a singleton | **WARN** | Each `new Database()` creates a new PDO connection |

---

## Summary

| Status | Count |
|--------|-------|
| **PASS** | 22 |
| **FIXED** | 3 |
| **FAIL** | 10 |
| **WARN** | 12 |
| **N/A** | 3 |

### Priority Fixes

1. **migration.sql** — add `COLLATE utf8mb4_unicode_ci`, `ROW_FORMAT=DYNAMIC`, foreign keys, `tbl_franchises`, `tbl_franchise_role_overrides`
2. **Default seed user** — remove or use PasswordHelper-compatible hash
3. **`declare(strict_types=1)`** — add to all src files
4. **Consolidate PermissionService** — merge two competing implementations
5. **Audit trail** — add `tbl_audit_log` and `auditLog()` function
6. **Mark classes `final`** — all services, helpers, DTOs
7. **Constructor promotion + readonly** — DTOs
8. **Code quality tooling** — add PHPStan, Pint configuration
