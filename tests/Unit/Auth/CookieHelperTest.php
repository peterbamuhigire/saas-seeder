<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\Helpers\CookieHelper;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class CookieHelperTest extends TestCase
{
    public function testEncryptedCookieValueRoundTripsAndTamperingFails(): void
    {
        $_ENV['COOKIE_ENCRYPTION_KEY'] = str_repeat('b', 32);
        $helper = new CookieHelper();

        $encrypt = new ReflectionMethod($helper, 'encryptValue');
        $decrypt = new ReflectionMethod($helper, 'decryptValue');
        $encrypt->setAccessible(true);
        $decrypt->setAccessible(true);

        $encrypted = $encrypt->invoke($helper, 'secret');

        self::assertSame('secret', $decrypt->invoke($helper, $encrypted));
        self::assertNull($decrypt->invoke($helper, substr($encrypted, 0, -2) . 'xx'));
    }
}
