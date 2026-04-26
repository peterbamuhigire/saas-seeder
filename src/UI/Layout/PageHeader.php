<?php

declare(strict_types=1);

namespace App\UI\Layout;

use App\UI\Support\Escaper;

final readonly class PageHeader
{
    /**
     * @param list<array{label:string, href?:string}> $breadcrumbs
     */
    public function __construct(
        private string $title,
        private string $pretitle = '',
        private array $breadcrumbs = []
    ) {
    }

    public function render(): string
    {
        $pretitle = $this->pretitle !== ''
            ? '<div class="page-pretitle">' . Escaper::html($this->pretitle) . '</div>'
            : '';

        return '<div class="page-header d-print-none"><div class="container-xl">'
            . Breadcrumbs::render($this->breadcrumbs)
            . '<div class="row g-2 align-items-center"><div class="col">'
            . $pretitle
            . '<h1 class="page-title">' . Escaper::html($this->title) . '</h1>'
            . '</div></div></div></div>';
    }
}
