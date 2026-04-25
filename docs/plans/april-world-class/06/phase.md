# Phase 06: Modular SaaS Registry And Tenant Gates

## Objective

Replace module-access stubs with real tenant-scoped module governance. This turns the seeder from "multi-tenant ready auth" into "modular SaaS scaffold."

## Skills Applied

- `modular-saas-architecture`
- `database-design-engineering`
- `webapp-gui-design`
- `system-architecture-design`

## Current Problems

- `hasModuleAccess()` always returns true.
- `requireModuleAccess()` only wraps the stub.
- No `tbl_modules`, `tbl_franchise_modules`, or dependency table exists.
- Navigation is not module-aware.
- There is no disabled-module UX state.

## Deliverables

Create:

- `src/Modules/ModuleRegistry.php`
- `src/Modules/ModuleAccessService.php`
- `src/Modules/ModuleManifest.php`
- `src/Modules/ModuleDependencyResolver.php`
- `src/Modules/ModuleLifecycleService.php`
- `src/Modules/Navigation/ModuleNavigationProvider.php`
- `src/config/modules.php`
- `database/migrations/0002_module_registry.sql`
- `docs/modules/manifest-contract.md`
- `docs/modules/module-lifecycle.md`
- `docs/architecture/module-registry.md`
- `docs/implementation/module-access.md`
- `public/module-disabled.php`
- `tests/Unit/Modules/ModuleAccessServiceTest.php`
- `tests/Feature/Modules/DisabledModuleAccessTest.php`

## Database Requirements

Add:

- `tbl_modules`
- `tbl_franchise_modules`
- `tbl_module_dependencies`
- optional `tbl_module_events` or reuse `tbl_audit_log`

Recommended columns:

- module code,
- name,
- version,
- is_core,
- status,
- config JSON,
- enabled_at,
- disabled_at,
- enabled_by,
- disabled_by.

## Work Breakdown

1. Define module manifest format.
2. Add registry tables and seed core modules:
   - `AUTH`,
   - `RBAC`,
   - `TENANT`,
   - `DASHBOARD`.
3. Implement `ModuleRegistry`.
4. Implement `ModuleAccessService`.
5. Replace global stubs in `src/config/auth.php`.
6. Add route guard helper that checks auth, module, and permission in order.
7. Add disabled-module page/state.
8. Gate navigation by module and permission.
9. Audit enable/disable events.
10. Add tests for:
    - enabled module,
    - disabled module,
    - missing module,
    - super admin context,
    - cross-tenant isolation,
    - direct route access.

## Acceptance Criteria

- Disabled modules disappear from navigation.
- Direct access to disabled module routes is blocked.
- Module access checks are tenant-scoped.
- Core modules cannot be disabled unless explicitly allowed.
- Enable/disable actions are audited.
- Module dependencies are enforced.
- Module registry docs explain how future SaaS modules plug in.

## Validation

Run:

```powershell
rg -n "function hasModuleAccess|function requireModuleAccess" src
rg -n "return true" src\config\auth.php
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -l src\Modules\ModuleAccessService.php
```

Feature tests:

- tenant A module enabled,
- tenant B same module disabled,
- route and nav agree,
- audit log row written.

## Sub-Agent Use

Use a data worker for module tables and seeds. Use a PHP worker for services. Use a UI worker for navigation/disabled-state integration.

## Exit Gate

Phase 09 tenant/module UX cannot be complete until this phase exposes real module status.

