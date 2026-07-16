<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\DTO\AuthResult;
use App\Auth\DTO\LoginDTO;
use App\Auth\Security\CredentialPolicy;
use App\Auth\Security\DeviceFingerprint;
use App\Auth\Token\TokenClaims;
use App\Auth\Token\TokenPair;
use PHPUnit\Framework\TestCase;

final class AuthValueObjectsTest extends TestCase
{
    public function testLoginDtoRetainsRequestContext(): void
    {
        $dto = new LoginDTO('peter', 'secret', '127.0.0.1', 'Browser');

        self::assertSame('peter', $dto->getUsername());
        self::assertSame('secret', $dto->getPassword());
        self::assertSame('127.0.0.1', $dto->getIpAddress());
        self::assertSame('Browser', $dto->getUserAgent());
    }

    public function testAuthResultExposesStableRepresentation(): void
    {
        $result = new AuthResult(7, 3, 'peter', 'SUCCESS', ['email' => 'p@example.test'], 'jti', null);

        self::assertTrue($result->isSuccessful());
        self::assertSame(7, $result->getUserId());
        self::assertSame(3, $result->getFranchiseId());
        self::assertSame('peter', $result->getUsername());
        self::assertSame('SUCCESS', $result->getStatus());
        self::assertSame(['email' => 'p@example.test'], $result->getUserData());
        self::assertSame('jti', $result->getToken());
        self::assertNull($result->getMessage());
        self::assertSame(7, $result->toArray()['userId']);
        self::assertFalse((new AuthResult(0, null, 'x', 'INVALID_PASSWORD'))->isSuccessful());
    }

    public function testCredentialPolicyReportsEachMissingRequirement(): void
    {
        $policy = new CredentialPolicy();

        self::assertCount(3, $policy->validatePassword('short'));
        self::assertSame([], $policy->validatePassword('SecurePass123'));
    }

    public function testDeviceFingerprintIsDeterministicAndContextSensitive(): void
    {
        $fingerprint = new DeviceFingerprint();

        self::assertSame($fingerprint->hash('Agent', '127.0.0.1'), $fingerprint->hash('Agent', '127.0.0.1'));
        self::assertNotSame($fingerprint->hash('Agent', '127.0.0.1'), $fingerprint->hash('Other', '127.0.0.1'));
    }

    public function testTokenPairCarriesAccessAndRefreshTokens(): void
    {
        $claims = new TokenClaims('issuer', 'audience', 100, 1000, 7, 3, 'jti', 2);
        $refreshExpiresAt = new \DateTimeImmutable('2030-01-01T00:00:00+00:00');
        $pair = new TokenPair('access', 'refresh', 900, $claims, $refreshExpiresAt);

        self::assertSame('access', $pair->accessToken);
        self::assertSame('refresh', $pair->refreshToken);
        self::assertSame('Bearer', $pair->tokenType);
        self::assertSame(900, $pair->expiresIn);
        self::assertSame('2030-01-01T00:00:00+00:00', $pair->toArray()['refresh_expires_at']);
    }
}
