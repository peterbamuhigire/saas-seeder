# Architecture Agents Guide

## Ownership

- **Owner:** architecture owner for system boundaries, ADRs, and cross-domain decisions.
- **Update trigger:** update this file when bounded contexts, ADR requirements, dependency rules, or critical-flow documentation standards change.

## Scope

This guide governs architecture documentation under `docs/architecture/`, including context maps, critical flows, dependency views, failure modes, and ADRs.

## Architecture Rules

- Capture expensive or irreversible decisions as ADRs before implementation.
- Every critical flow must state actor, entry point, auth mode, tenant source, authorization gate, transaction boundary, audit events, error model, and observability fields.
- Architecture docs must distinguish browser session auth from API bearer auth.
- Later implementation phases must cite the ADRs or architecture docs they depend on.

## Review Checklist

- No blocking ADR contains `TBD`, `TODO`, or unresolved decision language.
- API, data, module, UI, testing, and release docs agree with the accepted ADRs.
- Dependency changes are reflected in `docs/plans/april-world-class/dependency-map.md`.
