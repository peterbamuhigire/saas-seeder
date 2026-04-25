# Failure Modes

Phase: April World-Class Phase 02  
Status: Accepted architecture baseline

## Failure Matrix

| Failure | Detection | User/API response | Recovery | Audit/observability |
|---|---|---|---|---|
| Missing `JWT_SECRET_KEY` | `TokenService` constructor fails. | Web login/API login returns controlled server error without secret details. | Configure `.env` and restart PHP process. | `auth_config_missing`, exception class, request id. |
| Invalid credentials | `AuthService` status is not success or password verification fails. | Web form error or API 401 envelope. | User retries; lockout/rate-limit policy applies. | `login_failure`, email/username hash, IP, user agent. |
| Account locked, inactive, or suspended | Auth status or user row. | 423 for locked, 403 for inactive/suspended. | Admin reactivates account or lockout expires by policy. | `login_denied`, status, user id when known. |
| Expired web session | `last_activity` exceeds idle timeout. | Redirect to sign-in with `session_expired`. | User signs in again. | `session_expired`, user id, franchise id. |
| Expired access token | JWT `exp` or DB session invalid. | API 401 `AUTH_TOKEN_EXPIRED`. | Client uses refresh token. | `api_access_expired`, token jti hash. |
| Refresh token reuse | Revoked refresh token appears again. | API 409 `REFRESH_REUSE_DETECTED`; token family revoked. | User signs in again on all affected devices. | `api_refresh_reuse_detected`, family id, device id, IP. |
| Permission version mismatch | Access token `pv` differs from tenant `permission_version`. | API 401 requiring token refresh; web session permission check denies if stale. | Refresh token or sign in again. | `permission_version_stale`, user id, franchise id, token pv, current pv. |
| Missing permission | `PermissionService` returns false. | Web redirect to access denied or API 403 envelope. | Admin grants permission or user changes workflow. | `authorization_denied`, permission code, module code. |
| Module disabled | Module Registry denies tenant route. | Web access denied or API 403 `MODULE_DISABLED`. | Owner/super admin enables module if policy allows. | `module_access_denied`, module code, franchise id. |
| Tenant suspended or missing | Tenant resolver cannot find active franchise. | Web access denied; API 403 or 404 according to resource visibility. | Super admin repairs tenant state. | `tenant_unavailable`, franchise id. |
| Database unavailable | PDO connection or query fails. | Web generic error page; API 503 envelope when runtime can catch it. | Restore MySQL, verify migrations and connection settings. | `db_unavailable`, exception class, request id. |
| Migration drift | Ledger differs from filesystem or expected checksum. | Release gate fails; app startup may enter maintenance mode in production. | Run approved migration or rollback. | `migration_drift_detected`, migration id, checksum. |
| CORS origin denied | API bootstrap origin check fails. | Browser blocks request; API does not add credentialed origin header. | Add explicit origin in environment after review. | `cors_denied`, origin, route. |
| Invalid JSON body | JSON parser returns non-array or syntax error. | API 400 `INVALID_JSON`. | Client sends valid JSON and content type. | `api_invalid_json`, route, content length. |
| CSRF failure | CSRF helper rejects token. | Web form error or 419-style response in later runtime. | Reload form and resubmit. | `csrf_denied`, route, user id when present. |
| Unsafe upload or static path | MIME/path validation fails. | 400/403/404 without filesystem details. | Upload allowed file type or request valid asset. | `asset_denied`, path hash, reason. |
| Development utility exposed | `APP_ENV` is not development and dev route is requested. | 404 or 403. | Use local development environment only. | `dev_route_denied`, route, environment. |

## Error Envelope Requirements

API failures use [ADR-0002](adr/0002-api-runtime-contract.md). Browser failures use redirects or rendered errors, but logs and audit records still use the same `request_id` field so incidents can be correlated across web and API surfaces.
