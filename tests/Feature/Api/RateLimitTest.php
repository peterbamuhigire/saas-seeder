<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Http\RateLimit\InMemoryRateLimitStore;
use App\Http\RateLimit\RateLimiter;
use App\Http\RateLimit\RateLimitPolicy;
use PHPUnit\Framework\TestCase;

final class RateLimitTest extends TestCase
{
    public function testLimiterAllowsUpToPolicyLimitThenRejects(): void
    {
        $limiter = new RateLimiter(new InMemoryRateLimitStore(), 'test');
        $policy = new RateLimitPolicy('unit', 2, 60);

        self::assertTrue($limiter->hit($policy, 'client')->allowed);
        self::assertTrue($limiter->hit($policy, 'client')->allowed);

        $third = $limiter->hit($policy, 'client');

        self::assertFalse($third->allowed);
        self::assertSame(0, $third->remaining);
    }
}
