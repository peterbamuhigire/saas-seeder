# Data Invariants

- Every tenant-owned row must be reachable from a valid `tbl_franchises.id`.
- Module enablement is unique per tenant and module code.
- Core modules are always globally active and cannot be disabled by default.
- Refresh token values are never stored directly; only keyed hashes are persisted.
- Reused refresh tokens revoke their family.
- App runtime users do not need DDL privileges.
- Schema changes flow through `database/migrations/` and are reflected in `database/schema/current.sql`.

Run `.\scripts\db\validate-schema.ps1` after migrations to check required tables and collation.
