# API Idempotency Map

| Endpoint | Idempotency Support | Notes |
| --- | --- | --- |
| `POST /api/v1/auth/login` | No | Each successful login may create a new token/session. |
| `POST /api/v1/auth/refresh` | No | Refresh rotates token state and must not be replayed. |
| `POST /api/v1/auth/logout` | Effectively idempotent | Repeating a logout for an already revoked token should remain safe. |
| `POST /api/v1/auth/logout-all` | Effectively idempotent | Repeating the request should leave all matching tokens revoked. |
| `POST /api/v1/public/auth/register` | Conflict guarded | Duplicate pending signup returns `409`. |

`Idempotency-Key` is accepted in CORS headers for future write endpoints, but these auth endpoints do not currently persist idempotency keys.
