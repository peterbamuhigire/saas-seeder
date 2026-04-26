<?php

declare(strict_types=1);

namespace App\UI\Navigation;

final class ActiveRoute
{
    public static function matches(MenuItem $item, string $currentPath): bool
    {
        if ($item->href === $currentPath) {
            return true;
        }

        foreach ($item->activePatterns as $pattern) {
            if (fnmatch($pattern, $currentPath)) {
                return true;
            }
        }

        return false;
    }
}
