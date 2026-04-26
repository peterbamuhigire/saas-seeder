<?php

declare(strict_types=1);

namespace App\UI\Layout;

final class Footer
{
    public static function render(string $label = 'SaaS Seeder'): string
    {
        return '<footer class="footer footer-transparent d-print-none"><div class="container-xl text-secondary">'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . '</div></footer>';
    }
}
