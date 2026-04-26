<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Security\SecurityHeaderPolicy;

final class SecurityHeadersMiddleware
{
    public static function apply(): void
    {
        foreach ((new SecurityHeaderPolicy())->headers($_ENV['APP_ENV'] ?? 'development') as $name => $value) {
            header($name . ': ' . $value);
        }
    }
}
