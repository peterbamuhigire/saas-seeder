<?php
// /src/Auth/Interfaces/UserServiceInterface.php

namespace App\Auth\Interfaces;

use App\Auth\DTO\UserDTO;
use App\Auth\Models\User;

interface UserServiceInterface 
{
    /**
     * Create new user
     */
    public function createUser(UserDTO $userData): User;
    
    /**
     * Update existing user
     */
    public function updateUser(int $userId, UserDTO $userData): User;
    
    /**
     * Delete user
     */
    public function deleteUser(int $userId): void;
    
    /**
     * Get user by ID
     */
    public function getUserById(int $userId): ?User;
    
    /**
     * Get user by username
     */
    public function getUserByUsername(string $username): ?User;
}
