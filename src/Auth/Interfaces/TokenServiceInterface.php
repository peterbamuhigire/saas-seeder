<?php
// src/Auth/Interfaces/TokenServiceInterface.php

namespace App\Auth\Interfaces;

interface TokenServiceInterface 
{
    /**
     * Generate new authentication token
     */
    public function generateToken(int $userId, int $franchiseId): string;
    
    /**
     * Validate existing token
     */
    public function validateToken(string $token): bool;
    
    /**
     * Refresh existing token
     */
    public function refreshToken(string $currentToken): string;
    
    /**
     * Get user ID from token
     */
    public function getUserIdFromToken(string $token): ?int;
    
    /**
     * Get current token from request
     */
    public function getCurrentToken(): ?string;
    
    /**
     * Invalidate a token
     */
    public function invalidateToken(string $token): void;
}

