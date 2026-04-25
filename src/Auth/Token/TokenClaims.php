<?php
declare(strict_types=1);

namespace App\Auth\Token;

final class TokenClaims
{
    public function __construct(
        public readonly string $issuer,
        public readonly string $audience,
        public readonly int $issuedAt,
        public readonly int $expiresAt,
        public readonly int $userId,
        public readonly ?int $franchiseId,
        public readonly string $jti,
        public readonly int $permissionVersion
    ) {
    }

    /**
     * @param array<string, mixed> $claims
     */
    public static function fromArray(array $claims): self
    {
        $userId = $claims['sub'] ?? $claims['user_id'] ?? null;

        if ($userId === null || !isset($claims['iss'], $claims['aud'], $claims['iat'], $claims['exp'], $claims['jti'])) {
            throw new \InvalidArgumentException('Token claims are missing required fields.');
        }

        return new self(
            (string) $claims['iss'],
            is_array($claims['aud']) ? (string) reset($claims['aud']) : (string) $claims['aud'],
            (int) $claims['iat'],
            (int) $claims['exp'],
            (int) $userId,
            isset($claims['franchise_id']) ? (int) $claims['franchise_id'] : null,
            (string) $claims['jti'],
            (int) ($claims['pv'] ?? $claims['permission_version'] ?? 0)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $this->issuedAt,
            'exp' => $this->expiresAt,
            'sub' => (string) $this->userId,
            'user_id' => $this->userId,
            'franchise_id' => $this->franchiseId,
            'jti' => $this->jti,
            'pv' => $this->permissionVersion,
        ];
    }
}
