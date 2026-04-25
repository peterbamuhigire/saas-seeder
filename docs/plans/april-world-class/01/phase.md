# Phase 01: Governance Charter And Skill Contract Map

## Objective

Create the governance baseline for the world-class remediation effort before implementation begins. This phase turns the enhanced skills into repo-native expectations so the remaining phases do not drift into disconnected fixes.

## Skills Applied

- `skill-composition-standards`
- `system-architecture-design`
- `database-design-engineering`
- `api-design-first`
- `php-modern-standards`
- `webapp-gui-design`
- `design-audit`
- `form-ux-design`
- `modular-saas-architecture`

## Current Problems

- Root documentation references `docs/agents/AGENTS.md` and `docs/data/AGENTS.md`, but those files are absent.
- The March template standards plan is marked completed while some downstream docs still describe stale states.
- There is no single remediation charter that defines how 99.99/100 will be measured.
- Enhanced skill outputs are not mapped to concrete repository artifacts.

## Deliverables

Create or update:

- `docs/plans/april-world-class/README.md`
- `docs/plans/april-world-class/scorecard.md`
- `docs/plans/april-world-class/dependency-map.md`
- `docs/plans/april-world-class/decision-log.md`
- `docs/plans/april-world-class/skill-contract-map.md`
- `docs/plans/april-world-class/risk-register.md`
- `docs/agents/AGENTS.md`
- `docs/data/AGENTS.md`
- `docs/architecture/AGENTS.md`
- `docs/api/AGENTS.md`
- `docs/testing/AGENTS.md`
- `docs/release/AGENTS.md`
- Update `docs/plans/AGENTS.md`
- Update `docs/plans/INDEX.md`

## Work Breakdown

1. Define the 99.99 score rubric.
2. Convert April findings into measurable acceptance criteria.
3. Create a phase dependency map with critical path and parallel lanes.
4. Create a skill contract map:
   - architecture artifacts,
   - data artifacts,
   - API artifacts,
   - security artifacts,
   - UI artifacts,
   - testing artifacts,
   - release artifacts.
5. Add AGENTS files for missing doc domains.
6. Mark the March plan as historical/completed and point current work to this plan.
7. Create a risk register with severity, owner, mitigation, and expiry.

## Acceptance Criteria

- No documentation index references a missing AGENTS file.
- Every phase in this plan has objective, deliverables, tasks, validation gates, and dependencies.
- Every enhanced skill expectation maps to at least one artifact path.
- `docs/plans/AGENTS.md` names this plan as the current active world-class roadmap.
- `scorecard.md` explains how 99.99/100 is calculated.

## Validation

Run:

```powershell
rg -n "docs/(agents|data|architecture|api|testing|release)/AGENTS.md" docs AGENTS.md
Test-Path docs\agents\AGENTS.md
Test-Path docs\data\AGENTS.md
Test-Path docs\architecture\AGENTS.md
Test-Path docs\api\AGENTS.md
Test-Path docs\testing\AGENTS.md
Test-Path docs\release\AGENTS.md
```

Manual review:

- Confirm every new governance doc has a clear owner and update trigger.
- Confirm this plan is linked from the plans index.

## Sub-Agent Use

Use one explorer to review documentation references and dead links. Use another explorer to validate the skill contract map against the enhanced skills.

## Exit Gate

No implementation phases begin until this phase is merged and linked from `docs/plans/AGENTS.md`.

