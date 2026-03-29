# Phase 5: Code Cleanup

**Clears:** 0 FAILs, 10 WARNs
**Depends on:** Nothing (runs in parallel)
**Files:** `src/Auth/` directory restructure

---

## Findings Addressed

| ID | Type | Issue |
|----|------|-------|
| A-5.1 | WARN | Two competing PermissionService classes |
| A-5.2 | WARN | Legacy PermissionService uses raw `$_SESSION` |
| A-5.3 | WARN | Empty interface stubs (5 files) |
| A-5.4 | WARN | Non-functional middleware stubs (3 files) |
| A-5.5 | WARN | `tbl_distributors` reference (done in Phase 1 Task 3) |
| A-5.6 | WARN | Database not singleton |
| A-1.13 | WARN | Session regeneration not in AuthService |
| A-1.14 | WARN | Account lockout not enforced (done in Phase 2 Task 3) |
| A-2.1 | WARN | Module registry stub absent |
| A-2.2 | WARN | `hasModuleAccess()` not implemented |

---

## Task 1: Consolidate PermissionService

**TASK:** Merge `src/Auth/PermissionService.php` and `src/Auth/Services/PermissionService.php` into a single class.

**THINK STEP-BY-STEP:**
1. The `Services/PermissionService.php` is used by `AuthService` (constructor-injected)
2. The root `Auth/PermissionService.php` is used by `auth.php:89` (direct instantiation)
3. The root version has session caching (good) but uses raw `$_SESSION` (bad)
4. The Services version has franchise-override logic (good) but no caching

**APPROACH:**
- Keep `src/Auth/Services/PermissionService.php` as the single implementation
- Add session caching from the root version, using `setSession()`/`getSession()` instead of raw `$_SESSION`
- Add franchise-override logic from the root version
- Delete `src/Auth/PermissionService.php`
- Update `src/config/auth.php` to use `App\Auth\Services\PermissionService`

**VALIDATION:**
- [ ] `php -l src/Auth/Services/PermissionService.php`
- [ ] `src/Auth/PermissionService.php` deleted
- [ ] `grep -rn "App\\Auth\\PermissionService" src/` only shows `App\Auth\Services\PermissionService`
- [ ] Permission checks still work (cache hits, cache misses, franchise overrides)

---

## Task 2: Delete empty interface stubs

**FILES:** Delete these files:
- `src/Auth/Interfaces/AuthServiceInterface.php` (empty)
- `src/Auth/Interfaces/UserRepositoryInterface.php` (empty)
- `src/Auth/Interfaces/UserServiceInterface.php` (empty)
- `src/Auth/Interfaces/AuditServiceInterface.php` (empty)
- `src/Auth/Interfaces/PermissionServiceInterface.php` (empty)

**CONSTRAINTS:**
- Keep `src/Auth/Interfaces/TokenServiceInterface.php` — it's actually defined and implemented by `TokenService`
- If any of the "empty" files actually have content, review before deleting

**VALIDATION:**
- [ ] Only `TokenServiceInterface.php` remains in `src/Auth/Interfaces/`
- [ ] No `use` statements reference deleted interfaces

---

## Task 3: Delete non-functional middleware stubs

**FILES:** Delete:
- `src/Auth/Middleware/AuthMiddleware.php`
- `src/Auth/Middleware/PermissionMiddleware.php`
- `src/Auth/Middleware/RoleMiddleware.php`

**CONSTRAINTS:**
- These reference non-existent methods (`$request->getBearerToken()` etc.)
- They are never wired into any middleware pipeline
- If a project needs middleware, they should be built to match the actual routing system

**VALIDATION:**
- [ ] `src/Auth/Middleware/` directory removed
- [ ] No `use` statements reference deleted middleware

---

## Task 4: Move session regeneration into AuthService

**FILE:** `src/Auth/Services/AuthService.php`

**TASK:** Call `regenerateSession()` inside `authenticate()` after successful auth, before writing session vars.

**CODE:** Add before line 134 (`setSession('user_id', ...)`):
```php
// Regenerate session ID to prevent session fixation
if (function_exists('regenerateSession')) {
    regenerateSession();
}
```

**ALSO:** Remove the `regenerateSession()` call from `sign-in.php:90` (it's now centralized).

**VALIDATION:**
- [ ] Session ID changes on login
- [ ] API login (which doesn't use PHP sessions the same way) still works

---

## Task 5: Database connection sharing

**FILE:** `src/Config/Database.php`

**TASK:** Add a static factory method for connection sharing.

**CODE:**
```php
private static ?Database $instance = null;

public static function getInstance(): self
{
    if (self::$instance === null) {
        self::$instance = new self();
    }
    return self::$instance;
}
```

**CONSTRAINTS:**
- Keep the constructor public for backward compatibility
- Document that new code should use `Database::getInstance()->getConnection()`

**VALIDATION:**
- [ ] `php -l src/Config/Database.php`
- [ ] Multiple calls return the same PDO connection

---

## Status

| Task | Status |
|------|--------|
| 1: Consolidate PermissionService | not-started |
| 2: Delete empty interfaces | not-started |
| 3: Delete middleware stubs | not-started |
| 4: Session regeneration in AuthService | not-started |
| 5: Database connection sharing | not-started |
