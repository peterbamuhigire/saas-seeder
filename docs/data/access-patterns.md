# Access Patterns

Primary lookup paths:

- Login: username/email to `tbl_users`, franchise context to `tbl_franchises`, permissions through role and override tables.
- API token validation: JWT `jti` to `tbl_user_sessions`, franchise `permission_version` to invalidate stale grants.
- Refresh rotation: opaque token hash to `tbl_refresh_tokens`, family revoke on reuse.
- Module gate: module code to `tbl_modules`, tenant/module pair to `tbl_franchise_modules`.
- Navigation: configured module code plus permission code to module and RBAC services.
- Rate limit: hashed policy bucket to `tbl_rate_limit_hits` by time window.

Indexes should preserve these paths before feature-specific modules are added.
