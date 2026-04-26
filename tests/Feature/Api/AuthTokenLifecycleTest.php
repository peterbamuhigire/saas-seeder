<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Auth\Token\AccessTokenService;
use App\Auth\Token\RefreshTokenRepository;
use App\Auth\Token\RefreshTokenService;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

final class AuthTokenLifecycleTest extends TestCase
{
    public function testTokenLifecycleIssuesRotatesAndRejectsRefreshTokenReuseWithoutMysqlProcedures(): void
    {
        // The HTTP endpoints call MySQL stored procedures through bootstrap wiring.
        // This focused feature test exercises the same lifecycle services with SQLite.
        $db = $this->createRefreshTokenDatabase();
        $repository = new RefreshTokenRepository($db, 'refresh-hash-key');
        $service = new RefreshTokenService($db, $this->createAccessTokenService(), $repository, 3600);

        $initial = $service->issuePair(42, null, 'device-1', 'Feature Agent', '203.0.113.10');
        $rotated = $service->rotate($initial->refreshToken, 'device-1', 'Feature Agent v2', '203.0.113.11');

        self::assertSame(42, $initial->claims->userId);
        self::assertSame(42, $rotated->claims->userId);
        self::assertNotSame($initial->accessToken, $rotated->accessToken);
        self::assertNotSame($initial->refreshToken, $rotated->refreshToken);
        self::assertNull($repository->findByToken($rotated->refreshToken)->revokedAt);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Refresh token reuse detected.');

        $service->rotate($initial->refreshToken);
    }

    public function testRepositoryStoresRefreshTokenHashesButCanResolveByPlainToken(): void
    {
        $db = $this->createRefreshTokenDatabase();
        $repository = new RefreshTokenRepository($db, 'refresh-hash-key');
        $plainToken = 'plain-refresh-token';

        $created = $repository->create(
            42,
            null,
            $plainToken,
            'family-1',
            'device-1',
            'Feature Agent',
            '203.0.113.10',
            new \DateTimeImmutable('+1 hour')
        );

        $rawRow = $db->query('SELECT token_hash FROM tbl_refresh_tokens WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        $resolved = $repository->findByToken($plainToken);

        self::assertSame($repository->hashToken($plainToken), $rawRow['token_hash']);
        self::assertNotSame($plainToken, $rawRow['token_hash']);
        self::assertSame($created->id, $resolved->id);
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

        return $db;
    }
}
