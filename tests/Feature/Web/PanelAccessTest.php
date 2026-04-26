<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use PHPUnit\Framework\TestCase;

final class PanelAccessTest extends TestCase
{
    public function testPanelPagesExposeMainBodyLandmark(): void
    {
        foreach (['public/dashboard.php', 'public/adminpanel/index.php', 'public/memberpanel/index.php'] as $path) {
            $contents = file_get_contents(dirname(__DIR__, 3) . '/' . $path);
            self::assertIsString($contents);
            self::assertStringContainsString('id="main-body"', $contents, $path);
        }
    }
}
