<?php

declare(strict_types=1);

namespace App\Auth\DTO;

/**
 * Data Transfer Object for authentication results
 */
final readonly class AuthResult
{
    /**
     * @param array<string, mixed> $userData
     */
    public function __construct(
        private int $userId,
        private ?int $franchiseId,
        private string $username,
        private string $status,
        private array $userData = [],
        private ?string $token = null,
        private ?string $message = null
    ) {
    }

    /**
     * Check if authentication was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'SUCCESS';
    }

    /**
     * Get authenticated user ID
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Get franchise ID
     */
    public function getFranchiseId(): ?int
    {
        return $this->franchiseId;
    }

    /**
     * Get username
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Get authentication status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get user data array
     */
    public function getUserData(): array
    {
        return $this->userData;
    }

    /**
     * Get authentication token
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Get error message if any
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Convert to array representation
     */
    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'franchiseId' => $this->franchiseId,
            'username' => $this->username,
            'status' => $this->status,
            'userData' => $this->userData,
            'token' => $this->token,
            'message' => $this->message,
        ];
    }
}
