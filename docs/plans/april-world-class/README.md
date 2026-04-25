# April World-Class Remediation Plan

**Goal:** raise SaaS Seeder from a strong auth/RBAC starter to a 99.99/100 reusable SaaS scaffold.  
**Target path:** `docs/plans/april-world-class/`  
**Baseline:** `docs/april-2026-analysis/README.md` plus sub-agent reviews for API/security, UI/UX, and architecture/data/release governance.  
**Execution model:** spec-driven, evidence-producing, phase-gated. No implementation phase is complete until its validation gates pass and its evidence artifacts are written.
**Owner:** remediation lead for April phases 01-12.  
**Update trigger:** refresh this index when phase scope, dependencies, score rules, owners, or acceptance evidence changes.

## Score Target

99.99/100 does not mean "no bugs are possible." It means every major class of failure has a designed control:

- Runtime contracts exist before code.
- Auth, API, database, UI, and module behaviours are testable.
- Security-critical flows have unit, feature, and negative tests.
- Migrations are governed and reversible by policy.
- UI patterns are reusable instead of page-local.
- Releases have evidence, rollback posture, and known-risk signoff.
- Enhanced skills map to concrete repo artifacts.

## Sub-Agent Inputs Used

| Agent | Focus | Key contribution |
|---|---|---|
| Lagrange | API/security | Token/session mismatch, refresh/logout broken runtime, rate limiting, HTTP headers, security test matrix. |
| Poincare | UI/UX | Shell contract, design tokens, form UX, reusable PHP/Tabler primitives, accessibility QA. |
| Popper | Architecture/data/release | Governance baseline, ADRs, migration governance, module registry, CI/tooling, release evidence. |

## Phase Index

| Phase | Directory | Objective summary | Primary deliverables | Validation gate | Depends on |
|---:|---|---|---|---|---|
| 1 | [01/phase.md](01/phase.md) | Establish governance, scoring, doc-domain policy, and skill contracts. | Charter docs, scorecard, dependency map, risk register, domain AGENTS files. | Phase 01 `Test-Path` and `rg` checks plus owner/update-trigger review. | None |
| 2 | [02/phase.md](02/phase.md) | Define architecture boundaries, critical flows, and blocking ADRs. | Context/container maps, flow docs, ADRs `0001`-`0006`. | ADRs have no TBD blocking choices and later phases link to them. | 1 |
| 3 | [03/phase.md](03/phase.md) | Make API behavior contract-driven and executable. | OpenAPI, auth/error/rate-limit docs, API runtime classes. | API lint/smoke checks prove JSON envelope and request IDs. | 1, 2 |
| 4 | [04/phase.md](04/phase.md) | Resolve token lifecycle consistency across login, refresh, logout, and storage. | Token services, token migration, endpoint rewrites, token lifecycle tests. | Auth endpoint scans, PHP lint, and feature tests are green. | 2, 3 |
| 5 | [05/phase.md](05/phase.md) | Govern database migrations, schema invariants, and rollback posture. | Migration ledger, data docs, schema checks, runtime DDL removal. | Migration and schema validation gates pass. | 2, Phase 4 token decisions |
| 6 | [06/phase.md](06/phase.md) | Add modular SaaS registry and tenant-aware gates. | Registry tables/services, manifest contract, route/menu guarding. | Disabled-module and tenant-gate tests pass. | 5 |
| 7 | [07/phase.md](07/phase.md) | Harden HTTP security, rate limiting, and authorization policy. | Header policy, limiter, CORS policy, threat model updates. | Security tests and header scans pass. | 3, 4, 5 |
| 8 | [08/phase.md](08/phase.md) | Modernize PHP services under test protection. | Typed services/DTOs, Composer scripts, static analysis baseline. | PHP lint, static analysis, and protected refactor tests pass. | 2, 3, 5 |
| 9 | [09/phase.md](09/phase.md) | Productize UI shell, design tokens, forms, and state patterns. | Shared shell, UI primitives, design docs, accessibility checks. | UI state, keyboard, contrast, and smoke checks pass. | 1, 2, Phase 6 interface |
| 10 | [10/phase.md](10/phase.md) | Build automated quality gates for the scaffold. | PHPUnit coverage, CI scripts, lint/static-analysis gate. | One-command local check and CI-equivalent evidence pass. | 3-9 progressively |
| 11 | [11/phase.md](11/phase.md) | Produce operations, observability, and release evidence. | Runbooks, SLOs, release checklist, rollback evidence. | Release evidence bundle is complete and reviewed. | 3-10 |
| 12 | [12/phase.md](12/phase.md) | Certify final score, close docs drift, and record exceptions. | Final scorecard, artifact index, known exceptions, closure notes. | Final stale-text scan and 99.99 certification evidence pass. | 1-11 |

## Critical Path

1. Decide the token model.
2. Stabilize the API runtime.
3. Govern database migrations.
4. Implement module registry.
5. Add quality gates and tests.
6. Productize UI shell and state system.
7. Produce release evidence and final certification.

## Global Definition of Done

Every phase must produce:

- A completed phase evidence note under its directory.
- Updated implementation docs where behaviour changes.
- Test, lint, static analysis, or manual verification evidence as appropriate.
- No stale plan status left behind.
- Cross-links to related ADRs, specs, and release evidence.

## Phase Ownership Model

Use sub-agents during implementation exactly where they create parallel value:

- **API/security worker:** Phases 3, 4, 7, and API tests in 10.
- **Data/architecture worker:** Phases 2, 5, 6, and release artifacts in 11.
- **UI/UX worker:** Phase 9 and UI/accessibility tests in 10.
- **Verification worker:** independent review after Phases 4, 7, 9, 10, and 12.

Workers must have disjoint write scopes when running in parallel.
