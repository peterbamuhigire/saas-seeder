<?php

declare(strict_types=1);

namespace App\UI\Components;

use App\UI\Support\Escaper;

final class StateBlock
{
    public static function render(string $state, string $message): string
    {
        $variant = match ($state) {
            'success' => 'success',
            'error' => 'danger',
            'loading' => 'info',
            default => 'secondary',
        };

        return '<div class="alert alert-' . $variant . '" role="status" aria-live="polite">' . Escaper::html($message) . '</div>';
    }
}
