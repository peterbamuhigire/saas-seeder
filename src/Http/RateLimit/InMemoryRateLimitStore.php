<?php

declare(strict_types=1);

namespace App\Http\RateLimit;

final class InMemoryRateLimitStore implements RateLimitStoreInterface
{
    /** @var array<string, list<int>> */
    private array $hits = [];

    public function hit(string $bucket, int $windowSeconds): int
    {
        $now = time();
        $this->hits[$bucket] = array_values(array_filter(
            $this->hits[$bucket] ?? [],
            static fn (int $hitAt): bool => $hitAt >= $now - $windowSeconds
        ));
        $this->hits[$bucket][] = $now;

        return count($this->hits[$bucket]);
    }

    public function count(string $bucket, int $windowSeconds): int
    {
        $now = time();

        return count(array_filter(
            $this->hits[$bucket] ?? [],
            static fn (int $hitAt): bool => $hitAt >= $now - $windowSeconds
        ));
    }
}
