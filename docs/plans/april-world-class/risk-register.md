# Risk Register

**Owner:** remediation lead.  
**Update trigger:** update at the end of every phase, when severity changes, when mitigation evidence lands, or when a risk expiry passes.

| ID | Risk | Severity | Phase | Owner | Mitigation | Expiry |
|---|---|---:|---:|---|---|---|
| R1 | Token model remains inconsistent across API docs, login, refresh, logout, and DB sessions. | Critical | 04 | API/security owner | Token lifecycle ADR, endpoint rewrite, feature tests. | Phase 04 exit |
| R2 | Refresh/logout endpoints keep runtime references to missing classes/helpers. | Critical | 03-04 | API owner | API runtime foundation, static scan, endpoint tests. | Phase 04 exit |
| R3 | Runtime DDL remains in public registration path. | High | 05 | Data owner | Migration governance and app-user least privilege. | Phase 05 exit |
| R4 | Module access remains a stub, causing false multi-tenant/module confidence. | High | 06 | Architecture owner | Registry tables, service tests, disabled route tests. | Phase 06 exit |
| R5 | Security headers remain inconsistent across root/admin/member/auth pages. | High | 07, 09 | Security owner | Centralized header policy and HTTP header tests. | Phase 07 exit |
| R6 | UI shell fixes only visible pages, not future module patterns. | High | 09 | UI owner | PHP UI primitives, docs, example pages. | Phase 09 exit |
| R7 | Refactors break auth behaviour without tests. | High | 08, 10 | PHP/testing owner | Build tests before major service decomposition. | Phase 10 exit |
| R8 | PHP path remains local-machine specific, breaking scripts. | Medium | 10 | Testing/tooling owner | `find-php.ps1` and script wrappers. | Phase 10 exit |
| R9 | Documentation drifts after implementation. | Medium | 01, 12 | Documentation owner | AGENTS guidance, final stale-text scans. | Phase 12 exit |
| R10 | 99.99 score becomes subjective. | Medium | 01, 12 | Remediation lead | Weighted scorecard and evidence-linked certification. | Phase 12 exit |

## Review Cadence

Update this file at the end of every phase. No critical risk may remain open at final certification without an accepted exception.
