<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\Security\DemoAccessConfig;
use PHPUnit\Framework\TestCase;

final class DemoAccessConfigTest extends TestCase
{
    public function testDemoAccessRequiresExplicitCompleteConfiguration(): void
    {
        self::assertFalse((new DemoAccessConfig(false, 'demo', 'admin', 'secret'))->isAvailable());
        self::assertFalse((new DemoAccessConfig(true, 'demo', '', 'secret'))->isAvailable());
        self::assertFalse((new DemoAccessConfig(true, 'demo', 'admin', ''))->isAvailable());
        self::assertTrue((new DemoAccessConfig(true, 'demo', 'admin', 'secret'))->isAvailable());
    }

    public function testDemoAccessIsAlwaysDisabledInProduction(): void
    {
        $config = new DemoAccessConfig(true, ' PRODUCTION ', 'admin', 'secret');

        self::assertFalse($config->isAvailable());
    }

    public function testCredentialsRemainAvailableToServerSideAuthentication(): void
    {
        $config = new DemoAccessConfig(true, 'demo', 'demo-admin', 'secret');

        self::assertSame('demo-admin', $config->username());
        self::assertSame('secret', $config->password());
    }
}
