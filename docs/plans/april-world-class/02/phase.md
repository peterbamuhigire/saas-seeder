# Phase 02: Architecture Decisions, Context Map, And ADRs

## Objective

Define the scaffold as a coherent system with explicit boundaries, critical flows, and architectural decisions. This phase prevents API, UI, module, and data work from solving different versions of the same problem.

## Skills Applied

- `system-architecture-design`
- `skill-composition-standards`
- `api-design-first`
- `database-design-engineering`
- `modular-saas-architecture`

## Current Problems

- The token model is split: `TokenService` behaves like DB-backed short-lived JWT sessions, while refresh/logout endpoints expect missing refresh-token infrastructure.
- Module boundaries are implied by folders, not documented as contexts.
- Critical flows lack a single table covering validation, tenant source, authz, audit, failure, and observability.
- ADRs do not capture expensive decisions such as token lifecycle, module registry, migration governance, and UI shell architecture.

## Deliverables

Create:

- `docs/architecture/context-map.md`
- `docs/architecture/container-map.md`
- `docs/architecture/module-boundaries.md`
- `docs/architecture/critical-flows.md`
- `docs/architecture/auth-token-lifecycle.md`
- `docs/architecture/failure-modes.md`
- `docs/architecture/dependency-view.md`
- `docs/architecture/adr/0001-auth-token-model.md`
- `docs/architecture/adr/0002-api-runtime-contract.md`
- `docs/architecture/adr/0003-migration-governance.md`
- `docs/architecture/adr/0004-module-registry-model.md`
- `docs/architecture/adr/0005-ui-shell-contract.md`
- `docs/architecture/adr/0006-quality-gate-model.md`

## Work Breakdown

1. Define actors:
   - super admin,
   - franchise owner,
   - staff,
   - member/end user,
   - API client,
   - setup operator,
   - AI coding agent.
2. Define bounded contexts:
   - Auth,
   - Tenant/Franchise,
   - RBAC,
   - Module Registry,
   - API Runtime,
   - UI Shell,
   - Database/Migrations,
   - Operations/Release.
3. For each critical flow, document:
   - entry point,
   - auth mode,
   - tenant source,
   - permission/module checks,
   - transaction boundary,
   - audit events,
   - error model,
   - observability fields,
   - retry/idempotency posture.
4. Write ADRs before code decisions:
   - choose access + rotating opaque refresh tokens or remove refresh endpoints,
   - choose API response/error envelope,
   - choose migration ledger model,
   - choose module table design,
   - choose shared shell and UI primitives.
5. Add a dependency view that makes implementation order explicit.

## Acceptance Criteria

- Every public route and API endpoint maps to a critical flow.
- Every critical flow states where tenant identity comes from.
- Token lifecycle ADR resolves the refresh/logout inconsistency.
- Module registry ADR defines tables, service boundaries, route guarding, and menu gating.
- UI shell ADR defines whether pages render through shared PHP components/includes.
- ADRs are referenced from later phase docs.

## Validation

Manual review:

- Confirm no ADR says "TBD" for a blocking implementation choice.
- Confirm each later phase has an ADR dependency listed.
- Confirm architecture docs distinguish web session auth from API bearer auth.

Static check:

```powershell
rg -n "TBD|TODO|to be decided" docs\architecture
rg -n "0001-auth-token-model|0002-api-runtime-contract|0003-migration-governance|0004-module-registry-model" docs\plans\april-world-class
```

## Sub-Agent Use

Use an architecture explorer to review ADR coherence and another API/security explorer to challenge the token model ADR.

## Exit Gate

Phase 03 and Phase 04 cannot begin until ADRs `0001` and `0002` are accepted.

