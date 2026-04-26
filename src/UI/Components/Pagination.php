<?php

declare(strict_types=1);

namespace App\UI\Components;

final class Pagination
{
    public static function render(int $page, int $pages): string
    {
        return '<nav aria-label="Pagination"><span class="text-secondary">Page ' . max(1, $page) . ' of ' . max(1, $pages) . '</span></nav>';
    }
}
