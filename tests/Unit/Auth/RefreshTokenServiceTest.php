<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\Token\AccessTokenService;
use App\Auth\Token\RefreshTokenRepository;
use App\Auth\Token\RefreshTokenService;
use App\Auth\Token\TokenPair;
use App\Auth\Token\TokenRevocationReason;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

final class RefreshTokenServiceTest extends TestCase
{
    public function testIssuePairPersistsOnlyRefreshTokenHashAndReturnsTokenPair(): void
    {
        $db = $this->createRefreshTokenDatabase();
        $repository = new RefreshTokenRepository($db, 'refresh-hash-key');
        $service = new RefreshTokenService($db, $this->createAccessTokenService(), $repository, 3600);

        $pair = $service->issuePair(42, null, 'device-1', 'Unit Test Agent', '203.0.113.10');

        self::assertInstanceOf(TokenPair::class, $pair);
        self::assertSame('Bearer', $pair->tokenType);
        self::assertSame(42, $pair->claims->userId);
        self::assertNotSame('', $pair->accessToken);
        self::assertNotSame('', $pair->refreshToken);

        $rows = $db->query('SELECT * FROM tbl_refresh_tokens')->fetchAll(PDO::FETCH_ASSOC);

        self::assertCount(1, $rows);
        self::assertSame($repository->hashToken($pair->refreshToken), $rows[0]['token_hash']);
        self::assertNotSame($pair->refreshToken, $rows[0]['token_hash']);
        self::assertSame(hash('sha256', 'Unit Test Agent'), $rows[0]['user_agent_hash']);
        self::assertSame('203.0.113.10', $rows[0]['ip_address']);
    }

    public function testRotateRevokesCurrentTokenAndCreatesReplacementInSameFamily(): void
    {
        $db = $this->createRefreshTokenDatabase();
        $repository = new RefreshTokenRepository($db, 'refresh-hash-key');
        $service = new RefreshTokenService($db, $this->createAccessTokenService(), $repository, 3600);
        $originalPair = $service->issuePair(42, null, 'device-1', 'Original Agent', '203.0.113.10');
        $originalFamily = $repository->findByToken($originalPair->refreshToken);

        self::assertNotNull($originalFamily);

        $rotatedPair = $service->rotate($originalPair->refreshToken, null, 'Rotated Agent', '203.0.113.11');

        self::assertNotSame($originalPair->refreshToken, $rotatedPair->refreshToken);

        $current = $repository->findByToken($originalPair->refreshToken);
        $replacement = $repository->findByToken($rotatedPair->refreshToken);

        self::assertNotNull($current);
        self::assertNotNull($replacement);
        self::assertNotNull($current->revokedAt);
        self::assertSame($replacement->id, $current->replacedByTokenId);
        self::assertSame($originalFamily->familyId, $replacement->familyId);
        self::assertNull($replacement->revokedAt);
    }

    public function testRotateDetectsReuseAndRevokesRefreshTokenFamily(): void
    {
        $db = $this->createRefreshTokenDatabase();
        $repository = new RefreshTokenRepository($db, 'refresh-hash-key');
        $service = new RefreshTokenService($db, $this->createAccessTokenService(), $repository, 3600);
        $originalPair = $service->issuePair(42, null, 'device-1', 'Original Agent', '203.0.113.10');

        $service->rotate($originalPair->refreshToken, null, 'Rotated Agent', '203.0.113.11');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Refresh token reuse detected.');

        try {
            $service->rotate($originalPair->refreshToken);
        } finally {
            $rows = $db->query('SELECT revoked_at, reuse_detected_at FROM tbl_refresh_tokens ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

            self::assertCount(2, $rows);
            self::assertNotNull($rows[0]['reuse_detected_at']);
            self::assertNotNull($rows[0]['revoked_at']);
            self::assertNotNull($rows[1]['revoked_at']);
        }
    }

    public function testRevokeAllForUserScopesByFranchiseWhenProvided(): void
    {
        $db = $this->createRefreshTokenDatabase();
        $repository = new RefreshTokenRepository($db, 'refresh-hash-key');
        $expiresAt = new \DateTimeImmutable('+1 hour');

        $repository->create(42, 7, 'token-a', 'family-a', 'device-a', null, null, $expiresAt);
        $repository->create(42, 8, 'token-b', 'family-b', 'device-b', null, null, $expiresAt);

        $service = new RefreshTokenService($db, $this->createAccessTokenService(), $repository, 3600);
        $service->revokeAllForUser(42, 7, TokenRevocationReason::LOGOUT_ALL);

        $rows = $db->query('SELECT franchise_id, revoked_at FROM tbl_refresh_tokens ORDER BY franchise_id')->fetchAll(PDO::FETCH_ASSOC);

        self::assertNotNull($rows[0]['revoked_at']);
        self::assertNull($rows[1]['revoked_at']);
    }

    private function createAccessTokenService(): AccessTokenService
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('execute')->willReturn(true);
        $statement->method('closeCursor')->willReturn(true);

        $db = $this->createMock(PDO::class);
        $db->method('prepare')->willReturn($statement);

        return new AccessTokenService($db, str_repeat('s', 32), 'https://issuer.test', 300);
    }

    private function createRefreshTokenDatabase(): PDO
    {
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->sqliteCreateFunction('NOW', static fn (): string => date('Y-m-d H:i:s'));
        $db->exec(
            'CREATE TABLE tbl_refresh_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                franchise_id INTEGER NULL,
                token_hash TEXT NOT NULL UNIQUE,
                family_id TEXT NOT NULL,
                device_id TEXT NULL,
                user_agent_hash TEXT NULL,
                ip_address TEXT NULL,
                expires_at TEXT NOT NULL,
                revoked_at TEXT NULL,
                replaced_by_token_id INTEGER NULL,
                reuse_detected_at TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )'
        );
        $db->exec(
            'CREATE TABLE tbl_user_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                franchise_id INTEGER NULL,
                invalidated_at TEXT NULL
            )'
        );

        return $db;
    }
}
