# Phase 11: Observability, Operations, And Release Evidence

## Objective

Make the scaffold operable. Every critical flow should have logs, audit events, runbooks, SLOs, release checks, rollback posture, and evidence artifacts.

## Skills Applied

- `system-architecture-design`
- `api-design-first`
- `database-design-engineering`
- `skill-composition-standards`

## Current Problems

- Audit table/service exists, but audit event coverage is not proven.
- No release evidence bundle exists.
- No SLO/alert plan exists.
- No runbook exists for setup, migrations, auth incidents, or rollback.
- No structured request ID propagation is documented.

## Deliverables

Create:

- `src/Observability/RequestContext.php`
- `src/Observability/Logger.php`
- `src/Observability/AuditEvent.php`
- `docs/operations/runbook.md`
- `docs/operations/slo-alert-plan.md`
- `docs/operations/incident-response.md`
- `docs/operations/auth-incident-runbook.md`
- `docs/operations/migration-runbook.md`
- `docs/release/release-plan.md`
- `docs/release/rollback-plan.md`
- `docs/release/release-checklist.md`
- `docs/release/evidence/2026-04-world-class-remediation.md`
- `docs/security/threat-model.md`
- `docs/security/security-review-checklist.md`
- `docs/testing/test-completion-report.md`
- `docs/api/observability-notes.md`

## Work Breakdown

1. Define request ID propagation:
   - generated in API runtime,
   - included in responses,
   - logged with errors,
   - added to audit details where useful.
2. Define audit event catalog:
   - login success/failure,
   - lockout,
   - password change,
   - token refresh,
   - token reuse detection,
   - logout,
   - logout-all,
   - permission override,
   - module enable/disable,
   - migration applied.
3. Define SLOs:
   - auth availability,
   - API response latency,
   - login error rate,
   - migration failure rate,
   - security event thresholds.
4. Create runbooks:
   - local setup,
   - failed migration,
   - account lockout spike,
   - suspected token theft,
   - CORS misconfiguration,
   - rollback.
5. Create release evidence template.
6. Record actual evidence from Phase 10:
   - PHP version,
   - Composer validation,
   - migration checks,
   - tests,
   - static analysis,
   - OpenAPI validation,
   - manual smoke results.

## Acceptance Criteria

- Every critical flow has audit/observability notes.
- Every release has a filled evidence file.
- Every high-risk failure mode has a runbook.
- Rollback plan exists before deployment.
- Skipped checks require owner, reason, and expiry date.

## Validation

Run:

```powershell
Test-Path docs\release\evidence\2026-04-world-class-remediation.md
rg -n "request_id|audit|SLO|rollback|runbook" docs\operations docs\release docs\api
```

Manual review:

- Pick one incident scenario and follow the runbook.
- Pick one release gate failure and verify it blocks certification.

## Sub-Agent Use

Use an operations/documentation worker for runbooks. Use a verification worker to cross-check that every critical flow from Phase 02 has evidence.

## Exit Gate

Final certification cannot begin without a complete release evidence bundle.

