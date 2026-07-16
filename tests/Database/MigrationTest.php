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
            'database/migrations/0005_signup_token_hardening.sql',
            'database/schema/checks.sql',
        ] as $path) {
            self::assertFileExists($root . '/' . $path);
        }
    }

    public function testTokenLifecycleMigrationGuardsSchemaChangesForReruns(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/database/migrations/0003_api_token_lifecycle.sql');

        self::assertIsString($contents);
        self::assertStringContainsString('information_schema.COLUMNS', $contents);
        self::assertStringContainsString('information_schema.STATISTICS', $contents);
        self::assertStringContainsString('IF NOT EXISTS', $contents);
    }

    public function testSignupTokensAreMigratedToHashOnlyStorage(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/database/migrations/0005_signup_token_hardening.sql');

        self::assertIsString($contents);
        self::assertStringContainsString('verify_token_hash', $contents);
        self::assertStringContainsString('DROP COLUMN `verify_token`', $contents);
        self::assertStringContainsString('uk_signup_verify_token_hash', $contents);
    }
}
