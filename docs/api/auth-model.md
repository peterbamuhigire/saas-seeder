# API Auth Model

The v1 API uses bearer tokens for authenticated endpoints. Clients send the token in the `Authorization` header:

```http
Authorization: Bearer <token>
```

## Endpoints

| Endpoint | Authentication | Purpose |
| --- | --- | --- |
| `POST /api/v1/auth/login` | Public | Exchanges username/email plus password for an access token. |
| `POST /api/v1/auth/refresh` | Refresh token in body or bearer header | Rotates refresh token and issues a new access token. |
| `POST /api/v1/auth/logout` | Refresh token in body or bearer header | Revokes the current device refresh token. |
| `POST /api/v1/auth/logout-all` | Access bearer token | Revokes all refresh tokens for the authenticated user, optionally scoped to `device_id`. |
| `POST /api/v1/public/auth/register` | Public | Creates a signup request. |

## Token Handling

Access tokens are short lived and intended for API authorization. Refresh tokens are longer lived and should be stored only in secure client storage. Token verification and persistence are completed by the auth token runtime owned by the endpoint rewrite phase; Phase 03 only defines the request contract and middleware boundary.

## Request IDs

Every response includes `request_id`. Clients may send `X-Request-Id`; otherwise the API generates one and returns it in the response body and `X-Request-Id` header.
