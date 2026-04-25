# ADR-0006: Quality Gate Model

Status: Accepted  
Date: 2026-04-26  
Phase: April World-Class Phase 02

## Context

The remediation plan targets a reusable, production-ready scaffold. Later phases need a consistent definition of validation across docs, migrations, API contracts, auth flows, PHP modernization, UI shell, and release evidence.

## Decision

Use layered quality gates with phase evidence. A phase is complete only when its owned artifacts pass static checks, targeted tests or manual verification, and evidence is recorded in the phase directory or operations evidence path.

## Gate Layers

| Layer | Required evidence |
|---|---|
| Documentation | No unresolved placeholders; ADR dependencies linked; route and context maps updated for behavior changes. |
| API contract | OpenAPI or documented JSON examples; envelope tests for success and failure. |
| Security | Auth, CSRF, CORS, token, permission, module, and rate-limit negative cases. |
| Database | Migration ledger status, checksum/drift check, rollback posture. |
| PHP quality | Syntax checks, static analysis where configured, focused unit/feature tests. |
| UI quality | Shared shell render checks, accessibility checks, responsive screenshots for changed surfaces. |
| Release | Evidence note, known risks, rollback steps, and final scorecard update. |

## Consequences

- Phase 10 implements automation for repeatable gates.
- Earlier phases still record the validation commands they can run.
- Final certification in Phase 12 depends on evidence, not only code presence.

## Rejected Alternatives

| Alternative | Reason rejected |
|---|---|
| Manual review only | Insufficient for reusable auth/RBAC infrastructure. |
| CI-only with no phase evidence | CI output alone does not capture architectural decisions, known risks, or rollback posture. |
| Defer quality gates to final phase | Late discovery would make earlier implementation phases expensive to unwind. |
