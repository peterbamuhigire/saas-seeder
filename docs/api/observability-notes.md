# API Observability Notes

## Request Correlation

The API initializes one request ID for each request. It is emitted as:

- `X-Request-Id` response header
- `request_id` response body field
- exception log context

Clients should include the `request_id` when reporting API failures.

## Logging

Runtime errors are logged without secrets. Do not log raw passwords, bearer tokens, refresh tokens, cookie values, or full authorization headers.

Log records should include:

- `request_id`
- request method
- request path
- stable error or audit event name
- tenant or franchise scope when known

## Audit Event Catalog

- `auth.login.success`
- `auth.login.failure`
- `auth.lockout`
- `auth.password.changed`
- `auth.token.refreshed`
- `auth.token.reuse_detected`
- `auth.logout`
- `auth.logout_all`
- `permission.override`
- `module.enabled`
- `module.disabled`
- `migration.applied`

## Metrics And Review Signals

- login success and failure by stable status
- lockout count by window
- refresh reuse detections
- logout and logout-all volume
- 401, 409, 422, and 429 response rates
- request latency by endpoint
- migration failure count during controlled releases
