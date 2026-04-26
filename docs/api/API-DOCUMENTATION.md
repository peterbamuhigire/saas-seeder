# API Documentation - SaaS Seeder Template

## Contract

Base URL:

```text
http://localhost:8000/api/v1
```

Canonical contract artifacts:

- [OpenAPI spec](openapi.yml)
- [Auth model](auth-model.md)
- [Error model](error-model.md)
- [Rate limit policy](rate-limit-policy.md)
- [Idempotency map](idempotency-map.md)
- [Observability notes](observability-notes.md)

## Response Envelope

Every response includes `request_id`. Clients may provide `X-Request-Id`; otherwise the API generates one.

Success:

```json
{
  "success": true,
  "request_id": "8f3a4d7c1b6e4a2f9d0c3b5a7e8f9012",
  "message": "Login successful",
  "data": {}
}
```

Error:

```json
{
  "success": false,
  "error": {
    "code": "AUTH_UNAUTHORIZED",
    "message": "Invalid credentials",
    "details": {},
    "documentation_url": "/docs/api/errors#AUTH_UNAUTHORIZED"
  },
  "request_id": "8f3a4d7c1b6e4a2f9d0c3b5a7e8f9012"
}
```

## Authentication

Authenticated endpoints use bearer tokens:

```http
Authorization: Bearer <token>
```

Password hashing remains centralized through `PasswordHelper` and `UserService`. API endpoint code must not call raw password hashing or verification functions directly.

## Auth Endpoints

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| `POST` | `/auth/login` | Public | Authenticate username/email plus password. |
| `POST` | `/auth/refresh` | Refresh token body or bearer header | Rotate refresh token and issue a new access token. |
| `POST` | `/auth/logout` | Refresh token body or bearer header | Revoke the current device refresh token. |
| `POST` | `/auth/logout-all` | Access bearer token | Revoke all refresh tokens for the user. |
| `POST` | `/public/auth/register` | Public | Create a public signup request. |

## Login Example

Request:

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "X-Request-Id: demo-request-1" \
  -d '{"username":"root","password":"password"}'
```

Success response shape is available at [examples/auth-login-success.json](examples/auth-login-success.json).

## Error Handling

Stable error codes are documented in [error-model.md](error-model.md). Common codes include:

| Code | HTTP | Meaning |
| --- | ---: | --- |
| `REQUEST_MALFORMED_JSON` | 400 | Invalid JSON body. |
| `AUTH_UNAUTHORIZED` | 401 | Authentication is missing or failed. |
| `REQUEST_METHOD_NOT_ALLOWED` | 405 | Unsupported method; response includes `Allow`. |
| `VALIDATION_FAILED` | 422 | Input failed validation. |
| `INTERNAL_SERVER_ERROR` | 500 | Unexpected server error. |

## Runtime Notes

`api/bootstrap.php` delegates request IDs, JSON responses, CORS, and security headers to classes under `src/Http`.

Endpoints call the runtime classes directly:

- `MethodGuard` for allowed methods.
- `JsonRequest` for request body parsing.
- `BearerAuth` for bearer token extraction.
- `ApiResponse` and `ApiError` for response envelopes.
- `Database` for PDO access.

## CORS

Allowed origins come from `CORS_ALLOWED_ORIGINS`. Development falls back to `*`. CORS allows `Content-Type`, `Authorization`, `X-Request-Id`, and `Idempotency-Key`.

## Version

Current API version: `v1`

Last updated: 2026-04-26
