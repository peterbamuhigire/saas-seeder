<?php

declare(strict_types=1);

namespace App\UI\Components;

use App\UI\Support\Escaper;

final class ModuleBadge
{
    public static function render(string $moduleCode, bool $enabled): string
    {
        return '<span class="badge ' . ($enabled ? 'bg-green-lt' : 'bg-red-lt') . '">' . Escaper::html($moduleCode) . '</span>';
    }
}
