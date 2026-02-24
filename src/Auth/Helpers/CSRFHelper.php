<?php
namespace App\Auth\Helpers;

class CSRFHelper
{
    /**
     * Generate a new CSRF token or return existing one
     */
    public function generateToken(): string
    {
        if (!hasSession('csrf_token')) {
            setSession('csrf_token', bin2hex(random_bytes(32)));
        }
        return getSession('csrf_token');
    }

    /**
     * Validate provided CSRF token against stored token
     */
    public function validateToken(?string $token): bool
    {
        if (empty($token) || !hasSession('csrf_token')) {
            throw new \Exception('Invalid or missing security token. Please refresh and try again.');
        }

        if (!hash_equals(getSession('csrf_token'), $token)) {
            throw new \Exception('Security token mismatch. Please refresh and try again.');
        }

        return true;
    }

    /**
     * Refresh the CSRF token (call after successful form submission)
     */
    public function refreshToken(): string
    {
        setSession('csrf_token', bin2hex(random_bytes(32)));
        return getSession('csrf_token');
    }

    /**
     * Remove the CSRF token
     */
    public function removeToken(): void
    {
        unsetSession('csrf_token');
    }
}
