# ADR-0001: Auth Token Model

Status: Accepted  
Date: 2026-04-26  
Phase: April World-Class Phase 02

## Context

Current login code issues short-lived JWT access tokens through `TokenService` and stores session state in `tbl_user_sessions`. The refresh and logout API endpoints reference a different future runtime with `JwtService` and `RefreshTokenStore`, which is not present in the current source tree. Leaving both models active would make Phase 03 and Phase 04 implement incompatible contracts.

## Decision

Use short-lived JWT access tokens plus rotating opaque refresh tokens.

Access tokens remain JWTs with a 15-minute default lifetime, DB session validation through `tbl_user_sessions`, issuer/audience checks, `jti`, `user_id`, `franchise_id`, `user_type`, and permission version `pv`.

Refresh tokens are opaque random values returned only at issue or rotation time. The database stores a hashed token value, family id, user id, franchise id, device id, jti, expiry, revocation state, replacement chain, IP, and user agent. Refresh rotation is single-use and transactional.

Web sessions continue to use HttpOnly, Secure PHP session cookies and the existing session helpers. Browser pages do not depend on refresh tokens.

## Consequences

- Phase 04 must replace the missing refresh runtime with concrete services under the existing project namespace.
- API login must return both `access_token` and `refresh_token` after the refresh-token table exists.
- Refresh-token reuse revokes the full token family.
- Logout by refresh token is idempotent and does not reveal whether a token was already revoked.
- Password change, account suspension, tenant suspension, and permission-version changes invalidate access in addition to natural token expiry.

## Rejected Alternatives

| Alternative | Reason rejected |
|---|---|
| Remove refresh endpoints | Weak API client experience and does not match the existing public endpoint surface. |
| Long-lived JWT-only bearer tokens | Hard to revoke safely after permission, tenant, password, or device changes. |
| Refresh JWTs | Exposes refresh metadata client-side and makes server-side family reuse detection more complex than opaque token hashing. |
