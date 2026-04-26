# Service Boundaries

Authentication service boundaries:

- `LoginAuthenticator`: stored procedure/manual credential lookup.
- `UserContextService`: user and franchise context lookup.
- `UserSessionService`: web session hydration.
- `TokenService` and token lifecycle services: token issuance and revocation.
- `PermissionService`: RBAC permission resolution.

`AuthService` remains the orchestration point for compatibility, but new behavior should be added to focused collaborators first.
