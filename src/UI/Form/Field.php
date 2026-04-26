<?php

declare(strict_types=1);

namespace App\UI\Form;

abstract readonly class Field
{
    public function __construct(
        protected string $name,
        protected string $label,
        protected string $value = '',
        protected string $description = '',
        protected string $error = '',
        protected bool $required = false
    ) {
    }

    abstract public function render(): string;

    protected function id(): string
    {
        return 'field-' . preg_replace('/[^a-z0-9_-]/i', '-', $this->name);
    }
}
