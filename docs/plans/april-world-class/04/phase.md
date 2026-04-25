# Phase 04: Token Lifecycle And Auth Endpoint Rewrite

## Objective

Resolve the broken/inconsistent auth API by implementing one coherent token lifecycle across web auth, API login, refresh, logout, logout-all, database storage, and documentation.

## Skills Applied

- `api-design-first`
- `php-modern-standards`
- `database-design-engineering`
- `skill-composition-standards`

## Current Problems

- API login returns only `access_token`, while refresh expects `refresh_token`.
- Refresh/logout endpoints reference missing `App\Http\Auth` infrastructure.
- `TokenService` appears to store full JWTs but validate using decoded `jti` against a stored token procedure.
- Raw JWT storage increases blast radius in database backups.
- Logout-all semantics are undefined in the current working service layer.

## Recommended Decision

Use access + rotating opaque refresh tokens:

- Access token: JWT, 15 minutes, contains `iss`, `aud`, `iat`, `exp`, `sub`, `franchise_id`, `jti`, permission version.
- Refresh token: opaque random token, stored only as SHA-256/HMAC hash.
- Refresh rotation: every refresh revokes old token and issues a new pair.
- Reuse detection: reuse of revoked refresh token revokes the token family.
- Logout: revoke current device refresh token and active access session.
- Logout-all: revoke every refresh family and access session for the user.

## Deliverables

Create:

- `src/Auth/Token/AccessTokenService.php`
- `src/Auth/Token/RefreshTokenService.php`
- `src/Auth/Token/RefreshTokenRepository.php`
- `src/Auth/Token/TokenPair.php`
- `src/Auth/Token/TokenClaims.php`
- `src/Auth/Token/TokenFamily.php`
- `src/Auth/Token/TokenRevocationReason.php`
- `database/migrations/0003_api_token_lifecycle.sql`
- `docs/architecture/auth-token-lifecycle.md`
- `docs/security/token-storage-policy.md`
- `tests/Unit/Auth/AccessTokenServiceTest.php`
- `tests/Unit/Auth/RefreshTokenServiceTest.php`
- `tests/Feature/Api/AuthTokenLifecycleTest.php`

Update:

- `src/Auth/Services/TokenService.php` or replace it with compatibility facade.
- `api/v1/auth/login.php`
- `api/v1/auth/refresh.php`
- `api/v1/auth/logout.php`
- `api/v1/auth/logout-all.php`
- `docs/api/openapi.yml`
- `docs/seeder-template/migration.sql`

## Database Requirements

Add `tbl_refresh_tokens`:

- `id BIGINT UNSIGNED`
- `user_id BIGINT UNSIGNED`
- `franchise_id BIGINT UNSIGNED NULL`
- `token_hash CHAR(64)`
- `family_id CHAR(32)`
- `device_id VARCHAR(128) NULL`
- `user_agent_hash CHAR(64) NULL`
- `ip_address VARCHAR(45) NULL`
- `expires_at DATETIME`
- `revoked_at DATETIME NULL`
- `replaced_by_token_id BIGINT UNSIGNED NULL`
- `reuse_detected_at DATETIME NULL`
- `created_at TIMESTAMP`

Fix `tbl_user_sessions`:

- Add indexed `jti`.
- Store token hash or jti, not raw JWT.
- Update `sp_validate_session()` to validate by `jti`.

## Work Breakdown

1. Add migration for token tables/session jti.
2. Implement access-token service.
3. Implement refresh-token repository.
4. Implement token pair issuance.
5. Rewrite API login to return token pair.
6. Rewrite refresh to rotate and detect reuse.
7. Rewrite logout to revoke current refresh token and access session.
8. Rewrite logout-all to revoke all user sessions and refresh families.
9. Update OpenAPI examples.
10. Add tests for valid, expired, stale, tampered, reused, and revoked tokens.

## Acceptance Criteria

- No API auth endpoint references missing classes or helpers.
- Login returns `access_token`, `refresh_token`, `token_type`, `expires_in`, and user.
- Refresh rotates refresh tokens and rejects reuse.
- Logout invalidates the current device.
- Logout-all invalidates all devices.
- Database never stores raw refresh tokens.
- Access-token validation checks issuer, audience, expiry, jti, DB session, and permission version.
- Stale permission-version tokens fail.

## Validation

Run:

```powershell
rg -n "App\\Http\\Auth|require_method|read_json_body|json_response|get_db" api src
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -l api\v1\auth\refresh.php
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -l api\v1\auth\logout.php
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -l api\v1\auth\logout-all.php
```

Feature tests:

- login success,
- login failure,
- refresh success,
- refresh reuse,
- logout current device,
- logout all devices,
- stale permission version.

## Sub-Agent Use

Use one worker for token services and migration. Use a separate worker for API endpoint rewrites. Use a verifier agent to test for lifecycle gaps after implementation.

## Exit Gate

No security or API certification can pass until this phase is green.

