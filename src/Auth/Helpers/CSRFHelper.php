<?php
namespace App\Auth\Helpers;

class CSRFHelper 
{
    /**
     * Generate a new CSRF token or return existing one
     */
    public function generateToken(): string 
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate provided CSRF token against stored token
     */
    public function validateToken(?string $token): bool 
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Refresh the CSRF token
     */
    public function refreshToken(): string 
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    /**
     * Remove the CSRF token
     */
    public function removeToken(): void 
    {
        unset($_SESSION['csrf_token']);
    }
}
