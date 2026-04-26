# Phase 05 Evidence

Implemented:

- Added governed migrations under `database/migrations/`.
- Added seeds under `database/seeds/`.
- Added schema bundle and validation SQL under `database/schema/`.
- Added DB scripts:
  - `scripts/db/migrate.ps1`
  - `scripts/db/validate-schema.ps1`
  - `scripts/db/rebuild-template-migration.ps1`
- Removed runtime signup table creation from `api/v1/public/auth/register.php`.
- Added data docs for entity model, access patterns, invariants, migration runbook, and rollback policy.

Validation:

- `rg -n "ensureSignupTable|CREATE TABLE IF NOT EXISTS" api public src` returned no matches.
- PHP lint passed.
- PHPUnit passed: 16 tests, 59 assertions.
