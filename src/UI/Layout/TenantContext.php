<?php

declare(strict_types=1);

namespace App\UI\Layout;

final readonly class TenantContext
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $code = '',
        public string $currency = '',
        public string $timezone = 'Africa/Kampala'
    ) {
    }
}
