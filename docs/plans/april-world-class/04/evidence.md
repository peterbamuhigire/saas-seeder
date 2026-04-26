# Phase 04 Evidence

**Status:** implemented on 2026-04-26.  
**Owner:** API/security owner.  
**Scope:** access token service, rotating opaque refresh token service, token migration, token storage policy, auth endpoint rewrites, and test scaffolding.

## Artifacts

- `src/Auth/Token/AccessTokenService.php`
- `src/Auth/Token/RefreshTokenService.php`
- `src/Auth/Token/RefreshTokenRepository.php`
- `src/Auth/Token/TokenPair.php`
- `src/Auth/Token/TokenClaims.php`
- `src/Auth/Token/TokenFamily.php`
- `src/Auth/Token/TokenRevocationReason.php`
- `src/Auth/Services/TokenService.php`
- `database/migrations/0003_api_token_lifecycle.sql`
- `docs/security/token-storage-policy.md`
- `docs/seeder-template/migration.sql`
- `api/v1/auth/login.php`
- `api/v1/auth/refresh.php`
- `api/v1/auth/logout.php`
- `api/v1/auth/logout-all.php`
- `docs/api/openapi.yml`
- `docs/api/examples/auth-login-success.json`
- `phpunit.xml`
- `tests/Unit/Auth/AccessTokenServiceTest.php`
- `tests/Unit/Auth/RefreshTokenServiceTest.php`
- `tests/Feature/Api/AuthTokenLifecycleTest.php`

## Validation

- Auth endpoint PHP syntax checks passed.
- Stale missing auth class/helper scan returned no matches.
- `C:\wamp64\bin\php\php8.3.28\php.exe vendor\bin\phpunit` passed with 9 tests and 48 assertions.
- `database/migrations/0003_api_token_lifecycle.sql` was applied to the live `saas_seeder` database on 2026-04-26.
- Live schema verification confirmed `tbl_refresh_tokens`, `tbl_user_sessions.jti`, `tbl_user_sessions.token_hash`, `uk_session_jti`, `idx_session_token_hash`, and updated token/session procedures.
