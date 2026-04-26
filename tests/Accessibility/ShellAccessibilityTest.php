<?php

declare(strict_types=1);

namespace Tests\Accessibility;

use PHPUnit\Framework\TestCase;

final class ShellAccessibilityTest extends TestCase
{
    public function testTopbarSkipLinkTargetsMainBody(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/public/includes/topbar.php');

        self::assertIsString($contents);
        self::assertStringContainsString('href="#main-body"', $contents);
    }
}
