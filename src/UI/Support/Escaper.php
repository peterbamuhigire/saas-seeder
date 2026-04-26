<?php

declare(strict_types=1);

namespace App\UI\Support;

final class Escaper
{
    public static function html(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
