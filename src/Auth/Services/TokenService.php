<?php

declare(strict_types=1);

namespace App\Auth\Services;

use App\Auth\Interfaces\TokenServiceInterface;
use App\Auth\Token\AccessTokenService;
use PDO;

final class TokenService implements TokenServiceInterface
{
    private AccessTokenService $accessTokens;

    public function __construct(PDO $db)
    {
        $this->accessTokens = new AccessTokenService($db);
    }

    public function generateToken(int $userId, int $franchiseId): string
    {
        return $this->accessTokens->issueToken(
            $userId,
            $franchiseId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );
    }

    public function validateToken(string $token): bool
    {
        return $this->accessTokens->validateToken($token);
    }

    public function refreshToken(string $currentToken): string
    {
        $claims = $this->accessTokens->validateClaims($currentToken);

        if ($claims === null) {
            throw new \Exception('Invalid token for refresh');
        }

        return $this->generateToken($claims->userId, $claims->franchiseId ?? 1);
    }

    public function getUserIdFromToken(string $token): ?int
    {
        return $this->accessTokens->getUserIdFromToken($token);
    }

    public function getCurrentToken(): ?string
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!is_string($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }

        return $matches[1];
    }

    public function invalidateToken(string $token): void
    {
        try {
            $this->accessTokens->revokeToken($token);
        } catch (\Throwable) {
            throw new \Exception('Invalid token for invalidation');
        }
    }
}
