<?php
declare(strict_types=1);

namespace App\Auth\Token;

use App\Auth\Security\AuthAuditLogger;
use App\Observability\AuditEvent;
use PDO;

final class RefreshTokenService
{
    public function __construct(
        private readonly PDO $db,
        private readonly AccessTokenService $accessTokens,
        private readonly RefreshTokenRepository $refreshTokens,
        private readonly int $refreshTtlSeconds = 2592000,
        private readonly ?AuthAuditLogger $audit = null
    ) {
    }

    public function issuePair(
        int $userId,
        ?int $franchiseId,
        ?string $deviceId = null,
        ?string $userAgent = null,
        ?string $ipAddress = null
    ): TokenPair {
        return $this->createPair(
            $userId,
            $franchiseId,
            $this->refreshTokens->generateFamilyId(),
            $deviceId,
            $userAgent,
            $ipAddress
        );
    }

    public function rotate(
        string $refreshToken,
        ?string $deviceId = null,
        ?string $userAgent = null,
        ?string $ipAddress = null
    ): TokenPair {
        $current = $this->refreshTokens->findByToken($refreshToken);

        if ($current === null) {
            throw new \RuntimeException('Refresh token is invalid.');
        }

        if ($current->revokedAt !== null || $current->reuseDetectedAt !== null) {
            $this->refreshTokens->markReuseDetected($current);
            $this->refreshTokens->revokeFamily($current->familyId, TokenRevocationReason::REUSE_DETECTED);
            $this->audit?->log(AuditEvent::AUTH_TOKEN_REUSE_DETECTED, $current->userId, $current->franchiseId, [
                'family_id' => $current->familyId,
                'device_id' => $current->deviceId,
            ]);
            throw new \RuntimeException('Refresh token reuse detected.');
        }

        if ($current->expiresAt <= new \DateTimeImmutable()) {
            $this->refreshTokens->revokeTokenId($current->id, TokenRevocationReason::EXPIRED);
            throw new \RuntimeException('Refresh token has expired.');
        }

        $this->db->beginTransaction();
        try {
            $plainRefreshToken = $this->refreshTokens->generateToken();
            $refreshExpiresAt = new \DateTimeImmutable('+' . $this->refreshTtlSeconds . ' seconds');
            $replacement = $this->refreshTokens->create(
                $current->userId,
                $current->franchiseId,
                $plainRefreshToken,
                $current->familyId,
                $deviceId ?? $current->deviceId,
                $userAgent,
                $ipAddress,
                $refreshExpiresAt
            );

            $this->refreshTokens->markReplaced($current->id, $replacement->id);
            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }

        $access = $this->accessTokens->issueTokenWithClaims(
            $current->userId,
            $current->franchiseId,
            $ipAddress,
            $userAgent
        );

        $this->audit?->log(AuditEvent::AUTH_TOKEN_REFRESHED, $current->userId, $current->franchiseId, [
            'family_id' => $current->familyId,
            'device_id' => $deviceId ?? $current->deviceId,
            'issued_via' => 'rotate',
        ]);

        return new TokenPair(
            $access['token'],
            $plainRefreshToken,
            $access['expires_in'],
            $access['claims'],
            $refreshExpiresAt
        );
    }

    public function revoke(string $refreshToken, TokenRevocationReason $reason = TokenRevocationReason::LOGOUT): void
    {
        $current = $this->refreshTokens->findByToken($refreshToken);

        if ($current !== null) {
            $this->refreshTokens->revokeTokenId($current->id, $reason);
            if ($reason === TokenRevocationReason::LOGOUT) {
                $this->audit?->log(AuditEvent::AUTH_LOGOUT, $current->userId, $current->franchiseId, [
                    'reason' => $reason->value,
                    'device_id' => $current->deviceId,
                    'family_id' => $current->familyId,
                ]);
            }
        }
    }

    public function revokeCurrentDevice(string $refreshToken, ?string $accessToken = null): void
    {
        $this->revoke($refreshToken, TokenRevocationReason::LOGOUT);

        if ($accessToken !== null) {
            $this->accessTokens->revokeToken($accessToken);
        }
    }

    public function revokeAllForUser(
        int $userId,
        ?int $franchiseId = null,
        TokenRevocationReason $reason = TokenRevocationReason::LOGOUT_ALL
    ): void {
        $this->refreshTokens->revokeAllForUser($userId, $reason, $franchiseId);

        $stmt = $this->db->prepare(
            'UPDATE tbl_user_sessions
             SET invalidated_at = COALESCE(invalidated_at, NOW())
             WHERE user_id = ? AND (? IS NULL OR franchise_id = ?)'
        );
        $stmt->execute([$userId, $franchiseId, $franchiseId]);
        $this->audit?->log(AuditEvent::AUTH_LOGOUT_ALL, $userId, $franchiseId, [
            'reason' => $reason->value,
        ]);
    }

    private function createPair(
        int $userId,
        ?int $franchiseId,
        string $familyId,
        ?string $deviceId,
        ?string $userAgent,
        ?string $ipAddress
    ): TokenPair {
        $plainRefreshToken = $this->refreshTokens->generateToken();
        $refreshExpiresAt = new \DateTimeImmutable('+' . $this->refreshTtlSeconds . ' seconds');

        $access = $this->accessTokens->issueTokenWithClaims($userId, $franchiseId, $ipAddress, $userAgent);

        $this->refreshTokens->create(
            $userId,
            $franchiseId,
            $plainRefreshToken,
            $familyId,
            $deviceId,
            $userAgent,
            $ipAddress,
            $refreshExpiresAt
        );

        $this->audit?->log(AuditEvent::AUTH_TOKEN_REFRESHED, $userId, $franchiseId, [
            'family_id' => $familyId,
            'device_id' => $deviceId,
            'issued_via' => 'issue_pair',
        ]);

        return new TokenPair(
            $access['token'],
            $plainRefreshToken,
            $access['expires_in'],
            $access['claims'],
            $refreshExpiresAt
        );
    }
}
