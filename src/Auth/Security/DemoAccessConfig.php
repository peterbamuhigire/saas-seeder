<?php

declare(strict_types=1);

namespace App\Auth\Security;

use App\Config\Env;

final readonly class DemoAccessConfig
{
    public function __construct(
        private bool $requested,
        private string $environment,
        private string $username,
        private string $password
    ) {
    }

    public static function fromEnvironment(): self
    {
        return new self(
            filter_var(Env::get('DEMO_MODE', 'false'), FILTER_VALIDATE_BOOL),
            Env::get('APP_ENV', 'development') ?? 'development',
            Env::get('DEMO_SUPER_ADMIN_USERNAME', '') ?? '',
            Env::get('DEMO_SUPER_ADMIN_PASSWORD', '') ?? ''
        );
    }

    public function isAvailable(): bool
    {
        return $this->requested
            && strtolower(trim($this->environment)) !== 'production'
            && trim($this->username) !== ''
            && $this->password !== '';
    }

    public function username(): string
    {
        return $this->username;
    }

    public function password(): string
    {
        return $this->password;
    }
}
