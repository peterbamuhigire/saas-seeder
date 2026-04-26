# Release Plan

1. Apply migrations in staging with seeds where needed.
2. Run schema validation and PHPUnit.
3. Smoke test login, refresh, logout, registration, module-disabled route, and dashboard.
4. Confirm production `.env` has explicit CORS origins and required secrets.
5. Apply migrations in production.
6. Deploy application files.
7. Monitor auth failures, 429s, and token reuse events.
