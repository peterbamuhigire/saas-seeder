<?php

declare(strict_types=1);

namespace App\Http\Security;

final class EnvironmentGuard
{
    public static function allowsLocalDevelopment(string $appEnv, string $remoteAddress): bool
    {
        return in_array(strtolower(trim($appEnv)), ['development', 'local'], true)
            && in_array($remoteAddress, ['127.0.0.1', '::1'], true);
    }

    public static function denyUnlessLocalDevelopment(string $appEnv, string $remoteAddress): void
    {
        if (self::allowsLocalDevelopment($appEnv, $remoteAddress)) {
            return;
        }

        http_response_code(404);
        exit;
    }
}
