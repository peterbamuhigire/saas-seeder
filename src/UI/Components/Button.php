<?php

declare(strict_types=1);

namespace App\UI\Components;

use App\UI\Support\Escaper;

final class Button
{
    public static function link(string $label, string $href, string $variant = 'primary'): string
    {
        return '<a class="btn btn-' . Escaper::html($variant) . '" href="' . Escaper::html($href) . '">' . Escaper::html($label) . '</a>';
    }
}
