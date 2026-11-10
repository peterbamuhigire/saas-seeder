# AGENTS.md (Root)

<!-- MODEL-ROUTING-BLOCK v2 2026-11-10 -->
## Model classes and token discipline (Claude Code, Codex and any runner; mandatory)
Name a class here, never a model version. The owner's routing file maps classes to current models (Knowledge Bank, Development, Anthropic Models: `model-routing.json`; handoff AM-01), so a model upgrade needs no edit in this repository.
- **Classes:** **M Mundane** (fastest and cheapest; mechanical work with a clear right answer); **N Normal** (default; executes an approved plan); **X Expert** (strongest standard class; plans, judges, hard debugging, final review); **S Special occasion** (premium class above X; rare; only through the seven-test gate, AM-01 s5, including approved billing). A new model is classified by relative price and vendor positioning (AM-01 s1.1). With no S class available, X is the ceiling.
- **Escalate one class, on a trigger only:** two failed acceptance checks; an undecidable choice; unreviewed money, tenant, security or legal work. Drop back after the hard step.
- **Tokens, both runners:** start-up files at most 8,000 tokens and `AGENTS.md` at most 32 KiB (Codex stops at its byte cap); one task per session, clear or new thread between tasks, compact near 80%; plan to a file, execute in a fresh session (resume a plan from its small `RESUME.md` only); read the router section then one `SKILL.md`, search instead of loading; consult only the engine whose domain matches the task; subagents and tools return summaries, cap tool output (`tool_output_token_limit`), disable unused MCP tools; stable prefix first, no instruction edits mid-session; parallel agents only with a written budget; non-interactive runs for bulk (Batch API only with an approved API budget); effort medium by default, high needs a reason, the maximum needs approval.
- **Class S case for this repository (A-14, AM-02):** none; N maintains the template; keep its instruction file small because children inherit it. Everything else: X plans, N executes, M for mechanical work.
- **Monthly Kaizen, first step:** Anthropic currency check (AM-01 s8) and the token-budget lint; record `Anthropic currency check YYYY-MM-DD: no change|changed`.
<!-- /MODEL-ROUTING-BLOCK -->


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
