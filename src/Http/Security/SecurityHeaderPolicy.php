<?php

declare(strict_types=1);

namespace App\Http\Security;

final class SecurityHeaderPolicy
{
    /**
     * @return array<string, string>
     */
    public function headers(string $appEnv = 'development'): array
    {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Cache-Control' => 'no-store',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
            'Content-Security-Policy-Report-Only' => (new CspPolicy())->reportOnlyHeader(),
        ];

        if ($appEnv === 'production') {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }

        return $headers;
    }
}
