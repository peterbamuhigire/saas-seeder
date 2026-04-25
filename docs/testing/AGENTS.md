# Testing Agents Guide

## Ownership

- **Owner:** testing/tooling owner for automated quality gates and evidence.
- **Update trigger:** update this file when test layers, CI gates, local check scripts, static-analysis policy, or release evidence requirements change.

## Scope

This guide governs testing documentation, quality gates, and validation evidence for implementation phases.

## Testing Rules

- Risky auth, API, RBAC, migration, module, and UI changes require tests or documented manual evidence.
- Phase evidence must include commands run, pass/fail outcome, and any accepted gaps.
- Local validation commands should be scriptable and not depend on undocumented machine-specific paths after Phase 10.
- Static analysis and lint failures must be fixed or tracked as accepted exceptions before final certification.

## Evidence Checklist

- Unit tests cover isolated services and DTOs.
- Feature tests cover API and auth flows.
- Migration checks cover schema invariants.
- UI/accessibility checks cover keyboard, focus, contrast, and state behavior.
- Release evidence links back to the phase that produced each validation result.
