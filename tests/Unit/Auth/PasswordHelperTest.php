<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\Helpers\PasswordHelper;
use PHPUnit\Framework\TestCase;

final class PasswordHelperTest extends TestCase
{
    public function testHashAndVerifyPasswordWithPepper(): void
    {
        $_ENV['PASSWORD_PEPPER'] = 'unit-test-pepper';
        $helper = new PasswordHelper();

        $hash = $helper->hashPassword('Correct Horse 1!');

        self::assertTrue($helper->verifyPassword('Correct Horse 1!', $hash));
        self::assertFalse($helper->verifyPassword('wrong', $hash));
    }
}
