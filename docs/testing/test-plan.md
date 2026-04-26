# Test Plan

Priority coverage:

- Token issue, validation, rotation, logout, and reuse detection.
- Module access enabled/disabled/missing/super-admin paths.
- Registration without runtime DDL.
- Rate limit allowed and exceeded responses.
- Security headers and production CORS behavior.

Run:

```powershell
.\scripts\quality\check.ps1
```
