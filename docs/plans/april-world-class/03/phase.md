# Phase 03: API Contract And Runtime Foundation

## Objective

Make the API contract-driven and executable. This phase replaces inconsistent global helper usage with a stable API runtime model and produces OpenAPI/error/auth artifacts before endpoint rewrites.

## Skills Applied

- `api-design-first`
- `php-modern-standards`
- `skill-composition-standards`
- `system-architecture-design`

## Current Problems

- API docs and runtime are out of sync.
- `refresh.php`, `logout.php`, and `logout-all.php` reference missing classes/functions.
- `api/bootstrap.php` defines ad hoc global helpers instead of a testable runtime layer.
- Error responses use `success/message/errors`, not the enhanced structured error envelope.
- No OpenAPI spec exists.

## Deliverables

Create:

- `docs/api/openapi.yml`
- `docs/api/auth-model.md`
- `docs/api/error-model.md`
- `docs/api/rate-limit-policy.md`
- `docs/api/idempotency-map.md`
- `docs/api/observability-notes.md`
- `docs/api/examples/auth-login-success.json`
- `docs/api/examples/auth-login-error.json`
- `docs/api/examples/validation-error.json`
- `src/Http/Request/JsonRequest.php`
- `src/Http/Response/ApiResponse.php`
- `src/Http/Response/ApiError.php`
- `src/Http/Middleware/MethodGuard.php`
- `src/Http/Middleware/BearerAuth.php`
- `src/Http/Middleware/CorsMiddleware.php`
- `src/Http/Middleware/SecurityHeadersMiddleware.php`
- `src/Http/Request/RequestId.php`

Update:

- `api/bootstrap.php`
- `docs/api/API-DOCUMENTATION.md`

## Work Breakdown

1. Define canonical response envelope:

```json
{
  "success": false,
  "error": {
    "code": "AUTH_INVALID_CREDENTIALS",
    "message": "Invalid credentials",
    "details": {},
    "documentation_url": "/docs/api/errors#AUTH_INVALID_CREDENTIALS"
  },
  "request_id": "..."
}
```

2. Write OpenAPI for current and planned auth endpoints:
   - `POST /api/v1/auth/login`
   - `POST /api/v1/auth/refresh`
   - `POST /api/v1/auth/logout`
   - `POST /api/v1/auth/logout-all`
   - `POST /api/v1/public/auth/register`
3. Implement testable request/response classes.
4. Make `api/bootstrap.php` delegate to runtime classes.
5. Add request IDs to responses and logs.
6. Standardize method errors with JSON `405` and `Allow` header.
7. Standardize malformed JSON as JSON `400`.
8. Keep compatibility helpers temporarily only if needed, with a removal date.

## Acceptance Criteria

- Every advertised API endpoint exists in OpenAPI.
- OpenAPI examples match actual field names.
- All API errors use stable error codes.
- Every API response includes `request_id`.
- `GET /api/v1/auth/login.php` returns JSON 405, not PHP output or HTML.
- Malformed JSON returns JSON 400.
- API runtime classes are unit-testable without running a web server.

## Validation

Run after implementation:

```powershell
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -l api\bootstrap.php
& 'C:\wamp64\bin\php\php8.3.28\php.exe' -l src\Http\Response\ApiResponse.php
rg -n "function jsonResponse|function errorResponse" api src
```

Manual/API smoke:

- POST login valid JSON.
- POST login malformed JSON.
- GET login.
- Unknown route through the web server if routing is configured.

## Sub-Agent Use

Assign an API worker to runtime classes and endpoint contract shape. Assign a documentation worker to OpenAPI/error/auth artifacts. Keep write scopes separate.

## Exit Gate

Phase 04 cannot rewrite token endpoints until this runtime contract is accepted.

