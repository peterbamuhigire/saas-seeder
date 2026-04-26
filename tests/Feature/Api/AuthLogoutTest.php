<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use PHPUnit\Framework\TestCase;

final class AuthLogoutTest extends TestCase
{
    public function testLogoutEndpointsRevokeTokens(): void
    {
        $logout = file_get_contents(__DIR__ . '/../../../api/v1/auth/logout.php');
        $logoutAll = file_get_contents(__DIR__ . '/../../../api/v1/auth/logout-all.php');

        self::assertIsString($logout);
        self::assertIsString($logoutAll);
        self::assertStringContainsString('revokeCurrentDevice', $logout);
        self::assertStringContainsString('revokeAllForUser', $logoutAll);
    }
}
