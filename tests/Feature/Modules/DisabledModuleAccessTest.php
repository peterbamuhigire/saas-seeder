<?php

declare(strict_types=1);

namespace Tests\Feature\Modules;

use App\Modules\ModuleAccessService;
use PDO;
use PHPUnit\Framework\TestCase;

final class DisabledModuleAccessTest extends TestCase
{
    public function testRouteAndNavigationCanUseSameDisabledModuleDecision(): void
    {
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('CREATE TABLE tbl_modules (code TEXT PRIMARY KEY, name TEXT, version TEXT DEFAULT "1.0.0", is_core INTEGER, status TEXT, config TEXT NULL)');
        $db->exec('CREATE TABLE tbl_franchise_modules (id INTEGER PRIMARY KEY AUTOINCREMENT, franchise_id INTEGER, module_code TEXT, status TEXT)');
        $db->exec("INSERT INTO tbl_modules (code, name, is_core, status) VALUES ('REPORTS', 'Reports', 0, 'active')");
        $db->exec("INSERT INTO tbl_franchise_modules (franchise_id, module_code, status) VALUES (10, 'REPORTS', 'disabled')");

        $access = new ModuleAccessService($db);

        self::assertFalse($access->hasAccess(10, 'REPORTS', 'owner'));
    }
}
