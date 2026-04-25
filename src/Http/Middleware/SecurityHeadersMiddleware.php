<?php
declare(strict_types=1);

namespace App\Http\Middleware;

final class SecurityHeadersMiddleware
{
    public static function apply(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Cache-Control: no-store');
    }
}
