<?php
declare(strict_types=1);

namespace App\Http\Middleware;

final class CorsMiddleware
{
    /**
     * @param list<string> $allowedOrigins
     */
    public static function apply(array $allowedOrigins, string $appEnv = 'development'): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
            header('Vary: Origin');
        } elseif ($appEnv === 'development') {
            header('Access-Control-Allow-Origin: *');
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Request-Id, Idempotency-Key');
        header('Access-Control-Expose-Headers: X-Request-Id, X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset');
        header('Access-Control-Max-Age: 86400');
    }

    public static function handlePreflight(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'OPTIONS') {
            return;
        }

        http_response_code(204);
        exit;
    }
}
