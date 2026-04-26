<?php

declare(strict_types=1);

namespace App\UI\Components;

use App\UI\Support\Escaper;

final class EmptyState
{
    public static function render(string $title, string $subtitle, string $actionHtml = ''): string
    {
        return '<div class="empty"><p class="empty-title">' . Escaper::html($title) . '</p>'
            . '<p class="empty-subtitle text-secondary">' . Escaper::html($subtitle) . '</p>'
            . ($actionHtml !== '' ? '<div class="empty-action">' . $actionHtml . '</div>' : '')
            . '</div>';
    }
}
