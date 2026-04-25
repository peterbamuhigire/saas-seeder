# Token Storage Policy

This policy defines token storage for the April world-class token lifecycle work.

## Token Classes

- Access tokens are short-lived JWTs with a 15 minute lifetime.
- Refresh tokens are opaque random values with a 30 day lifetime.
- Access tokens identify their database session by `jti`.
- Refresh tokens identify their database row only after HMAC-SHA256 hashing.

## Access Token Storage

Access JWT strings must not be stored in the database. The database stores:

- `tbl_user_sessions.jti` for access-session lookup.
- `tbl_user_sessions.token_hash` as a SHA-256 hash of the `jti` for audit correlation where needed.
- Session metadata such as user, franchise, IP address, user agent, expiry, and invalidation time.

`sp_validate_session()` validates by `jti`, not by a raw JWT.

## Refresh Token Storage

Refresh token plaintext is returned to the client only once. The server stores only:

- `tbl_refresh_tokens.token_hash`, produced with `hash_hmac('sha256', $refreshToken, $hashKey)`.
- `family_id` for reuse detection and family-wide revocation.
- Optional device, user-agent hash, and IP metadata.
- Expiry, revocation, replacement, and reuse-detection timestamps.

Use `REFRESH_TOKEN_HASH_KEY` when configured. If it is not present in existing deployments, the service falls back to `JWT_SECRET_KEY` so rollout can proceed, but production deployments should set a distinct refresh-token hash key.

## Rotation And Reuse

Every refresh request rotates the refresh token:

- The old row is revoked and linked to the replacement row.
- A new opaque refresh token is issued in the same family.
- Reuse of a revoked or already-used refresh token marks reuse detection and revokes the full family.

Logout revokes the current refresh token. Logout-all revokes every refresh token for the user and invalidates active access sessions.

## Client Handling

Browser clients should store refresh tokens in `HttpOnly`, `Secure`, `SameSite=Lax` or stricter cookies where possible. Native or CLI clients should use the platform credential store. Access tokens should be kept in memory and replaced through refresh rotation instead of persisted in local storage.

## Operational Rules

- Never log access tokens, refresh tokens, or `Authorization` headers.
- Never include raw token values in audit details, error responses, or analytics payloads.
- Rotate `REFRESH_TOKEN_HASH_KEY` through a controlled migration because existing refresh token hashes become unverifiable after key rotation.
- Treat database backups as sensitive even though token plaintext is not stored.
