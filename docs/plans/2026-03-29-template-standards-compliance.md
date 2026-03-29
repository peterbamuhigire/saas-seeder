# Template Standards Compliance — Implementation Plan

**Goal:** Clear all 29 FAIL and 35 WARN findings from the 2026-03-29 evaluation, bringing the SaaS Seeder template to full compliance with all 10 skill standards.

**Architecture:** Static skills approach. All changes are to the template codebase directly. No sub-agents needed — this is code remediation, not feature development.

**Tech Stack:** PHP 8.x, MySQL 8.x, Tabler/Bootstrap 5, firebase/php-jwt

**Specification:** `docs/29-march-evaluation/README.md` (full audit results)

---

## Execution Strategy

**7 phases, dependency-ordered.** Phases 1-3 are sequential (each depends on the prior). Phases 4-7 are independent and can run in parallel.

```
Phase 1: Database Foundation ──→ Phase 2: Security Hardening ──→ Phase 3: PHP Modernization
                                                                          │
Phase 4: Accessibility (parallel) ─────────────────────────────────────────┤
Phase 5: Code Cleanup (parallel) ──────────────────────────────────────────┤
Phase 6: Supply Chain (parallel) ──────────────────────────────────────────┤
Phase 7: Infrastructure Stubs (parallel) ──────────────────────────────────┘
```

## Phase Summary

| Phase | Focus | FAILs Cleared | WARNs Cleared | Files | Est. |
|-------|-------|---------------|---------------|-------|------|
| [1: Database](./template-standards/01-database.md) | migration.sql rewrite, missing tables, FKs, collation | 8 | 3 | 2 | M |
| [2: Security](./template-standards/02-security.md) | Session hardening, rate limiting, headers, CORS, secrets | 9 | 5 | 6 | L |
| [3: PHP Modern](./template-standards/03-php-modern.md) | strict_types, final, readonly, type hints, constructor promotion | 6 | 1 | ~20 | M |
| [4: Accessibility](./template-standards/04-accessibility.md) | Skip-to-content, aria-required, aria-live, role="alert" | 6 | 0 | 7 | S |
| [5: Code Cleanup](./template-standards/05-code-cleanup.md) | Consolidate PermissionService, remove dead code, fix naming | 0 | 10 | ~15 | M |
| [6: Supply Chain](./template-standards/06-supply-chain.md) | composer.lock, SRI hashes, bundle SweetAlert2 | 2 | 0 | 5 | S |
| [7: Infrastructure](./template-standards/07-infrastructure.md) | Audit trail, module stubs, code quality tooling | 1 | 4 | 8 | M |
| **Total** | | **29** | **23** | | |

**Remaining 12 WARNs** are informational (e.g., "no navigation in footer", "no icons in nav links") — acceptable for a template and explicitly deferred.

---

## Status Tracking

| Phase | Status | Started | Completed |
|-------|--------|---------|-----------|
| 1: Database | not-started | — | — |
| 2: Security | not-started | — | — |
| 3: PHP Modern | not-started | — | — |
| 4: Accessibility | not-started | — | — |
| 5: Code Cleanup | not-started | — | — |
| 6: Supply Chain | not-started | — | — |
| 7: Infrastructure | not-started | — | — |

---

## Deferred WARNs (Acceptable for Template)

These 12 WARNs are documented but intentionally not fixed:

| # | WARN | Reason for Deferral |
|---|------|-------------------|
| W1 | Floating labels on sign-in | Functional, cosmetic preference |
| W2 | No icons in nav links | Template has minimal nav; project-specific |
| W3 | No navigation in footer | Template footer is intentionally minimal |
| W4 | Form has no action/method (forgot-password) | JS intercepts; placeholder page |
| W5 | Application DB user not root | Dev-only default; documented in security checklist |
| W6 | Verify token returned in register response | Documented as placeholder; commented in code |
| W7 | `tbl_distributors` referenced but missing | Project-specific table; removed in Phase 5 |
| W8 | Database not singleton | Acceptable for template; noted for projects |
| W9 | Nav link `$currentPage` redeclared | Harmless redundancy |
| W10 | Unescaped icon in access-denied | Fixed in Phase 4 as defence-in-depth |
| W11 | Password trimming | Addressed in Phase 2 |
| W12 | Member panel empty state no CTA | Fixed in Phase 4 |
