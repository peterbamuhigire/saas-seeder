<?php

declare(strict_types=1);

namespace App\UI\Components;

final class FilterBar
{
    public static function search(string $name = 'q'): string
    {
        return '<div class="seeder-filter-bar"><input class="form-control" type="search" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" placeholder="Search" aria-label="Search"></div>';
    }
}
