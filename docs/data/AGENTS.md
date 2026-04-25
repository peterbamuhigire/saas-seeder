# Data Agents Guide

## Ownership

- **Owner:** data owner for schema, migration, and seed governance.
- **Update trigger:** update this file when table ownership, migration rules, rollback posture, seed data policy, or data validation gates change.

## Scope

This guide governs MySQL schema documentation, migration plans, seed data, stored procedures, and data validation evidence.

## Data Rules

- Schema changes must be planned before implementation and linked from `docs/plans/`.
- Runtime code must not create or alter production tables; use governed migrations instead.
- Migrations must state forward action, rollback posture, affected tables, stored procedure impact, and validation commands.
- Sensitive auth data must be stored as hashes or identifiers according to the token storage policy created in Phase 04.
- Data docs must distinguish tenant-scoped data, global configuration, audit data, and operational metadata.

## Validation Expectations

- Migration evidence includes successful apply checks and schema verification.
- Stored procedure changes include caller impact notes.
- Data changes that affect auth, RBAC, tenants, or modules must update the entity model and invariants docs.
