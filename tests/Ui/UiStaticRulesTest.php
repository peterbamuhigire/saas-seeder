<?php

declare(strict_types=1);

namespace Tests\Ui;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use PHPUnit\Framework\TestCase;

final class UiStaticRulesTest extends TestCase
{
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
}
