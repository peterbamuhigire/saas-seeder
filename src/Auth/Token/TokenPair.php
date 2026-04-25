<?php
declare(strict_types=1);

namespace App\Auth\Token;

final class TokenPair
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken,
        public readonly int $expiresIn,
        public readonly TokenClaims $claims,
        public readonly \DateTimeImmutable $refreshExpiresAt,
        public readonly string $tokenType = 'Bearer'
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'refresh_expires_at' => $this->refreshExpiresAt->format(DATE_ATOM),
        ];
    }
}
