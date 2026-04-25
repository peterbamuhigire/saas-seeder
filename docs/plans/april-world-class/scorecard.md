# 99.99 Scorecard

**Owner:** remediation lead, with dimension evidence supplied by each phase owner.  
**Update trigger:** update when baseline findings change, acceptance evidence lands, an exception is accepted, or a phase changes the scoring model.

## Scoring Method

Each dimension is scored from 0 to 100. The final score is the weighted average. A dimension can only score 99.99 when it has:

- documented contract,
- implemented controls,
- automated validation where practical,
- manual evidence where automation is not practical,
- release evidence link,
- no open critical/high findings without accepted exception.

The final numeric score is calculated as:

```text
sum(dimension score * dimension weight) / 100
```

Scores below 99.99 must cite the missing evidence or accepted exception. A dimension with an open critical finding is capped at 89.99. A dimension with an open high finding is capped at 94.99 unless the risk has a time-boxed accepted exception.

## Dimension Weights

| Dimension | Weight | 99.99 Evidence |
|---|---:|---|
| Governance and documentation integrity | 8 | AGENTS map complete, no dead doc references, active plan status current. |
| Architecture contracts | 8 | Context map, critical flows, ADRs, failure modes, dependency view. |
| API contract/runtime consistency | 10 | OpenAPI, error model, runtime tests, no missing endpoint dependencies. |
| Auth and token security | 12 | Token lifecycle tests, refresh/logout coherence, secret fail-closed tests. |
| Database and migration governance | 10 | Migration runner, schema checks, rollback policy, no runtime DDL. |
| Modular SaaS readiness | 8 | Module registry, tenant-scoped gates, navigation integration, tests. |
| PHP modern standards | 8 | PHP 8.3 constraint, static analysis, formatting, strict types, typed services. |
| UI/UX product shell | 8 | Shared shell, tokens, primitives, state matrix, tenant/module UX. |
| Accessibility | 7 | Landmarks, forms, focus, contrast, keyboard, automated/manual checks. |
| Automated quality gates | 8 | CI, PHPUnit, static analysis, lint, one-command local check. |
| Operations and release evidence | 7 | Runbooks, SLOs, threat model, release and rollback evidence. |
| Developer onboarding | 6 | Fresh install, first module path, script reliability, clear docs. |

## Current Baseline

Baseline from April analysis: **72/100**.

| Dimension | Baseline | Target |
|---|---:|---:|
| Governance and documentation integrity | 78 | 99.99 |
| Architecture contracts | 70 | 99.99 |
| API contract/runtime consistency | 42 | 99.99 |
| Auth and token security | 76 | 99.99 |
| Database and migration governance | 80 | 99.99 |
| Modular SaaS readiness | 45 | 99.99 |
| PHP modern standards | 70 | 99.99 |
| UI/UX product shell | 64 | 99.99 |
| Accessibility | 67 | 99.99 |
| Automated quality gates | 30 | 99.99 |
| Operations and release evidence | 44 | 99.99 |
| Developer onboarding | 62 | 99.99 |

## Certification Rule

Final certification requires every dimension to reach 99.99 or to have a documented accepted exception in `known-exceptions.md` with:

- reason,
- impact,
- mitigation,
- owner,
- expiry date.

## Acceptance Evidence Rules

| Evidence type | Minimum requirement |
|---|---|
| Contract | A versioned doc path, ADR, OpenAPI section, schema note, or UI contract path exists and is linked from the relevant phase. |
| Implementation | Code, migration, script, or UI artifact exists at the path named by the phase deliverable. |
| Automated validation | Test, lint, static analysis, schema check, or script output is captured in phase evidence. |
| Manual validation | Review notes identify reviewer, date, scope, and unresolved findings. |
| Release evidence | The release bundle links the validation output and rollback or exception decision. |
