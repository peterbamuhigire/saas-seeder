<?php
declare(strict_types=1);

namespace App\Config;

final readonly class AppConfig
{
    public function __construct(
        public string $environment,
        public string $appKey,
        public string $timezone,
        public string $corsAllowedOrigins
    ) {
    }

    public static function fromEnvironment(): self
    {
        return new self(
            Env::get('APP_ENV', 'development') ?? 'development',
            Env::get('APP_KEY', '') ?? '',
            Env::get('APP_TIMEZONE', 'UTC') ?? 'UTC',
            Env::get('CORS_ALLOWED_ORIGINS', '') ?? ''
        );
    }
}
