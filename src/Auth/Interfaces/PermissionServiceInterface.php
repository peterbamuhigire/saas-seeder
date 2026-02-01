<?php
// /src/Auth/Interfaces/PermissionServiceInterface.php

namespace App\Auth\Interfaces;

interface PermissionServiceInterface 
{
    /**
     * Check if user has specific permission
     */
    public function checkUserPermission(int $userId, string $permissionCode): bool;
    
    /**
     * Get all permissions for user
     */
    public function getUserPermissions(int $userId, int $franchiseId = null): array;
    
    /**
     * Assign role to user
     */
    public function assignUserRole(int $userId, int $roleId): void;
}
