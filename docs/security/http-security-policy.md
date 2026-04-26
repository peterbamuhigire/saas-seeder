# HTTP Security Policy

API and web responses apply centralized security headers through `SecurityHeadersMiddleware` or `public/includes/security-headers.php`.

Required headers:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Cache-Control: no-store`
- `Permissions-Policy`
- `Content-Security-Policy-Report-Only`
- `Strict-Transport-Security` in production

Production CORS must use `CORS_ALLOWED_ORIGINS`; wildcard origins are only for development.
