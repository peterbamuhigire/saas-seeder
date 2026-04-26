<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\Services\PermissionService;
use PDO;
use PHPUnit\Framework\TestCase;

final class PermissionServiceTest extends TestCase
{
    public function testPermissionServiceReturnsFalseWhenProcedureIsUnavailable(): void
    {
        $db = new PDO('sqlite::memory:');
        $db->exec('CREATE TABLE tbl_permissions (id INTEGER PRIMARY KEY, code TEXT)');
        $db->exec('CREATE TABLE tbl_user_permissions (user_id INTEGER, franchise_id INTEGER, permission_id INTEGER, allowed INTEGER)');
        $db->exec('CREATE TABLE tbl_user_roles (user_id INTEGER, franchise_id INTEGER, global_role_id INTEGER)');
        $db->exec('CREATE TABLE tbl_franchise_role_overrides (franchise_id INTEGER, global_role_id INTEGER, permission_id INTEGER, is_enabled INTEGER)');
        $db->exec('CREATE TABLE tbl_global_role_permissions (global_role_id INTEGER, permission_id INTEGER)');
        $service = new PermissionService($db);

        self::assertFalse($service->hasPermission(1, 1, 'VIEW_DASHBOARD'));
    }
}
