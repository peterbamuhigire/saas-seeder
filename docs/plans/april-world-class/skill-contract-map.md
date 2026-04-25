# Skill Contract Map

**Owner:** remediation lead, with domain owners maintaining their artifact rows.  
**Update trigger:** update when a skill expectation, deliverable path, validation artifact, or domain AGENTS policy changes.

## Purpose

This file maps the enhanced skills to the concrete artifacts this remediation plan must produce.

## Artifact Mapping

| Skill | Contract domain | Required artifacts in this repo | Validation evidence |
|---|---|---|---|
| `skill-composition-standards` | Governance/release | `docs/plans/april-world-class/README.md`, `scorecard.md`, `dependency-map.md`, `risk-register.md`, release evidence bundle, artifact index, accepted exceptions. | Phase evidence notes, final scorecard, exception review. |
| `system-architecture-design` | Architecture | `docs/architecture/context-map.md`, `container-map.md`, `module-boundaries.md`, `critical-flows.md`, `failure-modes.md`, `dependency-view.md`, ADRs. | ADR review, critical-flow coverage map, no blocking TBD scan. |
| `database-design-engineering` | Data | `docs/data/entity-model.md`, `access-patterns.md`, `invariants.md`, migration runner, schema checks, rollback policy. | Migration validation, schema diff evidence, least-privilege review. |
| `api-design-first` | API | `docs/api/openapi.yml`, `auth-model.md`, `error-model.md`, `rate-limit-policy.md`, `idempotency-map.md`, examples, endpoint tests. | OpenAPI/example review, API smoke tests, error envelope tests. |
| `php-modern-standards` | PHP/runtime | PHP 8.3 Composer constraint, strict types, typed DTOs/services, PHPStan or Psalm, formatter, Composer scripts. | PHP lint, static-analysis report, unit/feature tests. |
| `modular-saas-architecture` | Modules/tenancy | `docs/modules/manifest-contract.md`, `src/Modules/*`, module registry migrations, tenant gates, disabled-module tests. | Module registry tests, tenant-scope checks, menu/route gate checks. |
| `webapp-gui-design` | UI shell | `src/UI/*`, `docs/design-system/*`, shell contract, state matrix, tenant/module UI examples. | UI smoke checks, visual state review, accessibility evidence. |
| `design-audit` | UI quality | UI audit checklist, accessibility/visual QA gates, final design audit. | Contrast, keyboard, focus, responsive review notes. |
| `form-ux-design` | Forms | Form primitives, auth form normalization, validation summary, form accessibility tests. | Form validation tests, error-summary review, keyboard checks. |

## Security Artifact Coverage

| Security expectation | Artifact path | Owning phase |
|---|---|---:|
| Token storage avoids raw refresh-token persistence. | `docs/security/token-storage-policy.md`, token lifecycle tests. | 04 |
| API auth and error behavior is contract-first. | `docs/api/auth-model.md`, `docs/api/error-model.md`, `docs/api/openapi.yml`. | 03 |
| Rate limits and CORS are centrally governed. | `docs/api/rate-limit-policy.md`, security header/CORS docs. | 03, 07 |
| Threat model and release risks are explicit. | `docs/security/threat-model.md`, `docs/plans/april-world-class/risk-register.md`. | 07, 11 |

## Freshness Rules

| Artifact type | Refresh trigger |
|---|---|
| ADR | Any irreversible architecture or security decision. |
| OpenAPI | Any API endpoint/schema/error/auth change. |
| Entity model | Any table/entity lifecycle change. |
| Access patterns | Any new query-heavy feature or module. |
| Threat model | Any auth, token, CORS, module, or permission change. |
| UI system docs | Any new primitive or changed shell contract. |
| Test plan | Any new risk category or release gate. |
| Release evidence | Every release candidate. |
