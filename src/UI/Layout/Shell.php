<?php

declare(strict_types=1);

namespace App\UI\Layout;

final readonly class Shell
{
    public function __construct(
        private string $title,
        private string $body,
        private string $panel = 'admin'
    ) {
    }

    public function render(): string
    {
        $title = htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8');

        return '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . $title . '</title>'
            . '<link rel="stylesheet" href="/assets/tabler/css/tabler.min.css">'
            . '<link rel="stylesheet" href="/assets/css/seeder-tokens.css">'
            . '<link rel="stylesheet" href="/assets/css/seeder-components.css">'
            . '</head><body><div class="page" data-panel="' . htmlspecialchars($this->panel, ENT_QUOTES, 'UTF-8') . '">'
            . '<main id="main-body" class="page-wrapper" tabindex="-1">'
            . $this->body
            . '</main></div><script src="/assets/js/seeder-ui.js"></script></body></html>';
    }
}
