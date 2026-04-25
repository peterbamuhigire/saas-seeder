# API Observability Notes

## Request Correlation

The API initializes one request ID for each request. It is emitted as:

- `X-Request-Id` response header
- `request_id` response body field
- exception log context

Clients should include the `request_id` when reporting API failures.

## Logging

Runtime errors are logged without secrets. Do not log raw passwords, bearer tokens, refresh tokens, cookie values, or full authorization headers.

## Metrics To Add

Later API runtime work should add counters for:

- Authentication success and failure by stable error code
- Method guard failures
- Malformed JSON failures
- Token refresh rotations and revocations
- Rate limit decisions
