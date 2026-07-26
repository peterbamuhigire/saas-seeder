<?php

declare(strict_types=1);

namespace Tests\Ui;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use PHPUnit\Framework\TestCase;

final class UiStaticRulesTest extends TestCase
{
    private const AUTH_PAGES = [
        'sign-in.php',
        'sign-up.php',
        'forgot-password.php',
        'change-password.php',
        'super-user-dev.php',
    ];

    public function testPublicPhpFilesDoNotUsePlaceholderLinks(): void
    {
        $root = dirname(__DIR__, 2) . '/public';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            self::assertStringNotContainsString('href="#"', $contents, $file->getPathname());
        }
    }

    public function testAuthPagesUseSharedSemanticShellWithoutInlineScripts(): void
    {
        $root = dirname(__DIR__, 2) . '/public';

        foreach (self::AUTH_PAGES as $page) {
            $contents = file_get_contents($root . '/' . $page);
            self::assertIsString($contents);
            self::assertStringContainsString('auth-page-start.php', $contents, $page);
            self::assertStringContainsString('auth-page-end.php', $contents, $page);
            self::assertStringNotContainsString('<script>', $contents, $page);
            self::assertStringNotContainsString('<style>', $contents, $page);
            self::assertStringNotContainsString('logo-light.png', $contents, $page);
        }
    }

    public function testAuthDesignSystemUsesApprovedSelfHostedFontsAndAccessibleTargets(): void
    {
        $root = dirname(__DIR__, 2) . '/public';
        $css = file_get_contents($root . '/assets/css/auth.css');

        self::assertIsString($css);
        self::assertStringContainsString('Bricolage Grotesque', $css);
        self::assertStringContainsString('Hanken Grotesk', $css);
        self::assertStringContainsString('format("woff2-variations")', $css);
        self::assertStringContainsString('min-height: 44px', $css);
        self::assertStringNotContainsString('Inter', $css);
        self::assertFileExists($root . '/assets/fonts/bricolage-grotesque/OFL.txt');
        self::assertFileExists($root . '/assets/fonts/hanken-grotesk/OFL.txt');
    }

    public function testCapabilityPagesDoNotOfferFakeSubmissionForms(): void
    {
        $root = dirname(__DIR__, 2) . '/public';

        foreach (['sign-up.php', 'forgot-password.php'] as $page) {
            $contents = file_get_contents($root . '/' . $page);
            self::assertIsString($contents);
            self::assertStringNotContainsString('<form', $contents, $page);
            self::assertStringContainsString('disabled', strtolower($contents), $page);
        }
    }

    public function testDemoLoginIsCsrfProtectedWithoutRenderingCredentials(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/public/sign-in.php');

        self::assertIsString($contents);
        self::assertStringContainsString('name="login_mode" value="demo"', $contents);
        self::assertStringContainsString('name="csrf_token"', $contents);
        self::assertStringContainsString('Explore the super-admin demo', $contents);
        self::assertStringNotContainsString('DEMO_SUPER_ADMIN_PASSWORD', $contents);
    }
}
