# Security Review Checklist

- Verify secrets come from `.env` or a vault and are not logged.
- Verify login, refresh, logout, and logout-all enforce method guards and rate limits.
- Verify responses expose `request_id` and do not expose secrets.
- Verify refresh-token storage remains hashed only.
- Verify module-disabled and permission-denied paths fail closed.
- Verify migrations are applied through `scripts/db/migrate.ps1`, not runtime code.
- Verify CORS allow-lists are explicit outside development.
- Verify any accepted exception has owner, mitigation, and expiry date.
