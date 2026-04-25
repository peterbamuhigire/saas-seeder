<?php
declare(strict_types=1);

namespace App\Auth\Token;

final class TokenFamily
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly ?int $franchiseId,
        public readonly string $tokenHash,
        public readonly string $familyId,
        public readonly ?string $deviceId,
        public readonly ?string $userAgentHash,
        public readonly ?string $ipAddress,
        public readonly \DateTimeImmutable $expiresAt,
        public readonly ?\DateTimeImmutable $revokedAt,
        public readonly ?int $replacedByTokenId,
        public readonly ?\DateTimeImmutable $reuseDetectedAt,
        public readonly \DateTimeImmutable $createdAt
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromDatabase(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['user_id'],
            isset($row['franchise_id']) ? (int) $row['franchise_id'] : null,
            (string) $row['token_hash'],
            (string) $row['family_id'],
            $row['device_id'] !== null ? (string) $row['device_id'] : null,
            $row['user_agent_hash'] !== null ? (string) $row['user_agent_hash'] : null,
            $row['ip_address'] !== null ? (string) $row['ip_address'] : null,
            new \DateTimeImmutable((string) $row['expires_at']),
            $row['revoked_at'] !== null ? new \DateTimeImmutable((string) $row['revoked_at']) : null,
            $row['replaced_by_token_id'] !== null ? (int) $row['replaced_by_token_id'] : null,
            $row['reuse_detected_at'] !== null ? new \DateTimeImmutable((string) $row['reuse_detected_at']) : null,
            new \DateTimeImmutable((string) $row['created_at'])
        );
    }

    public function isActive(?\DateTimeImmutable $now = null): bool
    {
        $now ??= new \DateTimeImmutable();

        return $this->revokedAt === null
            && $this->reuseDetectedAt === null
            && $this->expiresAt > $now;
    }
}
