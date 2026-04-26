<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\UI\Support\Escaper;

final readonly class Checkbox extends Field
{
    public function render(): string
    {
        $id = $this->id();
        $checked = $this->value === '1' ? ' checked' : '';

        return '<label class="form-check"><input class="form-check-input" type="checkbox" id="' . Escaper::html($id) . '" name="' . Escaper::html($this->name) . '" value="1"' . $checked . '>'
            . '<span class="form-check-label">' . Escaper::html($this->label) . '</span></label>';
    }
}
