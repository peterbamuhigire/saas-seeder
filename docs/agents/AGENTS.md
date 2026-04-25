# Documentation Agents Guide

## Ownership

- **Owner:** documentation owner for SaaS Seeder governance.
- **Update trigger:** update this file when documentation structure, landing pages, AGENTS hierarchy, naming conventions, or doc review workflow changes.

## Scope

This guide governs documentation policy under `docs/`. The root `AGENTS.md` remains the project-wide source for identity, stack, security, and script rules.

## Documentation Rules

- Keep markdown inside `docs/` except for the root files explicitly allowed by `AGENTS.md`.
- Use `docs/overview/README.md` as the canonical documentation landing page.
- Keep module-specific policy in the nearest `docs/<domain>/AGENTS.md`.
- Link structural documentation changes from the relevant plan or evidence note.
- Do not move or rename large documentation areas without updating `docs/overview/README.md`, `docs/plans/AGENTS.md`, and `docs/plans/INDEX.md`.

## Review Checklist

- New or changed docs have a clear owner and update trigger when they define policy.
- Links to AGENTS files point to files that exist.
- Current roadmap status points to `docs/plans/april-world-class/README.md`.
- Historical plans are marked historical/completed and do not compete with the active roadmap.
