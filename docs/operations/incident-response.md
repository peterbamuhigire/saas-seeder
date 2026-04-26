# Incident Response

## Severity Model

- `SEV-1`: authentication unavailable, active token abuse, or cross-tenant exposure.
- `SEV-2`: degraded login or refresh performance, elevated lockouts, or broken release gates.
- `SEV-3`: documentation drift, non-blocking tooling failures, or noisy alerts without user impact.

## Initial Response

1. Record start time, owner, and current customer impact.
2. Capture the `request_id`, endpoint, tenant or franchise, and affected environment.
3. Classify the incident severity and open the matching runbook.
4. Freeze risky deploys until containment is confirmed.

## Evidence To Capture

- Exact command or request used to reproduce.
- Relevant `request_id` values from API responses or logs.
- Audit event names, affected users, and franchise scope.
- Schema or deployment revision involved.
- Mitigation, residual risk, and next review date.

## Closure

An incident is closed only when:

- customer impact is resolved or mitigated,
- release posture is re-evaluated,
- follow-up work is linked to `docs/plans/`,
- the relevant evidence or exception document is updated.
