# SaaS Seeder Documentation Overview

This folder is the canonical landing page for everything that used to live in `README.md`, plus the documentation policies enforced by the documentation skills. Treat this doc as the navigator: every other guide lives inside a semantic `docs/` subdirectory.

## What lives where?

- `docs/overview/` – this landing page plus supporting narrative copy.
- `docs/guides/` – onboarding/checklists like `NEXT-STEPS.md` and the authentication deep dive.
- `docs/api/` – the API reference and any future API-specific content.
- `docs/reference/` – quick lookups such as this file and other cheat sheets.
- `docs/operations/` – runbooks, progress trackers, and status updates (currently `SETUP-PROGRESS.md`).
- `docs/implementation/` – implementation notes (session prefix design, system walkthroughs).
- `docs/summaries/` – status summaries, completion notes, and interface fixes.
- `docs/data/` – data governance guidance and schema rules (see `AGENTS.md`).
- `docs/agents/` – documentation policy and AGENTS references.
- `docs/plans/` – spec-driven planning folders organized by module.
- `docs/seeder-template/` – template-specific assets (migration scripts, copy checklists).

## Getting started (developer quick-start)

1. Install the PHP dependencies:

   ```bash
   composer install
   ```

2. Run the relocated database setup script:

   ```powershell
   .\scripts\setup\setup-database.ps1
   ```

3. Start the development server:

   ```powershell
   .\scripts\server\start-server.ps1
   ```

## Script catalog (all scripts live under `scripts/`)

- `scripts/setup/` – installation helpers such as `install-dependencies.ps1` and `setup-database.ps1`.
- `scripts/db/` – development database assistants (`seed.ps1`, `fix-database.ps1`).
- `scripts/server/` – the PHP server launcher (`start-server.ps1`).
- `scripts/utils/` – utilities and tooling (`dir_map.ps1`).

Include a new script? Choose the appropriate `scripts/<category>/` folder so discovery stays predictable.

## Documentation governance

- **Doc creation rule:** No markdown at the repository root except `README.md` and `CLAUDE.md`. Every new guide, spec, or summary branches off `docs/` inside a descriptive folder.
- **Doc update order:** Follow the specific→general order described in `skills/update-claude-documentation/SKILL.md` (tech specs → architecture → CLAUDE → README → brief).
- **AGENTS synchrony:** Update `docs/agents/AGENTS.md`, `docs/data/AGENTS.md`, and `docs/plans/AGENTS.md` whenever documentation or planning policies change. The root `AGENTS.md` captures the big-picture standards.
- **Spec-driven work:** Place specs in `docs/plans/<module>/spec.md` following the structure in `docs/plans/AGENTS.md`. New modules should add their own subfolder and refresh the folder map.

## AGENTS map

- `[AGENTS.md](../AGENTS.md)` (repo root) – global project identity, tech stack, and documentation directives.
- `docs/agents/AGENTS.md` – documentation-specific policies, hierarchy rules, and the update workflow for future contributors.
- `docs/data/AGENTS.md` – data governance, migration checklists, and validation expectations for the MySQL schema.
- `docs/plans/AGENTS.md` – spec-driven workflow, folder map, and plan templates for each module.

## Next steps for contributors

- Add or alter docs only inside the appropriate `docs/<module>/` folder and describe the change in `docs/agents/AGENTS.md` if it affects policy.
- Run `skills/doc-architect` (per the instructions under `skills/doc-architect/SKILL.md`) when the structure changes enough to warrant regenerating the Triple-Layer AGENTS set.
- Reference this overview whenever you link to documentation from code, scripts, or other artifacts.
