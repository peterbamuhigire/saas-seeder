<?php

declare(strict_types=1);

namespace Tests\Database;

use PHPUnit\Framework\TestCase;

final class MigrationTest extends TestCase
{
    public function testGovernedMigrationFilesExist(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'database/migrations/0001_platform_base.sql',
            'database/migrations/0002_module_registry.sql',
            'database/migrations/0003_api_token_lifecycle.sql',
            'database/migrations/0004_rate_limits.sql',
            'database/schema/checks.sql',
        ] as $path) {
            self::assertFileExists($root . '/' . $path);
        }
    }
}
