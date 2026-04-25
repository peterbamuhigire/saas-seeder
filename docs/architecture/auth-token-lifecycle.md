# Auth Token Lifecycle

Phase: April World-Class Phase 02  
Status: Accepted architecture baseline  
Primary ADR: [ADR-0001](adr/0001-auth-token-model.md)

## Decision Summary

SaaS Seeder uses two auth modes:

| Mode | Consumer | Credential | Server state | Lifetime |
|---|---|---|---|---|
| Web session | Browser UI | HttpOnly, Secure PHP session cookie plus session-stored access token. | PHP session and `tbl_user_sessions`. | 30 minutes idle timeout; remember-me cookie creates a longer DB-backed session only when explicitly selected. |
| API bearer | API clients | Short-lived JWT access token plus rotating opaque refresh token. | Access session in `tbl_user_sessions`; hashed refresh tokens in `tbl_refresh_tokens`. | Access token 15 minutes; refresh token 30 days by default. |

Refresh tokens are opaque random values, not JWTs. The current refresh/logout endpoints that reference missing `JwtService` and `RefreshTokenStore` are treated as Phase 04 implementation targets, not as the accepted model.

## Access Token Claims

| Claim | Required | Source |
|---|---:|---|
| `iss` | Yes | `APP_URL` or configured issuer. |
| `aud` | Yes | API audience for SaaS Seeder clients. |
| `iat` | Yes | Issue timestamp. |
| `exp` | Yes | Issue timestamp plus access TTL. |
| `jti` | Yes | `tbl_user_sessions` session identifier. |
| `user_id` | Yes | Authenticated user id. |
| `franchise_id` | Yes for tenant users; platform sentinel for super-admin platform access. | User row or explicit selected tenant context. |
| `user_type` | Yes | User row. |
| `pv` | Yes | `tbl_franchises.permission_version` or platform permission version. |

## Refresh Token Storage

`tbl_refresh_tokens` is introduced by the Phase 04/05 implementation path with:

| Field | Purpose |
|---|---|
| `id` | Internal primary key. |
| `token_hash` | Hash of opaque refresh token using a server-side pepper. |
| `family_id` | Stable id for one login/device token chain. |
| `user_id` | Owner. |
| `franchise_id` | Tenant context at issue time. |
| `device_id` | Client-provided or server-generated device id. |
| `jti` | Server-visible token id stored separately from raw token. |
| `issued_at`, `expires_at`, `last_used_at` | Lifecycle timestamps. |
| `revoked_at`, `revoked_reason` | Revocation state. |
| `replaced_by_jti` | Rotation chain link. |
| `created_ip`, `created_user_agent` | Audit and abuse analysis. |

## Lifecycle

1. Login verifies credentials through `AuthService`.
2. The server creates a DB session row and access JWT.
3. API login also creates an opaque refresh token, stores only its hash, and returns the raw token once.
4. API refresh verifies the opaque token hash, user status, tenant status, device state, expiry, revocation, and permission version.
5. Refresh rotation happens in one transaction: revoke old token, insert new token in the same family, issue a new access JWT.
6. Reuse of a revoked refresh token revokes the full token family and returns a conflict-style security error.
7. Device logout revokes the presented refresh token and active session for that device.
8. Account logout revokes every refresh token for the actor or a selected device.
9. Password change, account suspension, tenant suspension, and permission-version mismatch invalidate active access without waiting for expiration.

## Tenant Rules

Tenant identity for API calls comes from verified token state, never from request body. Super-admin platform actions use platform scope. Super-admin tenant actions must record the selected tenant id in audit events and observability fields.

## Compatibility Window

During Phase 04, endpoints may accept an existing JWT-shaped refresh token only to return a controlled migration error or transition response. The target contract is opaque rotating refresh tokens, and new clients must not receive refresh JWTs.
