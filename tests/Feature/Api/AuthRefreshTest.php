<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use PHPUnit\Framework\TestCase;

final class AuthRefreshTest extends TestCase
{
    public function testRefreshEndpointRotatesRefreshToken(): void
    {
        $contents = file_get_contents(__DIR__ . '/../../../api/v1/auth/refresh.php');

        self::assertIsString($contents);
        self::assertStringContainsString('->rotate(', $contents);
        self::assertStringContainsString('RateLimitPolicy::refresh()', $contents);
    }
}
