<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\UI\Support\Escaper;

final class FormAlert
{
    public static function render(string $message, string $type = 'danger'): string
    {
        return '<div class="alert alert-' . Escaper::html($type) . '" role="alert" aria-live="polite">'
            . Escaper::html($message)
            . '</div>';
    }
}
