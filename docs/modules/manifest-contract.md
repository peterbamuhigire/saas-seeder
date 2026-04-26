# Module Manifest Contract

Module codes are uppercase stable identifiers such as `AUTH`, `RBAC`, `TENANT`, and `DASHBOARD`.

Registry fields:

- `code`: stable module identifier.
- `name`: display name.
- `version`: module contract version.
- `is_core`: core modules are available to every tenant and protected from normal disable actions.
- `status`: `active`, `disabled`, or `deprecated`.
- `config`: optional JSON defaults.

Tenant enablement is stored in `tbl_franchise_modules`. Future modules should add their navigation items through `src/config/modules.php` and guard direct routes with `requireModuleAccess()`.
