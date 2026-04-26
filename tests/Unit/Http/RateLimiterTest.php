<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\RateLimit\InMemoryRateLimitStore;
use App\Http\RateLimit\RateLimiter;
use App\Http\RateLimit\RateLimitPolicy;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    public function testSeparateIdentitiesUseSeparateBuckets(): void
    {
        $limiter = new RateLimiter(new InMemoryRateLimitStore(), 'salt');
        $policy = new RateLimitPolicy('test', 1, 60);

        self::assertTrue($limiter->hit($policy, 'a')->allowed);
        self::assertTrue($limiter->hit($policy, 'b')->allowed);
        self::assertFalse($limiter->hit($policy, 'a')->allowed);
    }
}
