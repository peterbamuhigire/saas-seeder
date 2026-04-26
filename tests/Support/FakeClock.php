<?php

declare(strict_types=1);

namespace Tests\Support;

final class FakeClock
{
    public function __construct(private int $now)
    {
    }

    public function now(): int
    {
        return $this->now;
    }
}
