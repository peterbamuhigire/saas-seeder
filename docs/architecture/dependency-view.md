# Dependency View

Phase: April World-Class Phase 02  
Status: Accepted architecture baseline

## Implementation Order

| Order | Dependency | Enables | ADR |
|---:|---|---|---|
| 1 | Token model decision | API auth contract, refresh/logout rewrite, security tests. | [ADR-0001](adr/0001-auth-token-model.md) |
| 2 | API runtime envelope and middleware contract | OpenAPI work, endpoint rewrites, negative tests, observability fields. | [ADR-0002](adr/0002-api-runtime-contract.md) |
| 3 | Migration ledger policy | Refresh-token tables, module tables, repeatable seed governance. | [ADR-0003](adr/0003-migration-governance.md) |
| 4 | Module registry schema and service contract | Tenant route guards, menu gating, module UX, module tests. | [ADR-0004](adr/0004-module-registry-model.md) |
| 5 | UI shell contract | Shared panel includes, primitives, page migration, accessibility checks. | [ADR-0005](adr/0005-ui-shell-contract.md) |
| 6 | Quality gate model | CI/static analysis/test evidence and final certification. | [ADR-0006](adr/0006-quality-gate-model.md) |

## Phase Dependencies

| Phase | Requires |
|---|---|
| 03 API contract and runtime foundation | ADR-0001 and ADR-0002. |
| 04 Token lifecycle and auth endpoint rewrite | ADR-0001, ADR-0002, and migration posture from ADR-0003. |
| 05 Database and migration governance | ADR-0003 and token table requirements from ADR-0001. |
| 06 Modular SaaS registry and tenant gates | ADR-0003 and ADR-0004. |
| 07 Security hardening, rate limiting, HTTP policy | ADR-0001 and ADR-0002. |
| 08 PHP modernization and service architecture | ADR-0002 and ADR-0006. |
| 09 UI shell, design system, form UX, primitives | ADR-0004 and ADR-0005. |
| 10 Automated tests, CI, static analysis, quality gates | ADR-0001 through ADR-0006. |
| 11 Observability, operations, release evidence | ADR-0002, ADR-0003, and ADR-0006. |
| 12 Final certification, docs sync, score closure | ADR-0001 through ADR-0006 and all phase evidence. |

## Cross-Context Dependencies

| Producer | Consumer | Blocking contract |
|---|---|---|
| Auth | API Runtime | Access-token verifier, refresh-token rotation service, auth error codes. |
| Auth | UI Shell | Session keys, logout behavior, forced-password-change signal. |
| Tenant/Franchise | RBAC | Tenant id and permission version for scoped checks. |
| RBAC | Auth | Permission version increments invalidate access tokens. |
| Module Registry | UI Shell | Menu visibility and route metadata. |
| Module Registry | API Runtime | Route-to-module mapping and permission metadata. |
| Database/Migrations | Auth | `tbl_user_sessions`, `tbl_refresh_tokens`, stored procedures. |
| Database/Migrations | Module Registry | Module and tenant enablement tables. |
| Operations/Release | All contexts | Required validation evidence and rollback posture. |
