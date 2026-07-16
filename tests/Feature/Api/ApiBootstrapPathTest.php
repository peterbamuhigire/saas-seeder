<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use PHPUnit\Framework\TestCase;

final class ApiBootstrapPathTest extends TestCase
{
    public function testEveryApiEndpointReferencesTheApiBootstrap(): void
    {
        $root = dirname(__DIR__, 3);
        $paths = [
            'api/v1/auth/login.php' => '/../../bootstrap.php',
            'api/v1/auth/logout.php' => '/../../bootstrap.php',
            'api/v1/auth/logout-all.php' => '/../../bootstrap.php',
            'api/v1/auth/refresh.php' => '/../../bootstrap.php',
            'api/v1/public/auth/register.php' => '/../../../bootstrap.php',
        ];

        foreach ($paths as $endpoint => $relativeBootstrap) {
            $endpointPath = $root . '/' . $endpoint;
            $contents = file_get_contents($endpointPath);
            self::assertIsString($contents);
            self::assertStringContainsString("__DIR__ . '{$relativeBootstrap}'", $contents, $endpoint);
            self::assertFileExists(dirname($endpointPath) . $relativeBootstrap, $endpoint);
        }
    }

    public function testDevelopmentRouterResolvesExtensionlessApiRoutes(): void
    {
        $router = file_get_contents(dirname(__DIR__, 3) . '/scripts/server/router.php');

        self::assertIsString($router);
        self::assertStringContainsString("\$relativePath . '.php'", $router);
    }
}
