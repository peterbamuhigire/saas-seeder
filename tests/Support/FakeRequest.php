<?php

declare(strict_types=1);

namespace Tests\Support;

final readonly class FakeRequest
{
    public function __construct(
        public string $method = 'GET',
        public array $headers = [],
        public array $body = []
    ) {
    }
}
