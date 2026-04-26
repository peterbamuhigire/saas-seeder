# Auth Threat Model

Primary threats:

- Credential stuffing against login and registration.
- Refresh token theft and replay.
- Cross-tenant module access.
- Session fixation or stale permission grants.
- Production CORS wildcard exposure.

Controls:

- Login and registration rate limits.
- Hashed refresh-token storage with rotation and reuse detection.
- Tenant-scoped module gates.
- JWT issuer/audience/jti validation and permission-version checks.
- Production CORS requires an explicit allow-list.
