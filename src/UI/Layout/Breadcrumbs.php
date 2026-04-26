<?php

declare(strict_types=1);

namespace App\UI\Layout;

use App\UI\Support\Escaper;

final class Breadcrumbs
{
    /**
     * @param list<array{label:string, href?:string}> $items
     */
    public static function render(array $items): string
    {
        if ($items === []) {
            return '';
        }

        $html = '<ol class="breadcrumb" aria-label="Breadcrumb">';
        foreach ($items as $item) {
            $label = Escaper::html($item['label']);
            if (isset($item['href'])) {
                $html .= '<li class="breadcrumb-item"><a href="' . Escaper::html($item['href']) . '">' . $label . '</a></li>';
            } else {
                $html .= '<li class="breadcrumb-item active" aria-current="page">' . $label . '</li>';
            }
        }

        return $html . '</ol>';
    }
}
