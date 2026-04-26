# SLO And Alert Plan

## Service Level Objectives

| Signal | Target | Alert Trigger |
|---|---|---|
| Auth availability | 99.9% successful responses for expected valid flows | sustained failures on login, refresh, logout, or logout-all |
| API latency | p95 under 500 ms for auth endpoints in normal load | p95 above 1 s for 15 minutes |
| Login error rate | less than 5% unexpected server-side failure rate | elevated 5xx or lockout spikes without attack explanation |
| Migration failure rate | 0 failed controlled releases | any failed migration in staging or production |
| Security event threshold | 0 unreviewed refresh reuse events in release windows | any `auth.token.reuse_detected` without triage owner |

## Alert Handling

- auth availability or token abuse alerts route to the auth incident runbook
- migration alerts block release certification until disposition is recorded
- skipped alerts or disabled gates require owner, reason, and expiry date
