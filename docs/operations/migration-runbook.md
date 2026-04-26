# Migration Runbook

## Before Running

1. Confirm the target database, credentials, and environment.
2. Review `docs/data/migration-runbook.md` and `docs/release/rollback-plan.md`.
3. Ensure a recent backup exists for any environment that carries real data.
4. Verify the migration ledger table is reachable.

## Execute

```powershell
.\scripts\db\migrate.ps1
.\scripts\db\validate-schema.ps1
```

## Success Criteria

- every migration file is applied in order
- `tbl_schema_migrations` has the expected checksum
- `migration.applied` audit rows exist once `tbl_audit_log` is available
- schema validation passes

## Failure Response

1. Stop applying additional migrations.
2. Record the failed file, MySQL error, and environment.
3. Restore service only after either a forward fix or an approved rollback decision.
4. Update release evidence with the failure and disposition.
