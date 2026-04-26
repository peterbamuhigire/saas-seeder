<?php

declare(strict_types=1);

namespace Tests\Unit\Observability;

use App\Http\Request\RequestId;
use App\Observability\RequestContext;
use PHPUnit\Framework\TestCase;

final class RequestContextTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['api_request_id'], $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
    }

    public function testFromGlobalsCapturesRequestMetadata(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/v1/auth/login?foo=bar';
        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['REMOTE_ADDR'] = '203.0.113.7';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Agent';
        RequestId::initialize('req-test-123');

        $context = RequestContext::fromGlobals();

        self::assertSame([
            'request_id' => 'req-test-123',
            'request_method' => 'POST',
            'request_path' => '/api/v1/auth/login',
        ], $context->withAuditDetails());

        self::assertSame('203.0.113.7', $context->ipAddress());
        self::assertSame('PHPUnit Agent', $context->userAgent());
    }
}
