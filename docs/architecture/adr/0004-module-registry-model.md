# ADR-0004: Module Registry Model

Status: Accepted  
Date: 2026-04-26  
Phase: April World-Class Phase 02

## Context

`hasModuleAccess` and `requireModuleAccess` currently always allow access. SaaS Seeder needs tenant-aware modules so a reusable scaffold can enable, hide, guard, and test capabilities consistently.

## Decision

Implement a first-class module registry backed by tables and a service boundary.

Tables:

- `tbl_modules`
- `tbl_module_dependencies`
- `tbl_franchise_modules`
- `tbl_module_menu_entries`
- `tbl_module_route_guards`

Service boundary:

- `ModuleRegistryService::isEnabledForTenant(string $moduleCode, int $franchiseId): bool`
- `ModuleRegistryService::requireEnabled(string $moduleCode, int $franchiseId): void`
- `ModuleRegistryService::getMenuEntries(int $franchiseId, int $userId): array`
- `ModuleRegistryService::resolveRouteGuard(string $route, string $method): ModuleRouteGuard`

Route guarding and menu gating both use the registry. Menus hide disabled or unauthorized entries, while routes enforce the same decision server-side.

## Consequences

- Phase 06 creates module registry migrations and services.
- Phase 09 consumes registry menu entries in the UI shell.
- API Runtime consumes route guard metadata for module and permission checks.
- Core scaffold modules are protected from tenant disablement.

## Rejected Alternatives

| Alternative | Reason rejected |
|---|---|
| Keep folder-based module detection | Folders cannot express tenant enablement, dependencies, menus, or route permissions. |
| Use only permission codes as modules | Permissions answer what a user may do, not whether a tenant owns a capability. |
| Menu-only gating | Hidden navigation is not authorization and does not protect direct route access. |
