<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\UI\Support\Escaper;

final readonly class TextInput extends Field
{
    public function render(): string
    {
        $id = $this->id();
        $required = $this->required ? ' required aria-required="true"' : '';
        $invalidClass = $this->error !== '' ? ' is-invalid' : '';
        $invalidAttr = $this->error !== '' ? ' aria-invalid="true"' : '';

        return '<div class="mb-3"><label class="form-label" for="' . Escaper::html($id) . '">' . Escaper::html($this->label) . '</label>'
            . '<input type="text" class="form-control' . $invalidClass . '" id="' . Escaper::html($id) . '" name="' . Escaper::html($this->name) . '" value="' . Escaper::html($this->value) . '"' . $required . $invalidAttr . '>'
            . $this->helpHtml()
            . '</div>';
    }

    private function helpHtml(): string
    {
        $html = '';
        if ($this->description !== '') {
            $html .= '<div class="form-hint">' . Escaper::html($this->description) . '</div>';
        }
        if ($this->error !== '') {
            $html .= '<div class="invalid-feedback d-block">' . Escaper::html($this->error) . '</div>';
        }

        return $html;
    }
}
