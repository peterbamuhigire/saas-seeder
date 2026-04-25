# Architecture Context Map

Phase: April World-Class Phase 02  
Status: Accepted architecture baseline  
Decision sources: [ADR-0001](adr/0001-auth-token-model.md), [ADR-0002](adr/0002-api-runtime-contract.md), [ADR-0003](adr/0003-migration-governance.md), [ADR-0004](adr/0004-module-registry-model.md), [ADR-0005](adr/0005-ui-shell-contract.md), [ADR-0006](adr/0006-quality-gate-model.md)

## System Purpose

SaaS Seeder is a reusable authentication, RBAC, and multi-tenant scaffold for PHP 8.3 SaaS products. The system provides browser session authentication for human operators and bearer-token authentication for API clients while keeping tenant identity, permissions, module access, schema changes, and release evidence explicit.

## Actors

| Actor | Primary intent | Auth mode | Tenant source |
|---|---|---|---|
| Super admin | Manage platform-wide settings, franchises, and emergency support access. | Web session; API bearer when automated. | Session user has `user_type=super_admin`; selected franchise context is explicit when operating on tenant data. |
| Franchise owner | Manage one franchise and its staff, settings, and enabled modules. | Web session; API bearer for integrations. | `tbl_users.franchise_id`, copied into session and access-token claims. |
| Staff | Operate franchise workflows allowed by roles and overrides. | Web session; API bearer for assigned clients. | `tbl_users.franchise_id`, copied into session and access-token claims. |
| Member/end user | Use member-facing portal features. | Web session initially; API bearer for member APIs when added. | `tbl_users.franchise_id`, copied into session and access-token claims. |
| API client | Perform scripted or external integration calls. | `Authorization: Bearer <access_token>` plus refresh token rotation. | Access-token `franchise_id` claim, verified against the refresh-token family and database session state. |
| Setup operator | Install dependencies, create schema, seed baseline data, and run local servers. | Local machine access and database credentials. | Chosen during setup scripts and seed data. |
| AI coding agent | Change code or docs under phase ownership constraints. | Workspace access controlled by the execution environment. | Reads docs/plans before edits; tenant context only appears in source or fixtures. |

## Bounded Contexts

| Context | Responsibility | Owns | Consumes | Boundary rules |
|---|---|---|---|---|
| Auth | Login, logout, session creation, access-token issue, refresh-token rotation, password verification. | `AuthService`, `TokenService`, `PasswordHelper`, `CookieHelper`, `tbl_user_sessions`, future `tbl_refresh_tokens`. | RBAC for permission version checks; Database/Migrations for procedures and tables. | Web session and API bearer flows stay distinct but share credential verification. |
| Tenant/Franchise | Franchise identity, tenant configuration, locale, currency, timezone, and selected context. | `tbl_franchises`, `tbl_franchise_settings`, session franchise fields. | Auth for current user; Module Registry for enabled features. | Tenant identity must be explicit in every critical flow. Super admin tenant switching must be logged. |
| RBAC | Roles, permissions, user overrides, franchise role overrides, permission version invalidation. | `PermissionService`, `tbl_permissions`, `tbl_global_roles`, `tbl_global_role_permissions`, `tbl_user_roles`, `tbl_user_permissions`, `tbl_franchise_role_overrides`. | Tenant context; Auth token claims. | Permission checks require a user id, tenant id when scoped, permission code, and module access result. |
| Module Registry | Product modules, dependencies, tenant enablement, route guards, and menu visibility. | Future `tbl_modules`, `tbl_module_dependencies`, `tbl_franchise_modules`, `ModuleRegistryService`. | Tenant/Franchise and RBAC. | Module checks gate route access and menu rendering before page-specific logic executes. |
| API Runtime | Request parsing, CORS, error envelope, auth middleware, JSON response contract, request correlation. | `api/bootstrap.php`, future middleware and response classes. | Auth, RBAC, Module Registry, Database. | API endpoints return the ADR-0002 envelope and never redirect. |
| UI Shell | Shared PHP includes, Tabler assets, page metadata, menus, alerts, and panel navigation. | `public/includes`, `public/adminpanel/includes`, `public/memberpanel/includes`, future shared primitives. | Auth session, RBAC, Module Registry. | Pages render through shared includes and escape output at render boundaries. |
| Database/Migrations | Schema lifecycle, procedures, seed data, rollback posture, drift checks. | `docs/seeder-template/migration.sql`, setup/db scripts, future migration ledger. | All contexts. | No endpoint creates production schema at request time after Phase 05. |
| Operations/Release | Setup, validation commands, observability fields, release evidence, rollback notes. | `scripts/setup`, `scripts/db`, `scripts/server`, `docs/operations`, future evidence notes. | Quality gates and all context-level checks. | Every phase records validation evidence before handoff. |

## Context Relationships

| Upstream | Downstream | Relationship | Contract |
|---|---|---|---|
| Auth | Tenant/Franchise | Auth resolves the active tenant after credential verification. | Session and access tokens carry `user_id`, `franchise_id`, `user_type`, and permission version. |
| Auth | RBAC | Auth calls RBAC for permission collection and token invalidation posture. | RBAC changes increment franchise `permission_version`. |
| Tenant/Franchise | Module Registry | Tenant decides which module set is enabled. | Module Registry stores tenant module state and exposes route/menu guards. |
| RBAC | Module Registry | Permission grants are meaningful only when the module is enabled. | Guard order is auth, tenant, module, permission. |
| API Runtime | Auth | API Runtime enforces bearer auth and refresh flow mechanics. | Access tokens are short lived; refresh tokens are opaque, rotating, and stored hashed. |
| UI Shell | Auth/RBAC/Module Registry | UI Shell consumes session context for page chrome and menus. | Hidden menus are not authorization; protected routes still call guards. |
| Database/Migrations | All contexts | Database changes provide tables, indexes, procedures, and rollback. | Migrations are ledged and run by scripts, not ad hoc endpoint logic. |
| Operations/Release | All contexts | Operations validates behavior and captures evidence. | Quality gates in ADR-0006 are required before release signoff. |
