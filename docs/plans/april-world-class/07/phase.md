# Phase 07: Security Hardening, Rate Limiting, And HTTP Policy

## Objective

Close remaining security gaps around auth abuse, rate limiting, headers, CORS, CSP, secret management, audit events, and production/development policy separation.

## Skills Applied

- `php-modern-standards`
- `api-design-first`
- `database-design-engineering`
- `skill-composition-standards`

## Current Problems

- API documentation says rate limiting is to be implemented.
- Header policy is not uniformly included across panels and auth pages.
- CSP currently allows `unsafe-inline`.
- Registration returns verification token as a placeholder.
- Login failure messages and audit reasons need clearer separation.

## Deliverables

Create:

- `src/Auth/Security/LoginAttemptService.php`
- `src/Auth/Security/CredentialPolicy.php`
- `src/Auth/Security/DeviceFingerprint.php`
- `src/Auth/Security/AuthAuditLogger.php`
- `src/Http/RateLimit/RateLimiter.php`
- `src/Http/RateLimit/RateLimitPolicy.php`
- `src/Http/RateLimit/RateLimitStoreInterface.php`
- `src/Http/RateLimit/DatabaseRateLimitStore.php`
- `src/Http/Middleware/RateLimitMiddleware.php`
- `src/Http/Security/SecurityHeaderPolicy.php`
- `src/Http/Security/CspPolicy.php`
- `src/Http/Security/CorsPolicy.php`
- `database/migrations/0004_rate_limits.sql`
- `docs/security/auth-threat-model.md`
- `docs/security/http-security-policy.md`
- `docs/security/csp-rollout.md`
- `docs/security/secret-management.md`
- `docs/api/rate-limit-policy.md`
- `tests/Feature/Api/RateLimitTest.php`
- `tests/Feature/Security/HttpHeadersTest.php`

## Rate Limit Policies

- Login: 5/minute per IP and 10/hour per normalized username/email hash.
- Refresh: 30/minute per user/device.
- Logout: 30/minute per user/device.
- Register: 3/hour per IP and 5/day per normalized email hash.
- General authenticated API: 100/minute per user by default.

Return headers:

- `RateLimit-Limit`
- `RateLimit-Remaining`
- `RateLimit-Reset`
- `Retry-After` on 429

## Work Breakdown

1. Move failed-login and lockout decisions into `LoginAttemptService`.
2. Add rate-limit store and middleware.
3. Apply auth-specific policies to API auth endpoints.
4. Apply production CORS allow-list rules.
5. Fail production boot if `CORS_ALLOWED_ORIGINS` is empty.
6. Centralize web/API security headers.
7. Include headers from root, adminpanel, memberpanel, and standalone auth pages.
8. Add CSP report-only rollout plan.
9. Stop returning `verify_token` in production registration responses.
10. Add audit events:
    - login success,
    - login failure,
    - account lockout,
    - token refresh,
    - token reuse detection,
    - logout,
    - logout-all,
    - module enable/disable.

## Acceptance Criteria

- Rate limit violations return canonical JSON 429.
- Production CORS never returns wildcard.
- Security headers exist for web panel pages, auth pages, and API endpoints.
- HSTS is present in production mode.
- CSP rollout is documented.
- Public login messages do not leak user existence.
- Internal audit events retain precise failure reasons.

## Validation

Run:

```powershell
rg -n "Access-Control-Allow-Origin: \\*" api public src
rg -n "unsafe-inline" public src docs
rg -n "verify_token" api docs
```

HTTP checks:

- `/sign-in.php`
- `/dashboard.php`
- `/adminpanel/`
- `/memberpanel/`
- `/api/v1/auth/login.php`

Tests:

- rate limit exceeded,
- production CORS,
- headers present,
- missing secrets fail closed.

## Sub-Agent Use

Use an API/security worker for rate limiting and CORS. Use a web shell worker for header inclusion across pages. Use a verifier for HTTP header tests.

## Exit Gate

Security phase is complete only when automated tests prove production-mode behaviour.

