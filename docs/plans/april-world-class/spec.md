# April World-Class Spec

## Scope

This spec governs the final remediation stretch for `docs/plans/april-world-class/`, with implementation focus on:

- Phase 11: observability, operations, security review, and release evidence.
- Phase 12: final certification, documentation synchronization, and score closure.

## Objectives

1. Make request correlation, audit coverage, and operational response explicit.
2. Produce a release bundle that links quality gates, rollback posture, and residual risk.
3. Close documentation drift across onboarding, API, operations, and planning entry points.
4. Certify the April roadmap against the 99.99 scoring model or document any accepted exception.

## In Scope

- `src/Observability/` runtime primitives.
- Audit enrichment for auth, module, permission-override, password-change, and migration flows.
- Operations, release, security, testing, and certification markdown under `docs/`.
- Phase evidence and roadmap status updates.

## Validation

- `.\scripts\quality\check.ps1`
- `composer check` when PHP and Composer are available in the shell PATH
- Manual review of the release checklist, rollback plan, and incident runbooks

## Exit Criteria

- Phase 11 evidence exists and links concrete validation output.
- Phase 12 certification docs exist and reconcile roadmap state.
- README and `docs/overview/README.md` guide a new team from setup through quality gates.
