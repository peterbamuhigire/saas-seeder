# Plans Directory - Agent Guide

> Index of all plans and specs for AI agent navigation.

## Ownership

- **Owner:** planning owner for spec-driven work and roadmap status.
- **Update trigger:** update this file when the active roadmap changes, plan status changes, phase dependencies change, or plan folder policy changes.

## Current Roadmap

`docs/plans/april-world-class/README.md` is the active April World-Class remediation roadmap as of 2026-04-26. Implementation work should reference the relevant April phase and must not treat older plans as competing active roadmaps.

## Active Plans

### April World-Class Remediation (2026-04-26)

**Goal:** Bring SaaS Seeder from a strong auth/RBAC starter to a 99.99/100 reusable SaaS scaffold using the enhanced skill standards.  
**Index:** [april-world-class/README.md](april-world-class/README.md)

| Phase | File | Focus | Status |
|-------|------|-------|--------|
| 1 | [april-world-class/01/phase.md](april-world-class/01/phase.md) | Governance charter and skill contract map | completed |
| 2 | [april-world-class/02/phase.md](april-world-class/02/phase.md) | Architecture decisions, context map, ADRs | completed |
| 3 | [april-world-class/03/phase.md](april-world-class/03/phase.md) | API contract and runtime foundation | completed |
| 4 | [april-world-class/04/phase.md](april-world-class/04/phase.md) | Token lifecycle and auth endpoint rewrite | completed |
| 5 | [april-world-class/05/phase.md](april-world-class/05/phase.md) | Database and migration governance | completed |
| 6 | [april-world-class/06/phase.md](april-world-class/06/phase.md) | Modular SaaS registry and tenant gates | completed |
| 7 | [april-world-class/07/phase.md](april-world-class/07/phase.md) | Security hardening, rate limiting, HTTP policy | completed |
| 8 | [april-world-class/08/phase.md](april-world-class/08/phase.md) | PHP modernization and service architecture | completed |
| 9 | [april-world-class/09/phase.md](april-world-class/09/phase.md) | UI shell, design system, form UX, primitives | completed |
| 10 | [april-world-class/10/phase.md](april-world-class/10/phase.md) | Automated tests, CI, static analysis, quality gates | completed |
| 11 | [april-world-class/11/phase.md](april-world-class/11/phase.md) | Observability, operations, release evidence | completed |
| 12 | [april-world-class/12/phase.md](april-world-class/12/phase.md) | Final certification, docs sync, score closure | completed |

**Execution order:** Phase 1 -> 2 -> 3/5/8 planning lanes, with Phase 4 on the API critical path, Phase 6 after migration governance, Phase 9 after shell/module decisions, Phase 10 before release evidence, and Phase 12 last.

## Historical Plans

### Template Standards Compliance (2026-03-29)

**Goal:** Clear all 29 FAIL and 35 WARN findings from the evaluation audit.  
**Index:** [2026-03-29-template-standards-compliance.md](2026-03-29-template-standards-compliance.md)  
**Status:** Historical/completed input. Remaining standards concerns are governed by the April World-Class roadmap.

| Phase | File | Focus |
|-------|------|-------|
| 1 | [template-standards/01-database.md](template-standards/01-database.md) | Migration rewrite, missing tables, FKs, collation |
| 2 | [template-standards/02-security.md](template-standards/02-security.md) | Session hardening, rate limiting, headers, CORS |
| 3 | [template-standards/03-php-modern.md](template-standards/03-php-modern.md) | strict_types, final, readonly, type hints |
| 4 | [template-standards/04-accessibility.md](template-standards/04-accessibility.md) | Skip-to-content, ARIA attributes |
| 5 | [template-standards/05-code-cleanup.md](template-standards/05-code-cleanup.md) | Consolidate PermissionService, remove dead code |
| 6 | [template-standards/06-supply-chain.md](template-standards/06-supply-chain.md) | composer.lock, bundle SweetAlert2 |
| 7 | [template-standards/07-infrastructure.md](template-standards/07-infrastructure.md) | Audit trail, module stubs |

**Evaluation source:** [docs/29-march-evaluation/](../29-march-evaluation/)

## Completed Plans

| Date | Plan |
|------|------|
| 2026-03-29 | [Template Standards Compliance](2026-03-29-template-standards-compliance.md) |
| 2026-02-24 | [Auth Single Source of Truth - Design](2026-02-24-auth-single-source-of-truth-design.md) |
| 2026-02-24 | [Auth Single Source of Truth - Implementation](2026-02-24-auth-single-source-of-truth-impl.md) |
| 2026-02-25 | [Auth SOT Completion Report](2026-02-25-auth-sot-completion.md) |
