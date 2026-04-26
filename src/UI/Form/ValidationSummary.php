<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\UI\Support\Escaper;

final class ValidationSummary
{
    /**
     * @param list<string> $errors
     */
    public static function render(array $errors): string
    {
        if ($errors === []) {
            return '';
        }

        $items = '';
        foreach ($errors as $error) {
            $items .= '<li>' . Escaper::html($error) . '</li>';
        }

        return '<div class="alert alert-danger" role="alert" aria-live="assertive"><ul class="mb-0">' . $items . '</ul></div>';
    }
}
