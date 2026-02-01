<?php
declare(strict_types=1);

namespace App\Auth;

use PDO;
use Exception;

/**
 * PermissionService
 *
 * Handles permission checks and caching for the RBAC system.
 * Provides methods to retrieve user permissions and check authorization.
 *
 * Features:
 * - Session-based caching with 15-minute TTL
 * - Automatic cache invalidation on role/permission changes
 * - Efficient bulk permission queries
 * - Support for wildcards/super_admin bypass
 *
 * @package App\Auth
 * @author Peter Bamuhigire
 * @since 2025-11-17
 */
class PermissionService
{
    private PDO $db;
    private const CACHE_TTL = 900; // 15 minutes
    private const SESSION_CACHE_KEY = 'user_permissions_cache';

    /**
     * Constructor with dependency injection
     *
     * @param PDO $db Database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all permission codes for a user in a franchise
     *
     * Retrieves permissions from user's assigned roles with caching.
     * Cache is stored in session for performance and invalidated when
     * roles or permissions change.
     *
     * @param int $userId User ID
     * @param int $franchiseId Franchise context
     * @return array Permission codes as strings (e.g., ['user_view', 'role_create'])
     * @throws Exception
     */
    public function getUserPermissions(int $userId, int $franchiseId): array
    {
        // If super_admin - return all permissions
        if ($this->isSuperAdmin()) {
            $all = $this->getAllPermissions();
            // Cache the result for speed
            $this->setInCache($userId, $franchiseId, $all);
            return $all;
        }

        // Check session cache first
        $cached = $this->getFromCache($userId, $franchiseId);
        if ($cached !== null) {
            return $cached;
        }

        try {
            // Use Stored Procedure to get final permissions (includes roles + overrides)
            $stmt = $this->db->prepare('CALL sp_get_user_permissions(?, ?)');
            $stmt->execute([$userId, $franchiseId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor(); // Important for next queries

            $permissionsString = $result['permissions'] ?? '';
            $permissions = $permissionsString !== '' ? explode(',', $permissionsString) : [];

            // Cache the result
            $this->setInCache($userId, $franchiseId, $permissions);

            return $permissions;

        } catch (Exception $e) {
            error_log("Failed to get permissions for user $userId: " . $e->getMessage());
            throw new Exception("Failed to retrieve user permissions", 0, $e);
        }
    }

    /**
     * Check if user has a specific permission
     *
     * Returns true if the user's assigned roles include the requested permission.
     * Uses cached permissions for efficiency.
     *
     * @param int $userId User ID
     * @param int $franchiseId Franchise context
     * @param string $permissionCode Permission code to check (e.g., 'user_view')
     * @return bool True if user has permission, false otherwise
     * @throws Exception
     */
    public function hasPermission(int $userId, int $franchiseId, string $permissionCode): bool
    {
        try {
            if ($this->isSuperAdmin()) return true;

            // Check user override first
            $overrideStmt = $this->db->prepare("SELECT up.allowed, p.code FROM tbl_user_permissions up JOIN tbl_permissions p ON up.permission_id = p.id WHERE up.user_id = ? AND up.franchise_id = ? AND p.code = ? LIMIT 1");
            $overrideStmt->execute([$userId, $franchiseId, $permissionCode]);
            $ov = $overrideStmt->fetch(PDO::FETCH_ASSOC);
            if ($ov !== false) {
                return (bool)(int)$ov['allowed'];
            }

            // Otherwise fall back to role permissions
            $permissions = $this->getUserPermissions($userId, $franchiseId);
            return in_array($permissionCode, $permissions, true);

        } catch (Exception $e) {
            error_log("Error checking permission for user $userId: " . $e->getMessage());
            throw new Exception("Failed to check permission", 0, $e);
        }
    }

    /**
     * Require a permission and throw an Exception when missing.
     *
     * @param int $userId
     * @param int $franchiseId
     * @param string $permissionCode
     * @throws Exception
     */
    public function requirePermission($userId, $arg2, $arg3 = null): void
    {
        if (!$this->hasPermission($userId, $arg2, $arg3)) {
            throw new Exception('Forbidden: missing permission ' . ($arg3 ?? $arg2));
        }
    }

    /**
     * Check if user has any of the provided permissions
     *
     * Returns true if the user has at least one of the provided permissions.
     * Useful for optional access scenarios.
     *
     * @param int $userId User ID
     * @param int $franchiseId Franchise context
     * @param array $permissionCodes Array of permission codes
     * @return bool True if user has at least one permission
     * @throws Exception
     */
    public function hasAnyPermission(int $userId, int $franchiseId, array $permissionCodes): bool
    {
        try {
            if ($this->isSuperAdmin()) return true;
            $permissions = $this->getUserPermissions($userId, $franchiseId);

            foreach ($permissionCodes as $code) {
                if (in_array($code, $permissions, true)) {
                    return true;
                }
            }

            return false;

        } catch (Exception $e) {
            error_log("Error checking any permission for user $userId: " . $e->getMessage());
            throw new Exception("Failed to check permissions", 0, $e);
        }
    }

    /**
     * Check if user has all of the provided permissions
     *
     * Returns true only if the user has every provided permission.
     * Useful for requiring multiple permissions simultaneously.
     *
     * @param int $userId User ID
     * @param int $franchiseId Franchise context
     * @param array $permissionCodes Array of permission codes
     * @return bool True if user has all permissions
     * @throws Exception
     */
    public function hasAllPermissions(int $userId, int $franchiseId, array $permissionCodes): bool
    {
        try {
            if ($this->isSuperAdmin()) return true;
            $permissions = $this->getUserPermissions($userId, $franchiseId);

            foreach ($permissionCodes as $code) {
                if (!in_array($code, $permissions, true)) {
                    return false;
                }
            }

            return true;

        } catch (Exception $e) {
            error_log("Error checking all permissions for user $userId: " . $e->getMessage());
            throw new Exception("Failed to check permissions", 0, $e);
        }
    }

    /**
     * Invalidate permission cache for a user
     *
     * Called when user roles or role permissions change to ensure
     * fresh data is fetched on next permission check.
     *
     * @param int $userId User ID
     * @param int $franchiseId Franchise context
     * @return void
     */
    public function invalidateCache(int $userId, int $franchiseId): void
    {
        if (!isset($_SESSION[self::SESSION_CACHE_KEY])) {
            return;
        }

        $key = "{$userId}:{$franchiseId}";
        unset($_SESSION[self::SESSION_CACHE_KEY][$key]);
    }

    /**
     * Invalidate permission cache for all users (system-wide)
     *
     * Called when global permissions are added/removed.
     * Use sparingly as it affects all users.
     *
     * @return void
     */
    public function invalidateAllCache(): void
    {
        unset($_SESSION[self::SESSION_CACHE_KEY]);
    }

    /**
     * Get permission details by code
     *
     * Retrieves full permission information including name and description.
     *
     * @param string $permissionCode Permission code
     * @return ?array Permission data or null if not found
     * @throws Exception
     */
    public function getPermissionByCode(string $permissionCode): ?array
    {
        try {
            $stmt = $this->db->prepare('
                SELECT id, code, name, description, created_at
                FROM tbl_permissions
                WHERE code = ?
            ');

            $stmt->execute([$permissionCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (Exception $e) {
            error_log("Failed to get permission details: " . $e->getMessage());
            throw new Exception("Failed to retrieve permission", 0, $e);
        }
    }

    /**
     * Get all global permissions
     *
     * Returns all available permission codes in the system.
     * Useful for admin UIs and validation.
     *
     * @return array Array of permission codes
     * @throws Exception
     */
    public function getAllPermissions(): array
    {
        try {
            $stmt = $this->db->prepare('
                SELECT code FROM tbl_permissions ORDER BY code
            ');

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        } catch (Exception $e) {
            error_log("Failed to get all permissions: " . $e->getMessage());
            throw new Exception("Failed to retrieve permissions", 0, $e);
        }
    }

    // ========================================================================
    // Private Helper Methods
    // ========================================================================

    /**
     * Get permissions from session cache
     *
     * @param int $userId User ID
     * @param int $franchiseId Franchise context
     * @return ?array Cached permissions or null if expired/not found
     */
    private function getFromCache(int $userId, int $franchiseId): ?array
    {
        if (!isset($_SESSION[self::SESSION_CACHE_KEY])) {
            return null;
        }

        $key = "{$userId}:{$franchiseId}";

        if (!isset($_SESSION[self::SESSION_CACHE_KEY][$key])) {
            return null;
        }

        $cached = $_SESSION[self::SESSION_CACHE_KEY][$key];

        // Check if cache has expired
        if (time() - $cached['timestamp'] > self::CACHE_TTL) {
            unset($_SESSION[self::SESSION_CACHE_KEY][$key]);
            return null;
        }

        return $cached['permissions'];
    }

    /**
     * Check from session whether current user is super_admin
     *
     * @return bool
     */
    private function isSuperAdmin(): bool
    {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'super_admin';
    }

    /**
     * Store permissions in session cache
     *
     * @param int $userId User ID
     * @param int $franchiseId Franchise context
     * @param array $permissions Permission codes
     * @return void
     */
    private function setInCache(int $userId, int $franchiseId, array $permissions): void
    {
        if (!isset($_SESSION[self::SESSION_CACHE_KEY])) {
            $_SESSION[self::SESSION_CACHE_KEY] = [];
        }

        $key = "{$userId}:{$franchiseId}";
        $_SESSION[self::SESSION_CACHE_KEY][$key] = [
            'permissions' => $permissions,
            'timestamp' => time()
        ];
    }

    /**
     * Get user permission overrides as associative array code => allowed
     * @param int $userId
     * @param int $franchiseId
     * @return array
     */
    public function getUserPermissionOverrides(int $userId, int $franchiseId): array
    {
        try {
            $stmt = $this->db->prepare('SELECT p.code, up.allowed FROM tbl_user_permissions up JOIN tbl_permissions p ON up.permission_id = p.id WHERE up.user_id = ? AND up.franchise_id = ?');
            $stmt->execute([$userId, $franchiseId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $map = [];
            foreach ($rows as $r) {
                $map[$r['code']] = (bool)(int)$r['allowed'];
            }
            return $map;
        } catch (Exception $e) {
            error_log('Failed to get user permission overrides: ' . $e->getMessage());
            return [];
        }
    }
}
