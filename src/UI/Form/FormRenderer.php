<?php

declare(strict_types=1);

namespace App\UI\Form;

final class FormRenderer
{
    /**
     * @param list<Field> $fields
     */
    public function render(array $fields, string $action, string $method = 'post'): string
    {
        $html = '<form action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '" method="' . htmlspecialchars($method, ENT_QUOTES, 'UTF-8') . '">';
        foreach ($fields as $field) {
            $html .= $field->render();
        }

        return $html . '</form>';
    }
}
