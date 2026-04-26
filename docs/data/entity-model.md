# Entity Model

The platform schema is tenant-first. `tbl_franchises` is the tenant boundary, `tbl_users` belongs to a franchise except for root/super-admin accounts, and RBAC is composed from global roles plus tenant and user overrides.

Core entities:

- `tbl_franchises`: tenant identity, subscription state, locale defaults, permission version.
- `tbl_users`: authenticated principals, tenant membership, lockout state.
- `tbl_global_roles`, `tbl_permissions`, `tbl_user_roles`: base RBAC model.
- `tbl_franchise_role_overrides`, `tbl_user_permissions`: tenant/user exceptions.
- `tbl_modules`, `tbl_franchise_modules`, `tbl_module_dependencies`: modular SaaS registry and tenant enablement.
- `tbl_user_sessions`, `tbl_refresh_tokens`: access-token session validation and refresh-token rotation.
- `tbl_audit_log`: append-only operational and security events.
- `tbl_rate_limit_hits`: fixed-window request throttling buckets.

Tenant-scoped tables must include `franchise_id` unless they are global catalog/config tables or security logs where tenant can be null for pre-auth events.
