<?php

declare(strict_types=1);

namespace Tests\Unit\Modules;

use App\Modules\ModuleAccessService;
use PDO;
use PHPUnit\Framework\TestCase;

final class ModuleAccessServiceTest extends TestCase
{
    public function testCoreModuleIsAvailableToTenant(): void
    {
        $service = new ModuleAccessService($this->db());

        self::assertTrue($service->hasAccess(1, 'AUTH', 'owner'));
    }

    public function testDisabledTenantModuleIsDenied(): void
    {
        $db = $this->db();
        $db->exec("INSERT INTO tbl_modules (code, name, is_core, status) VALUES ('BILLING', 'Billing', 0, 'active')");
        $db->exec("INSERT INTO tbl_franchise_modules (franchise_id, module_code, status) VALUES (1, 'BILLING', 'disabled')");

        $service = new ModuleAccessService($db);

        self::assertFalse($service->hasAccess(1, 'BILLING', 'owner'));
    }

    public function testSuperAdminBypassesTenantModuleState(): void
    {
        $service = new ModuleAccessService($this->db());

        self::assertTrue($service->hasAccess(null, 'MISSING', 'super_admin'));
    }

    private function db(): PDO
    {
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('CREATE TABLE tbl_modules (code TEXT PRIMARY KEY, name TEXT, version TEXT DEFAULT "1.0.0", is_core INTEGER, status TEXT, config TEXT NULL)');
        $db->exec('CREATE TABLE tbl_franchise_modules (id INTEGER PRIMARY KEY AUTOINCREMENT, franchise_id INTEGER, module_code TEXT, status TEXT)');
        $db->exec("INSERT INTO tbl_modules (code, name, is_core, status) VALUES ('AUTH', 'Authentication', 1, 'active')");

        return $db;
    }
}
