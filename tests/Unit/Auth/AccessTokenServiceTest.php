<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\Token\AccessTokenService;
use App\Auth\Token\TokenClaims;
use PDO;
use PHPUnit\Framework\TestCase;

final class AccessTokenServiceTest extends TestCase
{
    public function testIssueTokenWithClaimsBuildsExpectedJwtClaimsWithoutPersistingSession(): void
    {
        $db = new PDO('sqlite::memory:');
        $db->exec('CREATE TABLE tbl_franchises (id INTEGER PRIMARY KEY, permission_version INTEGER NOT NULL)');
        $db->exec('INSERT INTO tbl_franchises (id, permission_version) VALUES (7, 12)');

        $service = new AccessTokenService($db, str_repeat('s', 32), 'https://issuer.test', 120);

        $issued = $service->issueTokenWithClaims(
            userId: 42,
            franchiseId: 7,
            ipAddress: '203.0.113.10',
            userAgent: 'Unit Test',
            persistSession: false
        );

        self::assertIsString($issued['token']);
        self::assertSame(120, $issued['expires_in']);
        self::assertInstanceOf(TokenClaims::class, $issued['claims']);
        self::assertSame('https://issuer.test', $issued['claims']->issuer);
        self::assertSame('https://issuer.test', $issued['claims']->audience);
        self::assertSame(42, $issued['claims']->userId);
        self::assertSame(7, $issued['claims']->franchiseId);
        self::assertSame(12, $issued['claims']->permissionVersion);
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $issued['claims']->jti);

        $decoded = $service->decodeClaimsWithoutSessionValidation($issued['token']);

        self::assertSame($issued['claims']->toArray(), $decoded->toArray());
    }

    public function testValidateClaimsRejectsTokenIssuedForDifferentIssuerBeforeSessionLookup(): void
    {
        $issuerA = new AccessTokenService(new PDO('sqlite::memory:'), str_repeat('s', 32), 'https://issuer-a.test');
        $issuerB = new AccessTokenService(new PDO('sqlite::memory:'), str_repeat('s', 32), 'https://issuer-b.test');

        $issued = $issuerA->issueTokenWithClaims(42, null, persistSession: false);

        self::assertNull($issuerB->validateClaims($issued['token']));
    }

    public function testValidateClaimsRejectsExpiredTokenBeforeSessionLookup(): void
    {
        $service = new AccessTokenService(new PDO('sqlite::memory:'), str_repeat('s', 32), 'https://issuer.test', -1);

        $issued = $service->issueTokenWithClaims(42, null, persistSession: false);

        self::assertNull($service->validateClaims($issued['token']));
    }
}
