<?php

declare(strict_types=1);

namespace App\UI\Layout;

final class Topbar
{
    public static function skipLink(): string
    {
        return '<a href="#main-body" class="visually-hidden-focusable position-absolute" style="z-index:9999">Skip to main content</a>';
    }
}
