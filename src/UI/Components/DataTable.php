<?php

declare(strict_types=1);

namespace App\UI\Components;

use App\UI\Support\Escaper;

final class DataTable
{
    /**
     * @param list<string> $columns
     * @param list<array<string, mixed>> $rows
     */
    public static function render(array $columns, array $rows): string
    {
        $head = '';
        foreach ($columns as $column) {
            $head .= '<th>' . Escaper::html($column) . '</th>';
        }

        $body = '';
        foreach ($rows as $row) {
            $body .= '<tr>';
            foreach ($columns as $column) {
                $body .= '<td>' . Escaper::html($row[$column] ?? '') . '</td>';
            }
            $body .= '</tr>';
        }

        return '<div class="table-responsive"><table class="table table-vcenter"><thead><tr>' . $head . '</tr></thead><tbody>' . $body . '</tbody></table></div>';
    }
}
