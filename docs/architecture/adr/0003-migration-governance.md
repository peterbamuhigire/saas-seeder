# ADR-0003: Migration Governance

Status: Accepted  
Date: 2026-04-26  
Phase: April World-Class Phase 02

## Context

The repository has setup scripts and SQL under `docs/seeder-template`, and the public signup endpoint can create `tbl_api_signup_requests` at request time. Later phases need governed schema changes for refresh tokens, module registry tables, audit expansion, and quality gates.

## Decision

Adopt a migration ledger model. Schema changes are applied through versioned migration files and recorded in `tbl_schema_migrations`. Runtime endpoints must not create or alter production tables.

Migration files are ordered, immutable after merge, and contain explicit up/down posture. Seeds that are safe to rerun are separated from one-time schema changes.

## Ledger Fields

| Field | Purpose |
|---|---|
| `version` | Ordered migration identifier. |
| `name` | Human-readable migration name. |
| `checksum` | Detects drift after merge. |
| `applied_at` | Execution timestamp. |
| `applied_by` | Operator or automation identity. |
| `duration_ms` | Runtime evidence. |
| `status` | `applied`, `failed`, or `rolled_back`. |
| `rollback_notes` | Operational rollback posture. |

## Consequences

- Phase 05 implements the ledger and moves request-time table creation into migrations.
- Phase 04 token tables are migration-owned.
- Phase 06 module tables are migration-owned.
- Release evidence includes migration status and drift check output.

## Rejected Alternatives

| Alternative | Reason rejected |
|---|---|
| Continue ad hoc setup SQL only | Cannot prove order, drift, or rollback posture for reusable scaffold releases. |
| Allow endpoints to create missing tables | Production requests become schema deployment mechanisms and hide failed setup. |
| Store migration state only in files | Multiple environments need database-local evidence of applied schema. |
