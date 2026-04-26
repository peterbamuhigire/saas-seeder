<?php

declare(strict_types=1);

namespace App\UI\Navigation;

final class MenuRegistry
{
    /**
     * @return list<MenuItem>
     */
    public static function defaults(): array
    {
        return [
            new MenuItem('Dashboard', '/dashboard.php', 'admin', 'layout-dashboard', 'VIEW_DASHBOARD', 'DASHBOARD'),
            new MenuItem('Users', '/users.php', 'admin', 'users', 'VIEW_USERS', 'RBAC'),
            new MenuItem('Settings', '/settings.php', 'admin', 'settings', 'MANAGE_SETTINGS', 'TENANT'),
            new MenuItem('My dashboard', '/memberpanel/', 'member', 'user', null, 'DASHBOARD'),
        ];
    }
}
