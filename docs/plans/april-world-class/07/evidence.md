# Phase 07 Evidence

Implemented:

- Added rate-limit table migration in `database/migrations/0004_rate_limits.sql`.
- Added rate-limit classes and middleware under `src/Http/RateLimit` and `src/Http/Middleware/RateLimitMiddleware.php`.
- Applied auth-specific limits to login, refresh, logout, logout-all, and register endpoints.
- Added centralized HTTP security policy classes.
- Updated API CORS handling so production requires explicit origins.
- Added security headers to admin/member include wrappers and standalone auth pages.
- Registration no longer returns `verify_token` in production.
- Added security, CSP, secret-management, and rate-limit docs.
- Added rate-limit and security policy tests.

Validation:

- `rg -n "Access-Control-Allow-Origin: \\*" api public src` found no literal wildcard header.
- `rg -n "ensureSignupTable|CREATE TABLE IF NOT EXISTS" api public src` returned no matches.
- PHP lint passed.
- PHPUnit passed: 16 tests, 59 assertions.

Known follow-up:

- CSP is report-only and still permits inline assets until legacy auth page styles/scripts are moved into versioned files.
