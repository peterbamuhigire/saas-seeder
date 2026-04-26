<?php

declare(strict_types=1);

namespace App\Modules\Navigation;

use App\Modules\ModuleAccessService;

final class ModuleNavigationProvider
{
    public function __construct(private readonly ModuleAccessService $access)
    {
    }

    /**
     * @param list<array{label:string,href:string,module?:string,permission?:string,icon?:string}> $items
     * @return list<array{label:string,href:string,module?:string,permission?:string,icon?:string}>
     */
    public function visibleItems(array $items, ?int $franchiseId, ?string $userType = null): array
    {
        return array_values(array_filter(
            $items,
            fn (array $item): bool => !isset($item['module'])
                || $this->access->hasAccess($franchiseId, (string) $item['module'], $userType)
        ));
    }
}
