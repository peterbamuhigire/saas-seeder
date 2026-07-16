# Repository 95 Remediation Spec

Status: completed  
Owner: engineering and design quality owner  
Started: 2026-07-16  
Baseline: 55/100, with a user-requested ceiling of 65 for the pre-remediation score  
Target: 95/100 on the same evidence-backed rubric  
Completed: 2026-07-16  
Final evidence: `docs/plans/repo-95/final-scorecard.md` and `docs/plans/repo-95/verification.md`

## Problem Frame

SaaS Seeder has a credible PHP 8.3 auth, RBAC, token, module, and migration core, but its release claims exceed its reproducible evidence. The documented local server cannot route `/api/**`, the PowerShell quality gate treats missing PHPStan as success, the public UI fails the design engine's typography and accessibility gates, and completed-plan documents award 99.99 scores despite unrun database and browser checks.

The remediation must make the starter safer to adopt, easier to verify, and visibly authored without replacing its custom PHP plus Tabler architecture.

## Users and Critical Jobs

| User | Critical job | Failure consequence |
|---|---|---|
| Adopting engineer | Install, configure, run, and verify the starter without machine-specific guesswork | A broken first run or false-green quality gate enters a downstream product |
| SaaS operator | Create the first admin, sign in, recover access, and understand tenant context | Account lockout, unsafe setup exposure, or cross-tenant ambiguity |
| Application user | Complete auth flows with clear, accessible feedback | Blocked login, inaccessible controls, or account enumeration |
| Reviewer | Trace production-readiness claims to commands, tests, and rendered evidence | Unsupported certification or missed release risk |

## Fixed Rubric

The baseline and final score use ten equally weighted dimensions. Each dimension is scored from 0 to 10; no post-remediation dimension may rely on an unrun check.

| Dimension | Baseline | Target | Baseline evidence |
|---|---:|---:|---|
| Product integrity and flow completeness | 5 | 9 | Web registration and password recovery are placeholders; the API route is unreachable through the documented server |
| Visual system and authored design quality | 4 | 10 | Inter is imported on auth/error/setup screens; repeated inline styles; broken logo path; generic gradient error screen |
| Accessibility and responsive behavior | 5 | 10 | Password toggle is about 18x18 CSS px; auth pages lack an `h1`; low-contrast small labels; no complete manual/screen-reader evidence |
| Architecture and maintainability | 8 | 9 | Strong modular services and ADRs, offset by procedural entry points, a 500-line permission service, and duplicate page shells |
| Security and privacy | 6 | 10 | Strong hashing, token rotation, CSRF, and prepared statements; public session diagnostic, report-only unsafe-inline CSP, account-enumerating web errors |
| API, data, and tenant discipline | 7 | 9 | Governed migrations and tenant-aware services; local API routing broken and live MySQL verification not reproduced at baseline |
| Tests and enforceable quality gates | 5 | 9 | 57 tests and 267 assertions pass; line coverage is 26.05%; PHPStan is missing and silently skipped |
| Operations and developer experience | 5 | 10 | Scripts hard-code PHP 8.3.28 and a stale MySQL path; documented API URL is false under the current server command |
| Performance and supply-chain discipline | 6 | 9 | Composer audit is clean and auth route weight is modest; tracked public assets total 122.43 MiB with no asset budget/security.txt gate |
| Documentation and evidence integrity | 3 | 10 | README licence conflicts with `composer.json` and `LICENSE`; completed evidence asserts 99.99 and unqualified production readiness |
| **Total** | **55/100** | **95/100** | Pre-remediation score is below the requested 65 ceiling |

The design surface is separately governed by the design-engine rubric. Its baseline cannot exceed 59 because both the AI-slop typography gate and WCAG 2.2 AA target-size gate fail.

## Accepted Architecture

- Preserve the modular monolith and existing `public/`, `api/`, `src/`, `database/`, `scripts/`, and `docs/` boundaries.
- Keep browser sessions and API bearer-token flows distinct while sharing credential policy and audit services.
- Add reusable UI/auth assets and small testable services before adding page-specific code.
- Keep the production document root at `public/`. A development router may dispatch `/api/**` to the separate API entry points without exposing `src/`, `vendor/`, `database/`, or `.env`.
- Treat setup and diagnostic entry points as deny-by-default outside an explicit local development environment.

## Remediation Workstreams

### 1. Release integrity and developer tooling

- Make missing PHPStan, PHP CS Fixer, PHPUnit, Composer, PHP, or MySQL clients fail with an actionable error.
- Remove hard-coded runtime versions from setup/server scripts and reuse deterministic discovery helpers.
- Align the local PowerShell gate with `composer check` and CI.
- Add a safe development router and verify both web and API routes.

### 2. Security and privacy

- Guard or remove raw session diagnostics and development-only setup surfaces.
- Replace account-enumerating login copy with a generic response.
- Enforce a project-owned CSP strategy, remove external font loading, and cover security headers with tests.
- Add `/.well-known/security.txt` and verify no public entry point omits the common policy.
- Preserve prepared statements, Argon2id, CSRF, token rotation, refresh reuse detection, rate limits, and tenant-aware authorization.

### 3. Design, accessibility, and auth UX

- Replace banned Inter with a licensed, self-hosted display/body pairing.
- Consolidate repeated auth CSS and JavaScript into shared assets.
- Repair the brand mark, use one semantic `h1`, visible labels, 44px touch targets, focus-visible states, reduced-motion handling, and descriptive async states.
- Make placeholder flows truthful: do not present an active reset or registration action unless its backend contract is available.
- Rework the access-denied surface away from template gradients and ambient animation.

### 4. Tests, CI, and evidence

- Restore Composer development dependencies and run PHPStan plus PHP CS Fixer.
- Add regression tests for dev-route guards, headers/CSP, banned-font removal, asset existence, auth semantics, scripts, and routing.
- Update CI to exercise MySQL migrations/schema checks where feasible.
- Record coverage as evidence; improve risk coverage without chasing a misleading vanity percentage.

### 5. Documentation truth

- Replace the April 99.99 certification claim with historical context and explicit limitations.
- Align licence, prerequisites, commands, route documentation, test counts, and release evidence with actual files and command output.
- Produce a final evidence pack with commands, results, residual risks, rollback, and design-gate verdict.

## Validation Plan

Required automated evidence:

```powershell
.\scripts\quality\check.ps1
.\scripts\db\validate-schema.ps1
```

Additional evidence:

- `composer audit --locked --no-interaction`
- PHPUnit coverage report with Xdebug when available
- HTTP smoke tests for web, API, asset, diagnostic, and security.txt routes
- Browser inspection at 390px and the default desktop viewport
- Keyboard traversal of the sign-in, recovery, registration, and denial flows
- Static scans for banned fonts, missing strict types, inline executable code, secrets, and broken local assets

## Rollback and Reversal Triggers

- UI changes are reversible by restoring the shared auth assets and page markup together.
- The development router affects only the PHP built-in server; production web-server rules remain unchanged.
- No destructive database migration is planned. If a schema change becomes necessary, add a numbered migration, rollback posture, and validation evidence before implementation.
- Reopen the typography decision if local font licensing, script coverage, or rendering evidence fails.

## Exit Criteria

- The final score is at least 95/100 on this rubric with no arithmetic change to weights.
- No design, accessibility, security, or quality-gate blocker remains open.
- All automated checks that exist are run; unavailable checks are not reported as passes.
- Key public and authenticated shells are rendered and inspected at desktop and mobile widths.
- Documentation claims match current code, test counts, routes, licence, and residual risk.
