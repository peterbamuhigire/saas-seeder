# Release Checklist

- Verify `.\scripts\quality\check.ps1` passed on the release candidate.
- Verify `composer check` passed when PHP and Composer are available on PATH.
- Confirm `.\scripts\db\validate-schema.ps1` passed against the target schema.
- Review `docs/security/security-review-checklist.md`.
- Review `docs/operations/slo-alert-plan.md`.
- Review `docs/release/rollback-plan.md`.
- Smoke test login, refresh, logout, logout-all, module-disabled routing, dashboard, and memberpanel.
- Record reviewer, date, commands, and residual risk in the evidence bundle.
- Any skipped gate must include owner, reason, and expiry date.
