# Known Exceptions

| Exception | Impact | Mitigation | Owner | Expiry | Accepted By |
|---|---|---|---|---|---|
| Historical plan files and active phase instructions still contain `not-started`, `TODO`, or `TBD` strings | Raw stale-text grep is noisy and cannot be used alone as a release blocker | Treat matches under historical plans and phase instructions as expected archival content; keep active entry points and evidence docs current | documentation owner | 2026-05-31 | remediation lead |
| MySQL-backed schema validation and browser smoke checks were not executed in this implementation turn | Final certification is repo-complete but not deployment-complete | Run `.\scripts\db\validate-schema.ps1` and the manual smoke list before external release | release owner | 2026-05-10 | release owner |
