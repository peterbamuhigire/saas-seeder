<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\Request\JsonRequest;
use App\Http\Response\ApiError;
use App\Http\Security\CorsPolicy;
use App\Http\Security\SecurityHeaderPolicy;
use PHPUnit\Framework\TestCase;

final class RequestPolicyTest extends TestCase
{
    public function testJsonRequestNormalizesMethodAndParsesBody(): void
    {
        $request = new JsonRequest('post', ['HTTP_AUTHORIZATION' => 'Bearer token-123'], '{"name":"Peter"}');

        self::assertSame('POST', $request->method());
        self::assertSame(['name' => 'Peter'], $request->jsonBody());
        self::assertSame('token-123', $request->bearerToken());
        self::assertSame([], (new JsonRequest('GET', [], ''))->jsonBody());
        self::assertNull((new JsonRequest('GET', ['HTTP_AUTHORIZATION' => 'Basic abc'], ''))->bearerToken());
        self::assertSame('redirected', (new JsonRequest('GET', ['REDIRECT_HTTP_AUTHORIZATION' => 'Bearer redirected'], ''))->bearerToken());
    }

    public function testMalformedJsonRaisesStableApiError(): void
    {
        $this->expectException(ApiError::class);
        $this->expectExceptionMessage('Invalid JSON body');

        (new JsonRequest('POST', [], '{broken'))->jsonBody();
    }

    public function testApiErrorFactoriesExposeDetails(): void
    {
        $method = ApiError::methodNotAllowed(['GET', 'POST']);
        self::assertSame(405, $method->statusCode());
        self::assertSame(['allowed_methods' => ['GET', 'POST']], $method->details());
        self::assertSame('AUTH_UNAUTHORIZED', ApiError::unauthorized()->errorCode());
        self::assertSame('REQUEST_MALFORMED_JSON', ApiError::invalidJson()->errorCode());
    }

    public function testCorsPolicyResolvesAllowListAndDevelopmentFallback(): void
    {
        $policy = new CorsPolicy();

        self::assertSame('https://app.example.test', $policy->resolveOrigin('https://app.example.test', ['https://app.example.test'], 'production'));
        self::assertNull($policy->resolveOrigin('https://evil.example.test', ['https://app.example.test'], 'production'));
        self::assertSame('*', $policy->resolveOrigin('', [], 'development'));
    }

    public function testDevelopmentHeadersOmitHsts(): void
    {
        $headers = (new SecurityHeaderPolicy())->headers('development');

        self::assertArrayNotHasKey('Strict-Transport-Security', $headers);
        self::assertSame('no-store', $headers['Cache-Control']);
    }
}
