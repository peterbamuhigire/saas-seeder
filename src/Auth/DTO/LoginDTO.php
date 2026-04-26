<?php

declare(strict_types=1);

namespace App\Auth\DTO;

final readonly class LoginDTO
{
    public function __construct(
        private string $username,
        private string $password,
        private ?string $ipAddress,
        private ?string $userAgent = null
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
}
