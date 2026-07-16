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

    public function testLandingPageProvidesTheSkipLinkTarget(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/public/index.php');

        self::assertIsString($contents);
        self::assertStringContainsString('id="main-body"', $contents);
        self::assertStringContainsString('tabindex="-1"', $contents);
    }

    public function testAuthShellHasOneMainHeadingAndSkipTarget(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/public/includes/auth-page-start.php');

        self::assertIsString($contents);
        self::assertStringContainsString('href="#main-content"', $contents);
        self::assertStringContainsString('<main class="auth-main" id="main-content">', $contents);
        self::assertStringContainsString('<h1 id="page-heading">', $contents);
    }
}
