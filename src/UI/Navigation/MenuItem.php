<?php

declare(strict_types=1);

namespace App\UI\Navigation;

final readonly class MenuItem
{
    /**
     * @param list<string> $activePatterns
     */
    public function __construct(
        public string $label,
        public string $href,
        public string $panel = 'admin',
        public ?string $icon = null,
        public ?string $permission = null,
        public ?string $module = null,
        public array $activePatterns = []
    ) {
    }
}
