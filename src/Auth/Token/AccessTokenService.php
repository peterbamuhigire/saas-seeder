<?php
declare(strict_types=1);

namespace App\Auth\Token;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;

final class AccessTokenService
{
    private string $secretKey;
    private string $algorithm;
    private string $issuer;
    private string $audience;

    public function __construct(
        private readonly PDO $db,
        ?string $secretKey = null,
        ?string $issuer = null,
        private readonly int $ttlSeconds = 900
    ) {
        $this->secretKey = $secretKey
            ?? $_ENV['JWT_SECRET_KEY']
            ?? $_SERVER['JWT_SECRET_KEY']
            ?? '';

        if ($this->secretKey === '') {
            throw new \RuntimeException('JWT_SECRET_KEY is not set.');
        }

        $this->algorithm = 'HS256';
        $this->issuer = $issuer ?? $_ENV['APP_URL'] ?? $_SERVER['APP_URL'] ?? 'saas-seeder';
        $this->audience = $this->issuer;
    }

    public function issueToken(
        int $userId,
        ?int $franchiseId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        bool $persistSession = true
    ): string {
        return $this->issueTokenWithClaims($userId, $franchiseId, $ipAddress, $userAgent, $persistSession)['token'];
    }

    /**
     * @return array{token: string, claims: TokenClaims, expires_in: int}
     */
    public function issueTokenWithClaims(
        int $userId,
        ?int $franchiseId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        bool $persistSession = true
    ): array {
        $issuedAt = time();
        $claims = new TokenClaims(
            $this->issuer,
            $this->audience,
            $issuedAt,
            $issuedAt + $this->ttlSeconds,
            $userId,
            $franchiseId,
            bin2hex(random_bytes(16)),
            $this->getPermissionVersion($franchiseId)
        );

        $token = JWT::encode($claims->toArray(), $this->secretKey, $this->algorithm);

        if ($persistSession) {
            $this->storeSession($claims, $ipAddress, $userAgent);
        }

        return [
            'token' => $token,
            'claims' => $claims,
            'expires_in' => $this->ttlSeconds,
        ];
    }

    public function validateToken(string $token): bool
    {
        return $this->validateClaims($token) !== null;
    }

    public function validateClaims(string $token): ?TokenClaims
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            $claims = TokenClaims::fromArray((array) $decoded);

            if ($claims->issuer !== $this->issuer || $claims->audience !== $this->audience) {
                return null;
            }

            if ($claims->expiresAt <= time()) {
                return null;
            }

            if (!$this->isSessionValid($claims->jti)) {
                return null;
            }

            if ($claims->permissionVersion !== $this->getPermissionVersion($claims->franchiseId)) {
                return null;
            }

            return $claims;
        } catch (\Throwable) {
            return null;
        }
    }

    public function getUserIdFromToken(string $token): ?int
    {
        return $this->validateClaims($token)?->userId;
    }

    public function revokeToken(string $token): void
    {
        $claims = $this->decodeClaimsWithoutSessionValidation($token);
        $this->revokeJti($claims->jti);
    }

    public function revokeJti(string $jti): void
    {
        $stmt = $this->db->prepare('CALL sp_invalidate_session(?)');
        $stmt->execute([$jti]);
        $stmt->closeCursor();
    }

    public function decodeClaimsWithoutSessionValidation(string $token): TokenClaims
    {
        $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));

        return TokenClaims::fromArray((array) $decoded);
    }

    private function storeSession(TokenClaims $claims, ?string $ipAddress, ?string $userAgent): void
    {
        $stmt = $this->db->prepare('CALL sp_create_user_session(?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $claims->userId,
            $claims->franchiseId,
            $claims->jti,
            $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? '',
            $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? '',
            date('Y-m-d H:i:s', $claims->expiresAt),
            0,
            json_encode([
                'jti' => $claims->jti,
                'perm_version' => $claims->permissionVersion,
                'created_at' => date('Y-m-d H:i:s', $claims->issuedAt),
            ], JSON_THROW_ON_ERROR),
        ]);
        $stmt->closeCursor();
    }

    private function isSessionValid(string $jti): bool
    {
        $stmt = $this->db->prepare('CALL sp_validate_session(?)');
        $stmt->execute([$jti]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result !== false && (int) ($result['is_valid'] ?? 0) === 1;
    }

    private function getPermissionVersion(?int $franchiseId): int
    {
        if ($franchiseId === null) {
            return 0;
        }

        $stmt = $this->db->prepare('SELECT permission_version FROM tbl_franchises WHERE id = ? LIMIT 1');
        $stmt->execute([$franchiseId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int) ($row['permission_version'] ?? 0) : 0;
    }
}
