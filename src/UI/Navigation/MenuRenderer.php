<?php

declare(strict_types=1);

namespace App\UI\Navigation;

use App\UI\Support\Escaper;

final class MenuRenderer
{
    /**
     * @param list<MenuItem> $items
     */
    public function render(array $items, string $panel, string $currentPath): string
    {
        $html = '';
        foreach ($items as $item) {
            if ($item->panel !== $panel) {
                continue;
            }

            $active = ActiveRoute::matches($item, $currentPath) ? ' active' : '';
            $html .= '<a class="nav-link' . $active . '" href="' . Escaper::html($item->href) . '">'
                . '<span class="nav-link-title">' . Escaper::html($item->label) . '</span></a>';
        }

        return $html;
    }
}
