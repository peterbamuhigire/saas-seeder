# Rate Limit Policy

API responses include:

- `RateLimit-Limit`
- `RateLimit-Remaining`
- `RateLimit-Reset`
- `Retry-After` on `429`

Policies:

- Login: 5/minute per IP and 10/hour per username/email hash.
- Refresh: 30/minute per IP/device.
- Logout: 30/minute per IP/device.
- Register: 3/hour per IP and 5/day per email hash.
- General authenticated API: 100/minute per user by default.
