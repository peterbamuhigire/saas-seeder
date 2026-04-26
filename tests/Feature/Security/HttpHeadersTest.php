<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Http\Security\CorsPolicy;
use App\Http\Security\SecurityHeaderPolicy;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HttpHeadersTest extends TestCase
{
    public function testProductionSecurityPolicyIncludesHsts(): void
    {
        $headers = (new SecurityHeaderPolicy())->headers('production');

        self::assertArrayHasKey('Strict-Transport-Security', $headers);
        self::assertSame('DENY', $headers['X-Frame-Options']);
    }

    public function testProductionCorsRequiresAllowList(): void
    {
        $this->expectException(RuntimeException::class);

        (new CorsPolicy())->resolveOrigin('https://app.example.test', [], 'production');
    }
}
