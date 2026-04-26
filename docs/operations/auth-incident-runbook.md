# Auth Incident Runbook

## Triggers

- login success rate drops below the SLO target
- repeated `auth.token.reuse_detected` events
- sudden spike in `auth.lockout`
- refresh or logout endpoints returning elevated 401, 409, or 429 responses

## Triage

1. Gather failing `request_id` values from clients or test probes.
2. Check the impacted endpoint: `/api/v1/auth/login`, `/refresh`, `/logout`, or `/logout-all`.
3. Review audit events for the user, franchise, and device family.
4. Confirm whether the issue is isolated to one tenant, one device family, or the full runtime.

## Containment

- For suspected token theft: force `logout-all` for the user or tenant scope and rotate secrets if compromise is broader.
- For lockout spikes: confirm brute-force traffic versus a regression before adjusting enforcement.
- For refresh reuse: treat as suspicious until a client replay bug is proven.

## Recovery Checks

- login returns a fresh access token and refresh token
- refresh rotates the token family exactly once
- logout revokes the current device token
- logout-all invalidates all user sessions
- affected audit events include `request_id`
