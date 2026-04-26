<?php

declare(strict_types=1);

namespace App\Auth\Services;

final class UserSessionService
{
    /**
     * @param array<string, mixed> $userData
     */
    public function hydrate(array $userData, string $token): void
    {
        if (!defined('SESSION_PREFIX')) {
            require_once dirname(__DIR__, 2) . '/config/session.php';
        }

        setSession('user_id', (int) $userData['id']);
        setSession('franchise_id', $userData['franchise_id']);
        setSession('username', (string) $userData['username']);
        setSession('user_type', (string) ($userData['user_type'] ?? 'staff'));
        setSession('full_name', trim((string) $userData['first_name'] . ' ' . (string) $userData['last_name']));
        setSession('role_name', $userData['roles'][0] ?? 'User');
        setSession('auth_token', $token);
        setSession('last_activity', time());
        setSession('franchise_name', (string) ($userData['franchise_name'] ?? ''));
        setSession('currency', (string) ($userData['currency'] ?? ''));
        setSession('franchise_code', (string) ($userData['franchise_code'] ?? ''));
        setSession('franchise_country', (string) ($userData['country'] ?? ''));
        setSession('franchise_language', (string) ($userData['language'] ?? 'en'));
        setSession('language', getSession('franchise_language'));
        setSession('timezone', (string) ($userData['timezone'] ?? 'Africa/Kampala'));
        setSession('force_password_change', $userData['force_password_change'] ?? 0);

        date_default_timezone_set((string) getSession('timezone'));
    }
}
