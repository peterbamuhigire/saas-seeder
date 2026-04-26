# Operations Runbook

Routine checks:

```powershell
.\scripts\db\validate-schema.ps1
composer test
```

Security review should include auth endpoint status, module gate behavior, CORS origin policy, and rate-limit table growth.
