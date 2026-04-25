# ADR-0002: API Runtime Contract

Status: Accepted  
Date: 2026-04-26  
Phase: April World-Class Phase 02

## Context

`api/bootstrap.php` currently provides JSON helpers, CORS headers, and an exception handler, while some endpoints call helper names that are not defined by that bootstrap. Later phases need one request, response, auth, and error model before endpoint rewrites begin.

## Decision

API v1 uses a single runtime contract:

```json
{
  "success": false,
  "message": "Human-readable summary",
  "data": null,
  "errors": [
    {
      "code": "ERROR_CODE",
      "field": "optional_field",
      "detail": "Specific failure detail"
    }
  ],
  "meta": {
    "request_id": "uuid-or-generated-id"
  }
}
```

Successful responses set `success=true`, include `data`, omit `errors` or return an empty list, and include `meta.request_id`. Failed responses set HTTP status codes accurately and use stable machine-readable error codes.

API endpoints never redirect. Browser session helpers can be shared for credential verification but API auth uses bearer middleware and JSON errors.

## Required Runtime Services

| Service | Responsibility |
|---|---|
| Request reader | Method guard, JSON parsing, body size limits, content-type validation. |
| Response writer | Envelope, HTTP status, headers, request id. |
| Auth middleware | Bearer extraction, access-token validation, tenant and user status resolution. |
| Error mapper | Converts validation, auth, permission, module, database, and unexpected errors to stable envelope codes. |
| CORS policy | Explicit production origins; wildcard allowed only in development without credentials. |

## Consequences

- Phase 03 defines OpenAPI and runtime helpers before endpoint rewrites.
- Phase 04 auth endpoints return identical envelope shapes.
- Tests assert both HTTP status and error code.
- Logs, audit events, and client responses share `meta.request_id`.

## Rejected Alternatives

| Alternative | Reason rejected |
|---|---|
| Keep endpoint-local helper functions | Existing endpoints already diverge, and future endpoints would repeat parsing and error behavior. |
| Always return HTTP 200 with `success=false` | Breaks client retry, monitoring, and security tooling semantics. |
| Redirect API clients to web sign-in | API clients require machine-readable auth failures. |
