# SaaS Seeder Documentation Overview

This is the documentation landing page for the production-ready SaaS auth and RBAC scaffold. Use it to move from local setup to architecture, API, operations, and release evidence without guessing where material lives.

## Start Here

1. Read the active roadmap: `docs/plans/april-world-class/README.md`
2. Install dependencies and set up the database:

   ```powershell
   composer install
   .\scripts\setup\setup-database.ps1
   .\scripts\server\start-server.ps1
   ```

3. Run the quality gate before changes or release work:

   ```powershell
   .\scripts\quality\check.ps1
   ```

## Documentation Map

- `docs/architecture/`: context maps, flows, ADRs, and failure modes
- `docs/api/`: OpenAPI, auth model, error model, and observability notes
- `docs/data/`: schema governance, invariants, rollback policy, and migration guidance
- `docs/design-system/`: UI shell, components, tokens, and state patterns
- `docs/operations/`: runbooks, SLOs, incident response, and quality-gate operations
- `docs/release/`: release plan, rollback posture, checklist, and evidence
- `docs/testing/`: test plan, coverage baseline, and completion report
- `docs/plans/`: active roadmap, specs, and evidence notes

## Script Layout

- `scripts/setup/`: installation and bootstrap helpers
- `scripts/db/`: migration, schema validation, and seeding
- `scripts/server/`: local server start commands
- `scripts/quality/`: lint, analysis, and test automation
- `scripts/utils/`: repository utilities

## Governance

- Root markdown is restricted to `README.md`, `CLAUDE.md`, and `AGENTS.md`.
- New implementation work should start from a module spec under `docs/plans/<module>/spec.md`.
- Update the nearest domain `AGENTS.md` when policy, ownership, or review gates change.
