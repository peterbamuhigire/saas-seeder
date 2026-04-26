<?php

declare(strict_types=1);

namespace App\Http\RateLimit;

final readonly class RateLimitPolicy
{
    public function __construct(
        public string $name,
        public int $limit,
        public int $windowSeconds
    ) {
    }

    public static function loginIp(): self
    {
        return new self('login.ip', 5, 60);
    }

    public static function loginIdentity(): self
    {
        return new self('login.identity', 10, 3600);
    }

    public static function refresh(): self
    {
        return new self('refresh', 30, 60);
    }

    public static function logout(): self
    {
        return new self('logout', 30, 60);
    }

    public static function registerIp(): self
    {
        return new self('register.ip', 3, 3600);
    }

    public static function registerIdentity(): self
    {
        return new self('register.identity', 5, 86400);
    }

    public static function authenticatedApi(): self
    {
        return new self('api.authenticated', 100, 60);
    }
}
