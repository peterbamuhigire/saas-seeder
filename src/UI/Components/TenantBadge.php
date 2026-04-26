<?php

declare(strict_types=1);

namespace App\UI\Components;

use App\UI\Support\Escaper;

final class TenantBadge
{
    public static function render(string $tenantName): string
    {
        return '<span class="badge bg-blue-lt">' . Escaper::html($tenantName) . '</span>';
    }
}
