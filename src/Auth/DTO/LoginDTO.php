<?php
namespace App\Auth\DTO;

class LoginDTO
{
    private string $username;
    private string $password;
    private ?string $ipAddress;
    private ?string $userAgent;

    public function __construct(
        string $username,
        string $password,
        ?string $ipAddress,
        ?string $userAgent = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
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
