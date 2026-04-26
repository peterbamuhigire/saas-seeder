# Migration Runbook

Migrations live in `database/migrations/` and seeds live in `database/seeds/`.

Local apply:

```powershell
.\scripts\db\migrate.ps1 -WithSeeds
.\scripts\db\validate-schema.ps1
```

Environment variables `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` are used when present. The migration user should have DDL privileges. The application user should not.

After adding a migration, rebuild the current schema bundle:

```powershell
.\scripts\db\rebuild-template-migration.ps1
```
