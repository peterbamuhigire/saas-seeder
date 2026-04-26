# Rollback Policy

Migration rollback depends on data risk:

- Reversible: pure additive objects that can be dropped before production data exists.
- Compensating-only: changes that transform data or create security records.
- Restore snapshot: destructive or broad structural changes.
- Forward-fix-only: auth/session/token changes after release.

Phases 5-7 are treated as forward-fix-only once deployed because auth, module access, and rate-limit tables affect security decisions.
