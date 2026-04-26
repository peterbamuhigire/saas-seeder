# Release Plan

1. Run `.\scripts\quality\check.ps1`.
2. Run `composer check` when the shell has PHP and Composer on PATH.
3. Apply migrations in staging with `.\scripts\db\migrate.ps1`.
4. Run `.\scripts\db\validate-schema.ps1`.
5. Smoke test login, refresh, logout, logout-all, registration, module-disabled route, dashboard, and memberpanel.
6. Confirm production `.env` has explicit CORS origins and required secrets.
7. Review `docs/release/rollback-plan.md` and `docs/security/security-review-checklist.md`.
8. Apply migrations in production.
9. Deploy application files.
10. Monitor auth failures, 429s, token reuse detections, and migration audit events.
