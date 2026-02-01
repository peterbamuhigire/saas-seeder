<?php
namespace App\Auth\Services;

use PDO;

class PermissionService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;

        // Initialize session helpers if not already loaded
        if (!function_exists('hasSession')) {
            require_once dirname(__FILE__) . '/../../config/session.php';
        }
    }

    private function isSuperAdmin(): bool
    {
        return hasSession('user_type') && getSession('user_type') === 'super_admin';
    }

    public function checkUserPermission(int $userId, string $permissionCode): bool
    {
        // Super admin bypass
        if ($this->isSuperAdmin()) return true;

            // Check user-level overrides first
            $overrideStmt = $this->db->prepare("SELECT up.allowed FROM tbl_user_permissions up JOIN tbl_permissions p ON up.permission_id = p.id WHERE up.user_id = ? AND p.code = ? LIMIT 1");
            $overrideStmt->execute([$userId, $permissionCode]);
            $ov = $overrideStmt->fetch(PDO::FETCH_ASSOC);
            if ($ov !== false) return (bool)(int)$ov['allowed'];

            // Resolve permission id for further checks
            $pidStmt = $this->db->prepare('SELECT id FROM tbl_permissions WHERE code = ? LIMIT 1');
            $pidStmt->execute([$permissionCode]);
            $permRow = $pidStmt->fetch(PDO::FETCH_ASSOC);
            if (!$permRow) return false; // permission doesn't exist
            $permissionId = (int)$permRow['id'];

            // Check user roles: prefer global_role_id if present (new model)
            $rolesStmt = $this->db->prepare('SELECT global_role_id, franchise_id FROM tbl_user_roles WHERE user_id = ?');
            $rolesStmt->execute([$userId]);
            $userRoles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($userRoles as $r) {
                // If a mapped global_role_id exists, consult franchise overrides then default global permissions
                if (!empty($r['global_role_id'])) {
                    $globalRoleId = (int)$r['global_role_id'];
                    $franchiseId = $r['franchise_id'] ?? null;

                    // Check if franchise has an override for this permission
                    if ($franchiseId) {
                        $frOverride = $this->db->prepare('SELECT is_enabled FROM tbl_franchise_role_overrides WHERE franchise_id = ? AND global_role_id = ? AND permission_id = ? LIMIT 1');
                        $frOverride->execute([$franchiseId, $globalRoleId, $permissionId]);
                        $fr = $frOverride->fetch(PDO::FETCH_ASSOC);
                        if ($fr !== false) {
                            if ((int)$fr['is_enabled'] === 1) return true; else continue; // explicit disable
                        }
                    }

                    // No override or override enabled, check if permission exists in global role defaults
                    $grStmt = $this->db->prepare('SELECT COUNT(*) FROM tbl_global_role_permissions WHERE global_role_id = ? AND permission_id = ?');
                    $grStmt->execute([$globalRoleId, $permissionId]);
                    if ((int)$grStmt->fetchColumn() > 0) return true;
                }
            }

            return false;
    }

    /**
     * Backwards-compatible hasPermission with franchise support
     *
     * @param int $userId
     * @param int $franchiseId
     * @param string $permissionCode
     * @return bool
     */
    public function hasPermission($userId, $arg2, $arg3 = null): bool
    {
        // Support signatures: hasPermission($userId, $permissionCode) or hasPermission($userId, $franchiseId, $permissionCode)
        if ($arg3 === null && is_string($arg2)) {
            $permissionCode = $arg2;
            $franchiseId = hasSession('franchise_id') ? (int)getSession('franchise_id') : null;
        } elseif (is_int($arg2) && is_string($arg3)) {
            $franchiseId = $arg2;
            $permissionCode = $arg3;
        } else {
            // Invalid parameter combination
            throw new \InvalidArgumentException('Invalid arguments for hasPermission');
        }

        // Super admin bypass
        if ($this->isSuperAdmin()) return true;

        // Check user override first (franchise-scoped if provided)
        $overrideFranchise = $franchiseId;
        if ($overrideFranchise === null) {
            $overrideFranchise = hasSession('franchise_id') ? (int)getSession('franchise_id') : null;
        }
        if ($overrideFranchise !== null) {
            $overrideStmt = $this->db->prepare("SELECT up.allowed, p.code FROM tbl_user_permissions up JOIN tbl_permissions p ON up.permission_id = p.id WHERE up.user_id = ? AND up.franchise_id = ? AND p.code = ? LIMIT 1");
            $overrideStmt->execute([$userId, $overrideFranchise, $permissionCode]);
            $ov = $overrideStmt->fetch(PDO::FETCH_ASSOC);
            if ($ov !== false) {
                return (bool)(int)$ov['allowed'];
            }
        }

        // Resolve permission id
        $pidStmt = $this->db->prepare('SELECT id FROM tbl_permissions WHERE code = ? LIMIT 1');
        $pidStmt->execute([$permissionCode]);
        $perm = $pidStmt->fetch(PDO::FETCH_ASSOC);
        if (!$perm) return false;
        $permissionId = (int)$perm['id'];

        // Evaluate all roles for the user and apply franchise-scoped overrides and global role defaults
        $rolesSql = 'SELECT global_role_id, franchise_id FROM tbl_user_roles WHERE user_id = ?';
        $rstmt = $this->db->prepare($rolesSql);
        $rstmt->execute([$userId]);
        $rows = $rstmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            // If a franchise filter is provided ensure row matches
            if ($franchiseId !== null && (int)$r['franchise_id'] !== (int)$franchiseId) continue;

            // Check global role path first
            if (!empty($r['global_role_id'])) {
                if ($franchiseId !== null) {
                    $fo = $this->db->prepare('SELECT is_enabled FROM tbl_franchise_role_overrides WHERE franchise_id = ? AND global_role_id = ? AND permission_id = ? LIMIT 1');
                    $fo->execute([$franchiseId, (int)$r['global_role_id'], $permissionId]);
                    $fov = $fo->fetch(PDO::FETCH_ASSOC);
                    if ($fov !== false) {
                        if ((int)$fov['is_enabled'] === 1) return true; else continue;
                    }
                }

                $g = $this->db->prepare('SELECT COUNT(*) FROM tbl_global_role_permissions WHERE global_role_id = ? AND permission_id = ?');
                $g->execute([(int)$r['global_role_id'], $permissionId]);
                if ((int)$g->fetchColumn() > 0) return true;
            }
        }

        return false;
    }

    /**
     * Require a permission and throw a 403-style exception when missing.
     * This is intentionally lightweight to allow api code to catch and return 403.
     *
     * @param int $userId
     * @param int $franchiseId
     * @param string $permissionCode
     * @throws \Exception
     */
    public function requirePermission($userId, $arg2, $arg3 = null): void
    {
        // Wrap hasPermission with the same flexible params, reusing the logic
        if (!$this->hasPermission($userId, $arg2, $arg3)) {
            throw new \Exception('Forbidden: missing permission ' . ($arg3 ?? $arg2));
        }
    }

    /**
     * Check if user has any of the specified permissions
     *
     * @param int $userId
     * @param int $franchiseId
     * @param array $permissionCodes
     * @return bool
     */
    public function hasAnyPermission(int $userId, int $franchiseId, array $permissionCodes): bool
    {
        // Super admin bypass
        if ($this->isSuperAdmin()) return true;

        if (empty($permissionCodes)) return false;

        foreach ($permissionCodes as $code) {
            if ($this->hasPermission($userId, $franchiseId, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the specified permissions
     *
     * @param int $userId
     * @param int $franchiseId
     * @param array $permissionCodes
     * @return bool
     */
    public function hasAllPermissions(int $userId, int $franchiseId, array $permissionCodes): bool
    {
        // Super admin bypass
        if ($this->isSuperAdmin()) return true;

        if (empty($permissionCodes)) return true;

        foreach ($permissionCodes as $code) {
            if (!$this->hasPermission($userId, $franchiseId, $code)) {
                return false;
            }
        }

        return true;
    }

    public function getUserPermissions(int $userId, ?int $franchiseId = null): array 
    {
        // Try using stored procedure if available
        try {
            // If no franchiseId provided, use session context
            $fr = $franchiseId ?? (hasSession('franchise_id') ? (int)getSession('franchise_id') : null);
            if ($fr === null) {
                $stmt = $this->db->prepare("CALL sp_get_user_permissions(?)");
                $stmt->execute([$userId]);
            } else {
                $stmt = $this->db->prepare("CALL sp_get_user_permissions(?, ?)");
                $stmt->execute([$userId, $fr]);
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            if ($row !== false) {
                $perms = [];
                $codes = $row['permissions'] ? explode(',', $row['permissions']) : [];
                foreach ($codes as $c) {
                    $perms[$c] = ['code' => $c, 'name' => '', 'module' => '', 'role_name' => null, 'allowed' => true];
                }
                return array_values($perms);
            }
        } catch (\PDOException $e) {
            // Stored procedure not available or failed – fallback to SQL
            // No action, fall through to SQL-based implementation
        }
        // Default franchise if not provided
        if ($franchiseId === null) {
            $franchiseId = hasSession('franchise_id') ? (int)getSession('franchise_id') : null;
        }

        // Gather permissions via global role permissions
        $perms = [];

        // Get user roles (optionally scoped to franchise)
        $roleSql = 'SELECT global_role_id, franchise_id FROM tbl_user_roles WHERE user_id = ?';
        $roleParams = [$userId];
        if ($franchiseId !== null) {
            $roleSql .= ' AND franchise_id = ?';
            $roleParams[] = $franchiseId;
        }

        $roleStmt = $this->db->prepare($roleSql);
        $roleStmt->execute($roleParams);
        $roles = $roleStmt->fetchAll(PDO::FETCH_ASSOC);

        // Collect permissions from global roles, respecting franchise overrides
        $permFetchStmt = $this->db->prepare('SELECT p.code, p.name, p.module, grp.permission_id FROM tbl_global_role_permissions grp JOIN tbl_permissions p ON grp.permission_id = p.id WHERE grp.global_role_id = ?');

        foreach ($roles as $r) {
            // Global role path
            if (!empty($r['global_role_id'])) {
                $permFetchStmt->execute([(int)$r['global_role_id']]);
                $grows = $permFetchStmt->fetchAll(PDO::FETCH_ASSOC);
                // Apply default permissions
                foreach ($grows as $gr) {
                    $code = $gr['code'];
                    $perms[$code] = ['code' => $code, 'name' => $gr['name'], 'module' => $gr['module'], 'role_name' => null, 'allowed' => true, 'permission_id' => $gr['permission_id']];
                }

                // Apply franchise overrides if franchise context provided
                if ($franchiseId !== null) {
                    $ovStmt = $this->db->prepare('SELECT permission_id, is_enabled FROM tbl_franchise_role_overrides WHERE franchise_id = ? AND global_role_id = ?');
                    $ovStmt->execute([$franchiseId, (int)$r['global_role_id']]);
                    $ovRows = $ovStmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($ovRows as $o) {
                        // find permission code for this id
                        $pId = (int)$o['permission_id'];
                        $pCodeRow = $this->db->prepare('SELECT code, name, module FROM tbl_permissions WHERE id = ? LIMIT 1');
                        $pCodeRow->execute([$pId]);
                        $pc = $pCodeRow->fetch(PDO::FETCH_ASSOC);
                        if (!$pc) continue;
                        $code = $pc['code'];
                        if ((int)$o['is_enabled'] === 1) {
                            $perms[$code] = ['code' => $code, 'name' => $pc['name'], 'module' => $pc['module'], 'role_name' => null, 'allowed' => true, 'permission_id' => $pId];
                        } else {
                            // disable
                            $perms[$code] = ['code' => $code, 'name' => $pc['name'], 'module' => $pc['module'], 'role_name' => null, 'allowed' => false, 'permission_id' => $pId];
                        }
                    }
                }
            }
        }
        // (permission collection above already handled global role permissions)

        // Apply user-level overrides
        $overrides = $this->getUserPermissionOverrides($userId, $franchiseId);
        foreach ($overrides as $code => $allowed) {
            if ($allowed) {
                if (!isset($perms[$code])) {
                    // Add a stub entry with permission details
                    $pd = $this->db->prepare('SELECT code, name, module FROM tbl_permissions WHERE code = ? LIMIT 1');
                    $pd->execute([$code]);
                    $pr = $pd->fetch(PDO::FETCH_ASSOC);
                    if ($pr) {
                        $perms[$code] = array_merge($pr, ['role_name' => null, 'allowed' => true]);
                    }
                } else {
                    $perms[$code]['allowed'] = true;
                }
            } else {
                // Deny override: ensure the permission is marked denied
                $perms[$code] = ['code' => $code, 'name' => '', 'module' => '', 'role_name' => null, 'allowed' => false];
            }
        }

        return array_values($perms);
    }

    /**
     * Return associative array of permission code => allowed (bool) for a user
     */
    public function getUserPermissionOverrides(int $userId, ?int $franchiseId = null): array
    {
        $sql = 'SELECT p.code, up.allowed FROM tbl_user_permissions up JOIN tbl_permissions p ON up.permission_id = p.id WHERE up.user_id = ?';
        $params = [$userId];
        if ($franchiseId !== null) {
            $sql .= ' AND up.franchise_id = ?';
            $params[] = $franchiseId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $r) {
            $map[$r['code']] = (bool)(int)$r['allowed'];
        }
        return $map;
    }

    public function assignRolePermission(int $roleId, int $permissionId, int $grantedBy): void 
    {
        try {
            // If this is a global role, use new global_role_permissions table
            $check = $this->db->prepare('SELECT id FROM tbl_global_roles WHERE id = ? LIMIT 1');
            $check->execute([$roleId]);
            $g = $check->fetch(PDO::FETCH_ASSOC);
            if ($g) {
                // Insert if not exists
                $ins = $this->db->prepare('INSERT IGNORE INTO tbl_global_role_permissions (global_role_id, permission_id) VALUES (?, ?)');
                $ins->execute([$roleId, $permissionId]);

                // Increment permission_version for all franchises so active tokens are invalidated
                $this->db->exec('UPDATE tbl_franchises SET permission_version = COALESCE(permission_version, 0) + 1');
                return;
            }

            // Fallback to legacy stored procedure for franchise-scoped roles
            $stmt = $this->db->prepare("CALL sp_grant_role_permission(?, ?, ?)");
            $stmt->execute([$roleId, $permissionId, $grantedBy]);
        } catch (\PDOException $e) {
            throw new \Exception("Failed to assign permission: " . $e->getMessage());
        }
    }

    public function revokeRolePermission(int $roleId, int $permissionId, int $revokedBy): void 
    {
        try {
            // If this is a global role, delete from global permissions
            $check = $this->db->prepare('SELECT id FROM tbl_global_roles WHERE id = ? LIMIT 1');
            $check->execute([$roleId]);
            $g = $check->fetch(PDO::FETCH_ASSOC);
            if ($g) {
                $del = $this->db->prepare('DELETE FROM tbl_global_role_permissions WHERE global_role_id = ? AND permission_id = ?');
                $del->execute([$roleId, $permissionId]);

                // Increment permission_version for all franchises so active tokens are invalidated
                $this->db->exec('UPDATE tbl_franchises SET permission_version = COALESCE(permission_version, 0) + 1');
                return;
            }

            // Fallback to legacy stored procedure
            $stmt = $this->db->prepare("CALL sp_revoke_role_permission(?, ?, ?)");
            $stmt->execute([$roleId, $permissionId, $revokedBy]);
        } catch (\PDOException $e) {
            throw new \Exception("Failed to revoke permission: " . $e->getMessage());
        }
    }

    /**
     * Set or remove a franchise-specific override for a global role permission.
     * This updates tbl_franchise_role_overrides and increments the franchise permission_version.
     */
    public function setFranchiseRoleOverride(int $franchiseId, int $globalRoleId, int $permissionId, bool $isEnabled, int $changedBy): void
    {
        try {
            $stmt = $this->db->prepare('INSERT INTO tbl_franchise_role_overrides (franchise_id, global_role_id, permission_id, is_enabled, created_at) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE is_enabled = VALUES(is_enabled), updated_at = NOW()');
            $stmt->execute([$franchiseId, $globalRoleId, $permissionId, $isEnabled ? 1 : 0]);

            // Bump franchise permission_version to invalidate tokens for the franchise
            $inc = $this->db->prepare('UPDATE tbl_franchises SET permission_version = COALESCE(permission_version, 0) + 1 WHERE id = ?');
            $inc->execute([$franchiseId]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to set franchise role override: ' . $e->getMessage());
        }
    }

    public function getRolePermissions(int $roleId): array 
    {
        try {
            // If roleId exists in tbl_global_roles treat it as a global_role_id
            $checkGlobal = $this->db->prepare('SELECT id, code, name FROM tbl_global_roles WHERE id = ? LIMIT 1');
            $checkGlobal->execute([$roleId]);
            $g = $checkGlobal->fetch(PDO::FETCH_ASSOC);
            if ($g) {
                $stmt = $this->db->prepare('SELECT p.id as permission_id, p.code, p.name, p.module FROM tbl_global_role_permissions grp JOIN tbl_permissions p ON grp.permission_id = p.id WHERE grp.global_role_id = ? ORDER BY p.module, p.name');
                $stmt->execute([$roleId]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Fallback to legacy stored procedure which expects role_id
            $stmt = $this->db->prepare("CALL sp_get_role_permissions(?)");
            $stmt->execute([$roleId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Failed to get role permissions: " . $e->getMessage());
        }
    }
}
