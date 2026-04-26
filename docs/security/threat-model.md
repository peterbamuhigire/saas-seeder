# Threat Model

## Protected Assets

- user credentials and password hashes
- access tokens, refresh-token families, and session state
- tenant boundaries and module entitlements
- migration ledger and audit trail integrity

## Primary Threats

- credential stuffing or brute-force login attempts
- refresh token theft and replay
- stale or inconsistent permission state after role overrides
- cross-tenant module access
- insecure release or rollback steps that bypass validation

## Controls In Repo

- login, refresh, and logout rate limiting
- hashed refresh-token storage with rotation and reuse detection
- request ID propagation in API responses and logs
- append-only audit logging for auth, module, permission, and migration events
- CORS and security header middleware
- governed migrations with checksums and schema validation
