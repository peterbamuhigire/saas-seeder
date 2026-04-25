# Decision Log

**Owner:** remediation lead until an ADR owner is assigned.  
**Update trigger:** add or update a row before any decision changes code, schema, security behavior, API contract, UI shell behavior, release gate, or documentation policy.

This file tracks remediation decisions before they become full ADRs.

| Date | Decision | Status | Owner | ADR |
|---|---|---|---|---|
| 2026-04-26 | Use `docs/plans/april-world-class/` as the temporary April remediation plan path until the coordinated path rename is performed. | accepted | remediation lead | n/a |
| 2026-04-26 | Split plan into 12 phases with one directory per phase. | accepted | remediation lead | n/a |
| 2026-04-26 | Treat April World-Class as the active roadmap and the March template standards plan as historical/completed remediation input. | accepted | remediation lead | n/a |
| 2026-04-26 | Use access + rotating opaque refresh tokens as the recommended SaaS API token model unless ADR rejects it. | proposed | API/security owner | `0001-auth-token-model.md` |
| 2026-04-26 | Treat OpenAPI/error/auth artifacts as required before API endpoint rewrites. | proposed | API owner | `0002-api-runtime-contract.md` |
| 2026-04-26 | Treat module registry as first-class scaffold infrastructure, not optional future app code. | proposed | architecture owner | `0004-module-registry-model.md` |
| 2026-04-26 | Keep Tabler as the UI base and create seeder-specific PHP primitives/tokens around it. | proposed | UI owner | `0005-ui-shell-contract.md` |

## Promotion Rule

Any proposed decision that affects code, schema, security, API contracts, release gates, or UI shell contracts must be promoted to an ADR before implementation.
