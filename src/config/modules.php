<?php
declare(strict_types=1);

return [
    'core' => ['AUTH', 'RBAC', 'TENANT', 'DASHBOARD'],
    'navigation' => [
        ['label' => 'Dashboard', 'href' => '/dashboard.php', 'module' => 'DASHBOARD', 'permission' => 'VIEW_DASHBOARD'],
        ['label' => 'Users', 'href' => '/users.php', 'module' => 'RBAC', 'permission' => 'VIEW_USERS'],
        ['label' => 'Settings', 'href' => '/settings.php', 'module' => 'TENANT', 'permission' => 'MANAGE_SETTINGS'],
    ],
];
