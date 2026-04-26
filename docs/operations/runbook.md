# Operations Runbook

## Routine Checks

```powershell
.\scripts\quality\check.ps1
.\scripts\db\validate-schema.ps1
```

## Daily Or Pre-Release Review

- confirm auth endpoints return request IDs
- review recent lockout, refresh reuse, and rate-limit behavior
- verify module gate behavior for disabled modules
- confirm CORS origins and security headers match the target environment
- confirm migration ledger and audit rows are present after schema changes

## Escalate To A Focused Runbook

- auth issue: `docs/operations/auth-incident-runbook.md`
- migration issue: `docs/operations/migration-runbook.md`
- broader incident posture: `docs/operations/incident-response.md`
