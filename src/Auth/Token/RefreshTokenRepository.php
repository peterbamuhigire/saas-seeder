<?php
declare(strict_types=1);

namespace App\Auth\Token;

use PDO;

final class RefreshTokenRepository
{
    private string $hashKey;

    public function __construct(private readonly PDO $db, ?string $hashKey = null)
    {
        $this->hashKey = $hashKey
            ?? $_ENV['REFRESH_TOKEN_HASH_KEY']
            ?? $_SERVER['REFRESH_TOKEN_HASH_KEY']
            ?? $_ENV['JWT_SECRET_KEY']
            ?? $_SERVER['JWT_SECRET_KEY']
            ?? '';

        if ($this->hashKey === '') {
            throw new \RuntimeException('REFRESH_TOKEN_HASH_KEY or JWT_SECRET_KEY is required.');
        }
    }

    public function generateToken(): string
    {
        return $this->base64UrlEncode(random_bytes(32));
    }

    public function generateFamilyId(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function hashToken(string $refreshToken): string
    {
        return hash_hmac('sha256', $refreshToken, $this->hashKey);
    }

    public function create(
        int $userId,
        ?int $franchiseId,
        string $refreshToken,
        string $familyId,
        ?string $deviceId,
        ?string $userAgent,
        ?string $ipAddress,
        \DateTimeImmutable $expiresAt
    ): TokenFamily {
        $stmt = $this->db->prepare(
            'INSERT INTO tbl_refresh_tokens (
                user_id, franchise_id, token_hash, family_id, device_id,
                user_agent_hash, ip_address, expires_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $franchiseId,
            $this->hashToken($refreshToken),
            $familyId,
            $deviceId,
            $userAgent !== null ? hash('sha256', $userAgent) : null,
            $ipAddress,
            $expiresAt->format('Y-m-d H:i:s'),
        ]);

        return $this->findById((int) $this->db->lastInsertId());
    }

    public function findByToken(string $refreshToken): ?TokenFamily
    {
        $stmt = $this->db->prepare('SELECT * FROM tbl_refresh_tokens WHERE token_hash = ? LIMIT 1');
        $stmt->execute([$this->hashToken($refreshToken)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? TokenFamily::fromDatabase($row) : null;
    }

    public function findById(int $id): TokenFamily
    {
        $stmt = $this->db->prepare('SELECT * FROM tbl_refresh_tokens WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new \RuntimeException('Refresh token row was not found.');
        }

        return TokenFamily::fromDatabase($row);
    }

    public function markReplaced(int $tokenId, int $replacementTokenId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE tbl_refresh_tokens
             SET revoked_at = COALESCE(revoked_at, NOW()), replaced_by_token_id = ?
             WHERE id = ?'
        );
        $stmt->execute([$replacementTokenId, $tokenId]);
    }

    public function revokeTokenId(int $tokenId, TokenRevocationReason $reason): void
    {
        $stmt = $this->db->prepare(
            'UPDATE tbl_refresh_tokens
             SET revoked_at = COALESCE(revoked_at, NOW())
             WHERE id = ?'
        );
        $stmt->execute([$tokenId]);
    }

    public function revokeFamily(string $familyId, TokenRevocationReason $reason): void
    {
        $stmt = $this->db->prepare(
            'UPDATE tbl_refresh_tokens
             SET revoked_at = COALESCE(revoked_at, NOW())
             WHERE family_id = ?'
        );
        $stmt->execute([$familyId]);
    }

    public function revokeAllForUser(int $userId, TokenRevocationReason $reason, ?int $franchiseId = null): void
    {
        $sql = 'UPDATE tbl_refresh_tokens SET revoked_at = COALESCE(revoked_at, NOW()) WHERE user_id = ?';
        $params = [$userId];

        if ($franchiseId !== null) {
            $sql .= ' AND franchise_id = ?';
            $params[] = $franchiseId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function markReuseDetected(TokenFamily $token): void
    {
        $stmt = $this->db->prepare(
            'UPDATE tbl_refresh_tokens
             SET reuse_detected_at = COALESCE(reuse_detected_at, NOW())
             WHERE id = ?'
        );
        $stmt->execute([$token->id]);
    }

    private function base64UrlEncode(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
