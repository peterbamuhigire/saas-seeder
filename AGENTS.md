# AGENTS.md (Root)

## Project Identity

- **Project Name:** SaaS Seeder Template
- **Mission:** Provide a production-ready authentication + RBAC starter so teams can ship secure SaaS products without rebuilding the same stack.
- **Primary Domain:** SaaS authentication infrastructure with multi-tenant readiness
- **Key Stakeholders:** Engineering teams standing up SaaS prototypes, architects reviewing auth flows, and product owners demanding production-ready templates.

## Tech Stack

- **Language:** PHP 8.3 (PSR-4 compliant)
- **Framework:** Custom PHP modules plus Tabler-based UI and Composer-managed libraries
- **Database:** MySQL 8.0 (utf8mb4 + stored procedures)
- **Infra/Deployment:** Local WAMP for development, deployable to standard LAMP or containerized environments
- **Services:** Composer, MySQL, PHP built-in server (dev), Tabler assets, CLAUDE skills

## Global Standards

### Engineering Principles

- Favor clarity over cleverness; the auth flow has multiple consumers (web UI, API, CLI) and must remain readable.
- Keep module boundaries explicit (`public/`, `api/`, `src/`, `docs/`, `scripts/`).
- Treat migrations, scripts, and documentation as a single narrative; document every change before merging.

### Documentation & Script Rules

- Markdown lives only under `docs/` subdirectories with the canonical landing at `docs/overview/README.md`. The only root markdown files are `README.md`, `CLAUDE.md`, and this `AGENTS.md`.
- Track doc policy shifts via `docs/agents/AGENTS.md`, planning rules via `docs/plans/AGENTS.md`, and data governance via `docs/data/AGENTS.md` before merging structural updates.

Scripts used by developers live inside `scripts/<category>/` (setup, db, server, utils). Always call them as `.\scripts/<category>/<script>.ps1` so the hierarchy stays discoverable for future contributors.

### Security & Compliance

- Validate inputs at the edge and encode all output rendered to users.
- Keep secrets in `.env` or a vault; never log them in plaintext.
- Sessions use HttpOnly + Secure cookies, and RBAC helpers enforce permissions consistently.

## Execution Guidance

- Confirm the stack (PHP 8.3, Composer dependencies, MySQL 8) before major refactors.
- Everything should be spec-driven: reference `docs/plans/AGENTS.md` and create a structured `docs/plans/<module>/spec.md` before coding.
- Link every change to a plan or ticket, refresh the relevant AGENTS file, and run the checks described in `skills/update-claude-documentation/SKILL.md` to keep terminology consistent.
