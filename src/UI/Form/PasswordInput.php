<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\UI\Support\Escaper;

final readonly class PasswordInput extends Field
{
    public function render(): string
    {
        $id = $this->id();
        $required = $this->required ? ' required aria-required="true"' : '';

        return '<div class="mb-3"><label class="form-label" for="' . Escaper::html($id) . '">' . Escaper::html($this->label) . '</label>'
            . '<input type="password" class="form-control" id="' . Escaper::html($id) . '" name="' . Escaper::html($this->name) . '"' . $required . '>'
            . ($this->description !== '' ? '<div class="form-hint">' . Escaper::html($this->description) . '</div>' : '')
            . ($this->error !== '' ? '<div class="invalid-feedback d-block">' . Escaper::html($this->error) . '</div>' : '')
            . '</div>';
    }
}
