<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Security\SecurityHeaderPolicy;

final class SecurityHeadersMiddleware
{
    public static function apply(?string $appEnv = null): void
    {
        $environment = $appEnv ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'development';

        foreach ((new SecurityHeaderPolicy())->headers($environment) as $name => $value) {
            header($name . ': ' . $value);
        }
    }
}
