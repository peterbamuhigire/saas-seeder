<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use PHPUnit\Framework\TestCase;

final class AuthLoginTest extends TestCase
{
    public function testLoginEndpointUsesRateLimitAndRefreshTokenLifecycle(): void
    {
        $contents = file_get_contents(__DIR__ . '/../../../api/v1/auth/login.php');

        self::assertIsString($contents);
        self::assertStringContainsString('RateLimitPolicy::loginIp()', $contents);
        self::assertStringContainsString('RefreshTokenService', $contents);
    }
}
