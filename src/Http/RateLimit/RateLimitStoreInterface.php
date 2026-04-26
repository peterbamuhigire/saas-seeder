<?php

declare(strict_types=1);

namespace App\Http\RateLimit;

interface RateLimitStoreInterface
{
    public function hit(string $bucket, int $windowSeconds): int;

    public function count(string $bucket, int $windowSeconds): int;
}
