<?php

declare(strict_types=1);

namespace App\Http\RateLimit;

final class RateLimiter
{
    public function __construct(
        private readonly RateLimitStoreInterface $store,
        private readonly string $keySalt = ''
    ) {
    }

    public function hit(RateLimitPolicy $policy, string $identity): RateLimitResult
    {
        $bucket = $this->bucket($policy, $identity);
        $count = $this->store->hit($bucket, $policy->windowSeconds);
        $remaining = max(0, $policy->limit - $count);
        $resetAt = time() + $policy->windowSeconds;

        return new RateLimitResult($count <= $policy->limit, $policy->limit, $remaining, $resetAt);
    }

    private function bucket(RateLimitPolicy $policy, string $identity): string
    {
        return hash('sha256', $policy->name . '|' . $this->keySalt . '|' . strtolower(trim($identity)));
    }
}
