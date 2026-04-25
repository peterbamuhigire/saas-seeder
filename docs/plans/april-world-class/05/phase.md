# Phase 05: Database And Migration Governance

## Objective

Move from a single large bootstrap SQL script plus runtime DDL to governed schema evolution with migrations, schema validation, repeatable seeds, rollback posture, and tenant-safe data invariants.

## Skills Applied

- `database-design-engineering`
- `system-architecture-design`
- `modular-saas-architecture`
- `skill-composition-standards`

## Current Problems

- `docs/seeder-template/migration.sql` is the primary schema source, but there is no migration ledger.
- Runtime DDL exists in API registration via `ensureSignupTable()`.
- There is no schema validation script.
- Migration rollback posture is undocumented.
- Module registry tables are missing.

## Deliverables

Create:

- `database/migrations/0001_platform_base.sql`
- `database/migrations/0002_module_registry.sql`
- `database/migrations/0003_api_token_lifecycle.sql`
- `database/seeds/0001_platform_permissions.sql`
- `database/seeds/0002_demo_franchise.sql`
- `database/schema/current.sql`
- `database/schema/checks.sql`
- `database/schema/invariants.sql`
- `scripts/db/migrate.ps1`
- `scripts/db/validate-schema.ps1`
- `scripts/db/rebuild-template-migration.ps1`
- `docs/data/entity-model.md`
- `docs/data/access-patterns.md`
- `docs/data/invariants.md`
- `docs/data/migration-runbook.md`
- `docs/data/rollback-policy.md`

Update:

- `docs/seeder-template/migration.sql`
- `api/v1/public/auth/register.php`

## Work Breakdown

1. Add migration ledger table:
   - migration id,
   - checksum,
   - applied_at,
   - applied_by,
   - execution_ms.
2. Split base schema into migration files.
3. Move seed data to `database/seeds/`.
4. Make `docs/seeder-template/migration.sql` generated or explicitly a bootstrap bundle.
5. Remove runtime table creation from registration.
6. Add validation SQL:
   - required tables,
   - required FKs,
   - collation,
   - row format,
   - stored procedures,
   - tenant indexes,
   - module tables,
   - token tables.
7. Add rollback policy per migration:
   - reversible,
   - compensating-only,
   - restore snapshot,
   - forward-fix-only.
8. Add least-privilege DB guidance:
   - migration user has DDL,
   - app user does not.

## Acceptance Criteria

- Fresh install from migrations succeeds.
- Re-running migrations is safe where declared.
- Runtime app path performs no schema creation.
- Template bootstrap migration matches governed migrations.
- `database/schema/checks.sql` passes against a fresh DB.
- Every tenant-scoped table has tenant/franchise key where appropriate.
- Every unique constraint is tenant-aware where appropriate.

## Validation

Run:

```powershell
.\scripts\db\migrate.ps1
.\scripts\db\validate-schema.ps1
rg -n "CREATE TABLE IF NOT EXISTS" api public src
rg -n "ensureSignupTable" api public src
```

Manual DB checks:

- `SHOW TABLES`
- FK count from `information_schema.TABLE_CONSTRAINTS`
- collation and row format checks
- stored procedure existence checks

## Sub-Agent Use

Use a data worker for migration files and schema docs. Use an API/security worker only for removing runtime DDL and adjusting registration behaviour.

## Exit Gate

Phase 06 module registry and Phase 10 database tests depend on this migration runner.

