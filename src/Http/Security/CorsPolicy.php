<?php

declare(strict_types=1);

namespace App\Http\Security;

use RuntimeException;

final class CorsPolicy
{
    /**
     * @param list<string> $allowedOrigins
     */
    public function resolveOrigin(string $origin, array $allowedOrigins, string $appEnv): ?string
    {
        if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
            return $origin;
        }

        if ($appEnv === 'production' && $allowedOrigins === []) {
            throw new RuntimeException('CORS_ALLOWED_ORIGINS must be configured in production.');
        }

        return $appEnv === 'development' ? '*' : null;
    }
}
