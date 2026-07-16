<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Http\Security\CorsPolicy;
use App\Http\Security\EnvironmentGuard;
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

    public function testContentSecurityPolicyIsEnforcedWithoutInlineScripts(): void
    {
        $headers = (new SecurityHeaderPolicy())->headers('production');

        self::assertArrayHasKey('Content-Security-Policy', $headers);
        self::assertArrayNotHasKey('Content-Security-Policy-Report-Only', $headers);
        self::assertStringContainsString("script-src 'self'", $headers['Content-Security-Policy']);
        self::assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $headers['Content-Security-Policy']);
    }

    public function testDevelopmentToolsRequireBothDevelopmentModeAndLoopback(): void
    {
        self::assertTrue(EnvironmentGuard::allowsLocalDevelopment('development', '127.0.0.1'));
        self::assertTrue(EnvironmentGuard::allowsLocalDevelopment('local', '::1'));
        self::assertFalse(EnvironmentGuard::allowsLocalDevelopment('production', '127.0.0.1'));
        self::assertFalse(EnvironmentGuard::allowsLocalDevelopment('development', '192.0.2.10'));
    }

    public function testSecurityContactIsPublished(): void
    {
        $path = dirname(__DIR__, 3) . '/public/.well-known/security.txt';
        $contents = file_get_contents($path);

        self::assertIsString($contents);
        self::assertStringContainsString('Contact: https://', $contents);
        self::assertStringContainsString('Expires:', $contents);
    }
}
