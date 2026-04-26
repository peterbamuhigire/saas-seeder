<?php

declare(strict_types=1);

namespace App\UI\Components;

use App\UI\Support\Escaper;

final class KpiStrip
{
    /**
     * @param list<array{label:string,value:string,trend?:string}> $items
     */
    public static function render(array $items): string
    {
        $html = '<div class="row row-cards seeder-kpi-strip">';
        foreach ($items as $item) {
            $html .= '<div class="col-sm-6 col-lg-3"><div class="card"><div class="card-body">'
                . '<div class="subheader">' . Escaper::html($item['label']) . '</div>'
                . '<div class="h1 mb-0">' . Escaper::html($item['value']) . '</div>'
                . (isset($item['trend']) ? '<div class="text-secondary small">' . Escaper::html($item['trend']) . '</div>' : '')
                . '</div></div></div>';
        }

        return $html . '</div>';
    }
}
