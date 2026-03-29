# Phase 7: Infrastructure Stubs

**Clears:** 1 FAIL, 4 WARNs
**Depends on:** Phase 1 (audit log table), Phase 5 (cleaned PermissionService)
**Files:** `src/Auth/Services/AuditService.php` (new), `src/config/auth.php`, `src/Config/Database.php`

---

## Findings Addressed

| ID | Type | Issue |
|----|------|-------|
| A-1.11 | FAIL | No audit trail infrastructure (logic — table created in Phase 1) |
| A-2.1 | WARN | Module registry stub absent |
| A-2.2 | WARN | `hasModuleAccess()` / `requireModuleAccess()` not implemented |
| A-4.13 | WARN | `sp_get_user_permissions` ignores franchise overrides |
| S-44 | WARN | Account lockout threshold not enforced (logic — done in Phase 2 Task 3) |

---

## Task 1: AuditService — audit trail infrastructure

**FILE:** `src/Auth/Services/AuditService.php` (new)

**TASK:** Create a minimal audit service that logs privileged operations to `tbl_audit_log`.

**CODE:**
```php
<?php
declare(strict_types=1);

namespace App\Auth\Services;

use PDO;

final class AuditService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Log an auditable action.
     *
     * @param string   $action     Action code (e.g., 'USER_CREATED', 'PERMISSION_CHANGED')
     * @param int|null $userId     Acting user ID (null for system actions)
     * @param int|null $franchiseId Franchise scope (null for system-wide)
     * @param string   $entityType Entity affected (e.g., 'user', 'role', 'franchise')
     * @param int|null $entityId   ID of affected entity
     * @param array    $details    Additional context (stored as JSON)
     */
    public function log(
        string $action,
        ?int $userId = null,
        ?int $franchiseId = null,
        string $entityType = '',
        ?int $entityId = null,
        array $details = [],
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO tbl_audit_log
              (user_id, franchise_id, action, entity_type, entity_id, details, ip_address, user_agent, created_at)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $userId,
            $franchiseId,
            $action,
            $entityType !== '' ? $entityType : null,
            $entityId,
            !empty($details) ? json_encode($details) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}
```

**ALSO:** Add a convenience function to `src/config/auth.php`:
```php
function auditLog(string $action, string $entityType = '', ?int $entityId = null, array $details = []): void
{
    try {
        $db = (new \App\Config\Database())->getConnection();
        $audit = new \App\Auth\Services\AuditService($db);
        $audit->log(
            $action,
            function_exists('getSession') ? (int)(getSession('user_id') ?? 0) : null,
            function_exists('getSession') ? (getSession('franchise_id') ? (int)getSession('franchise_id') : null) : null,
            $entityType,
            $entityId,
            $details
        );
    } catch (\Exception $e) {
        error_log('Audit log failed: ' . $e->getMessage());
    }
}
```

**VALIDATION:**
- [ ] `php -l src/Auth/Services/AuditService.php`
- [ ] `auditLog('TEST_ACTION')` inserts a row into `tbl_audit_log`

---

## Task 2: Module access stubs

**FILE:** `src/config/auth.php`

**TASK:** Add `hasModuleAccess()` and `requireModuleAccess()` stub functions that always return true / pass. These are placeholders for the modular-saas-architecture pattern.

**CODE:**
```php
/**
 * Check if the current franchise has access to a module.
 * Stub — always returns true until tbl_modules and tbl_franchise_modules are implemented.
 */
function hasModuleAccess(string $moduleCode): bool
{
    // TODO: Query tbl_franchise_modules when module registry is implemented
    return true;
}

/**
 * Require module access — redirects to access-denied if module is not enabled.
 * Stub — always passes until module registry is implemented.
 */
function requireModuleAccess(string $moduleCode): void
{
    if (!hasModuleAccess($moduleCode)) {
        header('Location: /access-denied.php?reason=module_disabled&module=' . urlencode($moduleCode));
        exit();
    }
}
```

**VALIDATION:**
- [ ] `php -l src/config/auth.php`
- [ ] `hasModuleAccess('ANY_MODULE')` returns `true`

---

## Task 3: Update sp_get_user_permissions to include overrides

**FILE:** `docs/seeder-template/migration.sql` (in Phase 1 rewrite)

**TASK:** Rewrite the `sp_get_user_permissions` stored procedure to consult:
1. Global role permissions (`tbl_global_role_permissions`)
2. Franchise role overrides (`tbl_franchise_role_overrides`) — can disable a role permission per franchise
3. User-level overrides (`tbl_user_permissions`) — highest priority

**THINK STEP-BY-STEP:**
1. Start with all permissions from user's global roles
2. Remove permissions disabled by franchise overrides (`is_enabled = 0`)
3. Add permissions explicitly granted at user level (`allowed = 1`)
4. Remove permissions explicitly denied at user level (`allowed = 0`)
5. Return the final set as comma-separated codes

**CONSTRAINTS:**
- Super admins bypass all checks (return all permission codes)
- Must handle NULL franchise_id (super admins have no franchise)
- Backward compatible with existing `PermissionService` PHP code

**VALIDATION:**
- [ ] SP returns correct permissions when franchise overrides disable a role permission
- [ ] SP returns correct permissions when user-level override grants an extra permission

---

## Status

| Task | Status |
|------|--------|
| 1: AuditService | not-started |
| 2: Module access stubs | not-started |
| 3: SP permission overrides | not-started |
