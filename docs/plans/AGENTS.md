# Plans Directory — Agent Guide

> Index of all plans and specs for AI agent navigation.

## Active Plans

### Template Standards Compliance (2026-03-29)
**Goal:** Clear all 29 FAIL and 35 WARN findings from the evaluation audit.
**Index:** [2026-03-29-template-standards-compliance.md](2026-03-29-template-standards-compliance.md)

| Phase | File | Focus |
|-------|------|-------|
| 1 | [template-standards/01-database.md](template-standards/01-database.md) | Migration rewrite, missing tables, FKs, collation |
| 2 | [template-standards/02-security.md](template-standards/02-security.md) | Session hardening, rate limiting, headers, CORS |
| 3 | [template-standards/03-php-modern.md](template-standards/03-php-modern.md) | strict_types, final, readonly, type hints |
| 4 | [template-standards/04-accessibility.md](template-standards/04-accessibility.md) | Skip-to-content, ARIA attributes |
| 5 | [template-standards/05-code-cleanup.md](template-standards/05-code-cleanup.md) | Consolidate PermissionService, remove dead code |
| 6 | [template-standards/06-supply-chain.md](template-standards/06-supply-chain.md) | composer.lock, bundle SweetAlert2 |
| 7 | [template-standards/07-infrastructure.md](template-standards/07-infrastructure.md) | Audit trail, module stubs |

**Execution order:** Phases 1 → 2 → 3 (sequential), then 4-7 (parallel).

**Evaluation source:** [docs/29-march-evaluation/](../29-march-evaluation/)

## Completed Plans

| Date | Plan |
|------|------|
| 2026-02-24 | [Auth Single Source of Truth — Design](2026-02-24-auth-single-source-of-truth-design.md) |
| 2026-02-24 | [Auth Single Source of Truth — Implementation](2026-02-24-auth-single-source-of-truth-impl.md) |
| 2026-02-25 | [Auth SOT Completion Report](2026-02-25-auth-sot-completion.md) |
