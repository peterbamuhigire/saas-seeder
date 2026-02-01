<?php
namespace App\Auth\Interfaces;

use App\Auth\Models\User;

interface UserRepositoryInterface 
{
    /**
     * Find user by ID
     */
    public function findById(int $id): ?User;
    
    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?User;
    
    /**
     * Find user by email within franchise
     */
    public function findByEmail(string $email, int $franchiseId): ?User;
    
    /**
     * Create new user
     */
    public function create(array $userData): User;
    
    /**
     * Update existing user
     */
    public function update(int $id, array $userData): bool;
    
    /**
     * Delete user (soft delete)
     */
    public function delete(int $id): bool;
    
    /**
     * Get users by franchise
     */
    public function getByFranchise(int $franchiseId, array $filters = []): array;
    
    /**
     * Update last login
     */
    public function updateLastLogin(int $id): bool;
    
    /**
     * Update failed login attempts
     */
    public function incrementFailedLogins(int $id): int;
    
    /**
     * Reset failed login attempts
     */
    public function resetFailedLogins(int $id): bool;
}
