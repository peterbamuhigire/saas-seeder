# Module Boundaries

Phase: April World-Class Phase 02  
Status: Accepted architecture baseline  
Primary ADR: [ADR-0004](adr/0004-module-registry-model.md)

## Boundary Principles

Modules are product capabilities, not PHP folders. A module may own routes, menu entries, permissions, migrations, and settings, but it must use shared Auth, RBAC, Tenant, API Runtime, UI Shell, and Database/Migrations contracts.

Guard order for module-owned behavior is fixed:

1. Authenticate the actor.
2. Resolve tenant identity.
3. Verify module enablement for the tenant.
4. Verify permission code or role capability.
5. Execute the route use case inside its transaction boundary.
6. Write audit and observability fields.

## Shared Contexts

| Context | Shared service boundary | Modules may do | Modules must not do |
|---|---|---|---|
| Auth | `AuthService`, token middleware, session helpers. | Request current actor and auth mode. | Verify passwords, create refresh tokens, or mutate sessions directly. |
| Tenant/Franchise | Tenant resolver and franchise settings access. | Read active tenant and tenant settings. | Infer tenant from URL, request body, or hidden fields when authenticated context already exists. |
| RBAC | `PermissionService` and permission definitions. | Define module permission codes and call guard helpers. | Bypass super-admin and franchise override rules. |
| Module Registry | Future `ModuleRegistryService`. | Register module metadata, menu entries, route patterns, dependencies, and default tenant state. | Hard-code route access with folder names only. |
| UI Shell | Shared PHP includes and primitives. | Provide page content slots and module menu metadata. | Fork separate shell markup for ordinary module pages. |
| Database/Migrations | Migration ledger and schema contract. | Ship migration files and seed entries. | Create or alter schema from public/API request handlers. |

## Module Metadata Contract

Each module is represented by a stable row in `tbl_modules` with:

| Field | Decision |
|---|---|
| `code` | Lowercase stable identifier, unique, used by guards and menu entries. |
| `name` | Human-readable display name. |
| `description` | Short operational purpose. |
| `status` | `active`, `deprecated`, or `retired`. Retired modules cannot be enabled for tenants. |
| `is_core` | Core modules cannot be disabled for a tenant. Auth, RBAC, Tenant, API Runtime, UI Shell, Database/Migrations, and Operations are core. |
| `default_enabled` | New tenant default. Must be false for paid or domain-specific modules. |
| `version` | Semver-like module schema/runtime contract version. |

Tenant enablement is stored in `tbl_franchise_modules` with `franchise_id`, `module_id`, `status`, `enabled_at`, `enabled_by`, `disabled_at`, `disabled_by`, and JSON `settings`.

Module dependencies are stored in `tbl_module_dependencies` with `module_id`, `depends_on_module_id`, and `requirement` set to `required` or `optional`.

## Route and Menu Boundaries

| Surface | Boundary rule |
|---|---|
| Browser page route | Calls `requireAuth`, resolves tenant, calls `requireModuleAccess($moduleCode)`, then `requirePermissionGlobal($permissionCode)`. |
| API endpoint | Middleware resolves bearer token, tenant claim, module code from route metadata, and permission code before handler execution. |
| Menu rendering | UI Shell fetches module menu entries and hides entries when module or permission checks fail. Hiding is a convenience only. |
| Super admin route | Platform routes use `platform` tenant scope and bypass tenant module enablement only for system administration tasks. |
| Member route | Member routes require tenant module enablement and member permission or member role mapping. |

## Core Modules

| Module code | Scope | Notes |
|---|---|---|
| `auth` | Core | Login, logout, password, session, token lifecycle. |
| `tenant` | Core | Franchise identity, settings, locale, currency, timezone. |
| `rbac` | Core | Roles, permissions, overrides, permission version invalidation. |
| `module_registry` | Core | Module metadata, dependency, enablement, guards, menu gating. |
| `api_runtime` | Core | API envelope, middleware, correlation, JSON errors. |
| `ui_shell` | Core | Shared shell and Tabler primitives. |
| `database_migrations` | Core | Migration ledger and schema operations. |
| `operations_release` | Core | Validation, evidence, rollback posture. |
