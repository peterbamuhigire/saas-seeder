# SaaS Seeder April 2026 Analysis

**Date:** 2026-04-26  
**Target:** SaaS Seeder Template at `C:\wamp64\www\saas-seeder`  
**Reviewer:** Codex, AI-assisted engineering review  
**Skill baseline studied:** enhanced `C:\Users\Peter\.claude\skills`, with emphasis on PHP/Tabler SaaS UI, practical UI design, design audit, PHP modern standards, modular SaaS architecture, API design first, database design engineering, system architecture design, form UX, and skill composition standards.

## Executive Summary

The seeder has moved meaningfully beyond the March 2026 audit. Several items that were previously critical are now fixed in the current tree: session hardening directives exist, `PASSWORD_PEPPER` now fails closed, cookies use AES-256-GCM, JWTs include `iss` and `aud`, the base migration has `tbl_franchises`, foreign keys, `utf8mb4_unicode_ci`, `ROW_FORMAT=DYNAMIC`, `tbl_franchise_role_overrides`, and `tbl_audit_log`, `composer.lock` is committed, and the shared topbar includes a skip link.

The template is still not yet "world-class SaaS scaffolding" by the bar set by the enhanced skills. It is a solid auth/RBAC seed with a useful three-panel shell, but it still has fragile API endpoints, incomplete module registry infrastructure, uneven panel includes, missing API contract artifacts, no automated test suite, no static analysis tooling, no release evidence bundle, and UI scaffolding that is functional but not yet a polished, reusable product shell.

**Overall readiness:** 72/100  
**Production auth/RBAC starter readiness:** 78/100  
**Reusable SaaS product scaffold readiness:** 66/100  
**Enhanced-skill alignment:** 63/100

The highest-impact next move is not another visual cleanup. The top priority is to make the scaffold contract-driven: stabilize all API auth endpoints, add OpenAPI/error/auth artifacts, add a module registry, add tests/static analysis, and turn the UI shell into documented primitives with consistent states.

## What I Reviewed

### Repository Areas

- `src/Auth/*`: authentication, tokens, permissions, cookies, CSRF, DTOs, audit service.
- `src/config/*`: session, auth helpers, database, autoloader.
- `api/bootstrap.php` and `api/v1/*`: API bootstrap and auth endpoints.
- `public/*`: auth pages, dashboards, three-panel includes, topbar/menu/footer assets.
- `docs/seeder-template/migration.sql`: base schema and stored procedures.
- `docs/29-march-evaluation/*`: prior audit baseline.
- `docs/plans/template-standards/*`: active remediation plan.
- `composer.json`, `composer.lock`, `.gitignore`, scripts and docs structure.

### Enhanced Skill Material Studied

- `webapp-gui-design`: PHP/Tabler stack, API-first rule, three-panel structure, menus, state completeness, SaaS UX rules.
- `practical-ui-design`: token systems, contrast, spacing, typography, component state requirements.
- `design-audit`: severity model, AI-slop detection, hierarchy, accessibility, performance and microcopy checks.
- `form-ux-design`: labels, form flow, validation, error anatomy, accessibility.
- `php-modern-standards`: strict types, PSR-4, final classes, DTO shape, tooling, security patterns.
- `modular-saas-architecture`: per-tenant modules, module registry, feature gates, dynamic navigation.
- `api-design-first`: OpenAPI-first workflow, error model, idempotency, rate limits, observability notes.
- `database-design-engineering`: tenant keys, invariants, access-pattern registers, migration posture.
- `system-architecture-design`: context maps, critical flows, ADRs, failure and release design.
- `skill-composition-standards`: artifact contracts, evidence outputs, normalised deliverables.

## Verification Performed

| Check | Result | Notes |
|---|---:|---|
| PHP version through WAMP path | PASS | `C:\wamp64\bin\php\php8.3.28\php.exe` reports PHP 8.3.28. |
| PHP on `PATH` | FAIL | `php` is not available directly in PowerShell. Scripts relying on bare `php` will fail unless WAMP PHP is added to `PATH` or scripts resolve it. |
| PHP syntax lint | PASS with warning | All non-asset PHP files linted cleanly under PHP 8.3.28. Warning: `api/v1/auth/refresh.php` has a non-compound `use DateTime` statement. |
| Composer validate | WARN | `composer.json` is valid but lacks a `license` field. |
| Test suite | NOT PRESENT | No `tests/` directory found. `phpunit` exists as a dev dependency, but there is no project test harness visible. |
| Static analysis / formatter config | NOT PRESENT | No visible PHPStan, Pint, PHP-CS-Fixer, or PHPCS config. |

## Current Strengths

### 1. Clear Project Identity

The template has a coherent purpose: a reusable authentication and RBAC foundation for SaaS prototypes. The README, root `AGENTS.md`, and `docs/overview/README.md` all reinforce the same concept: PHP 8.3, MySQL, Tabler, sessions, JWT, RBAC, multi-tenant readiness, and a three-tier panel structure.

### 2. Three-Panel Architecture Is Useful

The split between:

- `/public/` as the franchise/admin workspace,
- `/public/adminpanel/` as the super admin workspace,
- `/public/memberpanel/` as the end-user portal,

is a strong scaffold shape for many SaaS builds. It gives future projects a clear mental model before product-specific modules are added.

### 3. Security Baseline Has Improved Since March

Current code includes several important fixes:

- `src/config/session.php` sets strict session directives.
- `src/Auth/Helpers/PasswordHelper.php` throws if `PASSWORD_PEPPER` is missing.
- `src/Auth/Helpers/CookieHelper.php` uses AES-256-GCM.
- `src/Auth/Services/TokenService.php` adds JWT `iss`, `aud`, `jti`, expiry, and permission-version checks.
- `public/includes/security-headers.php` exists and is included by root `public/includes/head.php`.
- `docs/seeder-template/migration.sql` includes core tenancy, FKs, collation, row format, lockout fields, and audit tables.

### 4. Auth Single Source of Truth Is Mostly in Place

Web sign-in and API login both route through `AuthService`, `TokenService`, `PermissionService`, `PasswordHelper`, and `CookieHelper`. User creation is centralised through `UserService`. That is the right direction for a seeder: one auth path, multiple surfaces.

### 5. Documentation Governance Is Better Than Typical Starters

The repo has:

- a canonical docs landing page,
- plans under `docs/plans/`,
- previous audit outputs,
- implementation notes,
- quick references,
- setup scripts under `scripts/<category>/`.

That is a good basis for AI-agent collaboration and future maintainers.

## Critical Findings

### C1. Several API Auth Endpoints Reference Missing Infrastructure

**Severity:** Critical  
**Area:** API correctness  
**Files:** `api/v1/auth/refresh.php`, `api/v1/auth/logout.php`, `api/v1/auth/logout-all.php`

The API login endpoint is aligned with the current bootstrap and auth services, but refresh/logout endpoints are from a different API architecture. They reference:

- `App\Http\Auth\JwtService`
- `App\Http\Auth\RefreshTokenStore`
- `require_method()`
- `read_json_body()`
- `bearer_token()`
- `json_response()`
- `get_db()`
- `api/v1/auth/middleware.php`

Those classes/functions were not found in `src/` or `api/`. PHP lint does not catch this because the missing symbols are runtime dependencies. These endpoints will fail when called.

**Why this matters:** The template claims REST auth endpoints, but token refresh and logout are not executable. For a SaaS starter, broken auth endpoints are a trust-breaking defect.

**Fix:**

1. Either rewrite refresh/logout/logout-all to use the current `api/bootstrap.php`, `jsonResponse()`, `errorResponse()`, and `TokenService`.
2. Or add the missing `App\Http\Auth` layer and middleware consistently.
3. Add API integration tests for login, refresh, logout, logout-all.
4. Document the actual auth token model in an OpenAPI spec.

### C2. API Contract Artifacts Are Missing

**Severity:** Critical  
**Area:** API design and skill-contract compliance  
**Files:** `docs/api/`, `api/v1/*`

The enhanced `api-design-first` skill requires OpenAPI, auth model, error model, idempotency map, observability notes, rate limits, and examples. The current repo has narrative API docs, but no visible OpenAPI contract or standard error model artifact.

**Why this matters:** This seeder is intended to kickstart world-class SaaS apps. Without a contract, mobile clients, frontend modules, SDK generation, contract tests, and security review all depend on reading PHP code.

**Fix:**

- Add `docs/api/openapi.yml`.
- Add `docs/api/error-model.md`.
- Add `docs/api/auth-model.md`.
- Add `docs/api/idempotency-map.md`.
- Add `docs/api/observability-notes.md`.
- Add contract tests or at minimum a `docs/api/contract-test-plan.md`.

### C3. Module Registry Is Only a Stub

**Severity:** Critical for scaffolding ambition, High for current auth starter  
**Area:** Modular SaaS architecture  
**Files:** `src/config/auth.php`, `docs/seeder-template/migration.sql`

The enhanced modular SaaS skill expects:

- `tbl_modules`
- `tbl_franchise_modules`
- real `hasModuleAccess()`
- real `requireModuleAccess()`
- dynamic navigation by enabled module
- audit events for enable/disable

Current code has stub functions in `src/config/auth.php` that always return true. The base migration does not define `tbl_modules` or `tbl_franchise_modules`.

**Why this matters:** The project identity says "multi-tenant readiness" and the skills now target pluggable business modules. The current template supports tenant-scoped auth, but not module-gated SaaS scaffolding.

**Fix:**

1. Add module registry tables to the base migration.
2. Implement `ModuleRegistry` service.
3. Replace stub `hasModuleAccess()` and `requireModuleAccess()`.
4. Add menu gating by module and permission.
5. Add a sample disabled-module empty state.

### C4. Refresh Token Model Is Inconsistent

**Severity:** Critical  
**Area:** Auth architecture  
**Files:** `src/Auth/Services/TokenService.php`, `api/v1/auth/refresh.php`, `docs/seeder-template/migration.sql`

`TokenService` issues 15-minute JWT access tokens and stores sessions. The refresh endpoint expects a separate refresh-token infrastructure that is absent. The login endpoint only returns `access_token`; it does not return a refresh token. The docs/endpoint names imply refresh is supported, but the implemented service chain does not provide a complete refresh-token lifecycle.

**Fix:**

- Choose one model:
  - session-backed access token only, no refresh endpoint, or
  - access + refresh tokens with hashed refresh storage, rotation, reuse detection, and logout-all support.
- Update API docs, migration, endpoints, and tests to match the chosen model.

## High Findings

### H1. Security Headers Are Not Applied Uniformly Across Panels and Auth Pages

**Severity:** High  
**Area:** HTTP security  
**Files:** `public/includes/head.php`, `public/adminpanel/includes/head.php`, `public/memberpanel/includes/head.php`, auth pages

The root `public/includes/head.php` includes `public/includes/security-headers.php`. The adminpanel and memberpanel `head.php` files shown during review do not include that shared header. Auth pages such as `sign-in.php` render their own `<head>` and do not include the shared security header file.

**Why this matters:** Headers should be a platform invariant, not panel-specific. A user can land directly on auth pages and panel pages.

**Fix:**

- Include `security-headers.php` from every head include and standalone auth page before output.
- Prefer one shared `HeadRenderer` or include path for all panels.
- Verify with `curl -I` for `sign-in.php`, `dashboard.php`, `adminpanel/`, `memberpanel/`, and API endpoints.

### H2. Skip Link Target Is Incomplete

**Severity:** High  
**Area:** Accessibility  
**Files:** `public/includes/topbar.php`, panel pages

The shared topbar links to `#main-body`, but reviewed dashboard pages use `.page-body` without `id="main-body"` in several places. `public/skeleton.php` uses `id="main-content"` on a card body, which does not match the skip link target.

**Why this matters:** The visible skip link is present, but it may not move focus/scroll to the main content. This creates a false accessibility pass.

**Fix:**

- Add `id="main-body" tabindex="-1"` to the primary content wrapper on every shell page.
- Use a `<main id="main-body">` element where possible.
- Add standalone skip links to auth pages that do not use the topbar.

### H3. Panel Include Duplication Will Drift

**Severity:** High  
**Area:** Architecture and UI consistency  
**Files:** `public/includes/*`, `public/adminpanel/includes/*`, `public/memberpanel/includes/*`

There are separate include folders for each panel, but during review their head/foot contents were effectively duplicates. Root includes have security header improvements that panel-specific includes do not show. This is exactly the drift the enhanced UI skills warn against.

**Fix:**

- Keep separate panel menu files if needed, but centralise shared head, foot, security headers, and base topbar rendering.
- If panel-specific wrappers remain, make them delegate to shared includes.
- Add a "panel include contract" doc under `docs/implementation/`.

### H4. No Automated Tests for Security-Critical Behaviour

**Severity:** High  
**Area:** Quality gates  
**Files:** repo-wide

There is no `tests/` directory. PHPUnit is listed in `composer.json`, but no test harness is present. No visible test configuration exists.

**Highest-risk missing tests:**

- Password hash and verify, including old/new salt prefixes.
- Missing pepper/JWT/cookie keys fail closed.
- Cookie tampering fails under AES-GCM.
- Login lockout and reset.
- Session prefix helpers.
- Permission override precedence.
- JWT issuer/audience/permission-version validation.
- API endpoint contract tests.

**Fix:** Add a small PHPUnit suite before adding more features. For a seeder, tests are part of the product.

### H5. Static Analysis and Formatting Gates Are Missing

**Severity:** High  
**Area:** PHP modern standards  
**Files:** `composer.json`, repo root

The enhanced PHP skill expects code quality tooling. The repo currently lacks PHPStan/Psalm and a formatter config. The code has several style and type inconsistencies that static analysis would catch early.

**Fix:**

- Add PHPStan or Psalm at a practical starting level.
- Add Laravel Pint, PHP-CS-Fixer, or PHPCS for formatting.
- Add Composer scripts:
  - `composer lint`
  - `composer analyse`
  - `composer test`
  - `composer check`

### H6. DTOs Are Final But Not Modern Value Objects

**Severity:** High  
**Area:** PHP architecture  
**Files:** `src/Auth/DTO/AuthResult.php`, `src/Auth/DTO/LoginDTO.php`

The DTOs use private mutable properties and boilerplate getters. They are `final`, but not `readonly`, and do not use constructor promotion. The enhanced PHP standard explicitly calls out `final readonly` DTOs as the preferred shape.

**Fix:** Convert DTOs to `final readonly class` with constructor-promoted properties. Keep getters only if existing code depends on them.

### H7. API Error Envelope Is Inconsistent With Enhanced Standard

**Severity:** High  
**Area:** API design  
**Files:** `api/bootstrap.php`, `api/v1/*`

Current errors return:

```json
{
  "success": false,
  "message": "...",
  "errors": null
}
```

The enhanced API skill expects a structured error object with stable `code`, `message`, `documentation_url`, and field errors when relevant.

**Fix:** Introduce a canonical error response helper and migrate endpoints gradually.

### H8. `composer.json` Still Says PHP 8.0+

**Severity:** High  
**Area:** Platform consistency  
**File:** `composer.json`

The root project identity says PHP 8.3, and verification used PHP 8.3.28. `composer.json` still allows `php >=8.0`.

**Why this matters:** It weakens the contract. If the template is PHP 8.3, code can safely use newer language features and the dependency resolver should enforce that.

**Fix:** Change to `php: ^8.3` if PHP 8.3 is the actual minimum. If the template intentionally supports 8.0, update project identity and standards to match.

## Medium Findings

### M1. `php` Is Not on PATH

**Severity:** Medium  
**Area:** Developer experience  
**Evidence:** `php -v` failed, direct WAMP path succeeded.

PowerShell scripts and README commands that call `php` directly will fail in the current environment unless PATH is configured.

**Fix:**

- Update scripts to locate WAMP PHP automatically or document adding `C:\wamp64\bin\php\php8.3.28` to PATH.
- Prefer script wrappers under `scripts/server/` and `scripts/setup/` that resolve PHP consistently.

### M2. Composer Metadata Is Incomplete

**Severity:** Medium  
**Area:** Supply chain / packaging  
**File:** `composer.json`

`composer validate --strict` warns that no license is specified. The repo has a `LICENSE` file, but Composer metadata should declare it.

**Fix:** Add `"license": "GPL-3.0-or-later"` if that matches `LICENSE`, or `"proprietary"` if this template is not meant to be distributed under the root license.

### M3. AuthService Has Formatting and Responsibility Creep

**Severity:** Medium  
**Area:** Maintainability  
**File:** `src/Auth/Services/AuthService.php`

`AuthService::authenticate()` is doing many things:

- franchise lookup,
- stored procedure call,
- manual fallback auth,
- password verification,
- user data fallback,
- token generation,
- session writes,
- timezone setup,
- failed-attempt reset,
- permissions fetch.

It is also visually hard to review because indentation is inconsistent near method braces.

**Fix:** Extract private methods for lookup, SP/manual auth, session hydration, and user context hydration. Keep public behaviour unchanged.

### M4. `Database::getInstance()` Exists But Call Sites Still Instantiate Directly

**Severity:** Medium  
**Area:** Performance / consistency  
**Files:** `src/config/database.php`, `src/config/auth.php`, `public/sign-in.php`, API endpoints

`Database::getInstance()` was added, but many call sites still use `new Database()`. That leaves the singleton underused and makes connection reuse unpredictable.

**Fix:** Standardise on either injected PDO from composition root or `Database::getInstance()`. For a seeder, dependency injection via bootstrap is cleaner.

### M5. API Signup Creates Table at Runtime

**Severity:** Medium  
**Area:** Database governance  
**File:** `api/v1/public/auth/register.php`

`ensureSignupTable()` creates `tbl_api_signup_requests` at runtime. The same table exists in `docs/seeder-template/migration.sql`, so runtime DDL should not be necessary.

**Why this matters:** The enhanced database skill expects migrations as the source of truth. Runtime DDL hides schema drift and can fail under least-privilege production DB users.

**Fix:** Remove runtime table creation after setup scripts reliably apply the migration. Keep a clear error if the table is missing.

### M6. Menu Rules Conflict With Enhanced Skill Guidance

**Severity:** Medium  
**Area:** UI consistency  
**Files:** menu includes, `webapp-gui-design/sections/03-architecture-panels-menus.md`

The enhanced PHP/Tabler section says menu entries must use PNG icons, not Bootstrap Icons. Current menus still use Bootstrap Icons in dropdown entries and placeholder `href="#"` links.

There is also an internal contradiction across skill files: the top-level webapp skill says Bootstrap Icons only, while the PHP menu section says PNGs are mandatory for menus. For this repo, the section is more specific to the PHP seeder stack and should win.

**Fix:**

- Decide and document the seeder menu icon standard.
- If PNGs win, create `public/dist/img/icons/` or adjust the standard to the actual asset path.
- Replace placeholder menu links with real disabled states, planned routes, or remove them.

### M7. UI Shell Is Functional But Not Yet a Product-Grade System

**Severity:** Medium  
**Area:** UI/UX  
**Files:** `public/dashboard.php`, `public/adminpanel/index.php`, `public/memberpanel/index.php`, includes

The panels have basic Tabler empty states, but they do not yet demonstrate the richer SaaS shell expected by the enhanced UI skills:

- no tenant switcher,
- no global search,
- no breadcrumb system,
- no dashboard KPI strip,
- no reusable data-table primitive,
- no documented empty/loading/error/success state matrix,
- no design tokens beyond Tabler defaults,
- no interface signature specific to the seeder product.

**Fix:** Create a canonical "seeded product shell" with dashboard, table, form, detail, settings, and empty-state examples.

### M8. Documentation Plan Status Is Stale

**Severity:** Medium  
**Area:** Planning governance  
**Files:** `docs/plans/template-standards/*.md`

The active March plan still marks many tasks as `not-started`, but the current tree shows several are implemented. This makes the plan less useful as a source of truth.

**Fix:** Update plan statuses or add an April completion/status report that maps March findings to current state.

### M9. No Release Evidence Bundle

**Severity:** Medium  
**Area:** Skill composition / release readiness

The enhanced skills increasingly expect evidence artifacts: context map, critical-flow table, entity model, access patterns, OpenAPI, threat model, SLOs, test plan, release plan, rollback plan, runbook. This repo has plans and audits, but not a coherent release evidence bundle.

**Fix:** Add `docs/release-evidence/` or `docs/april-2026-analysis/release-evidence-gap.md` in a follow-up.

## Low Findings

### L1. `api/v1/auth/refresh.php` Has a Harmless Lint Warning

`use DateTime;` has no effect in the global namespace. Use `new \DateTime()` or remove the `use`.

### L2. Root README Has Encoding Artifacts

The README contains mojibake-style characters from prior Unicode rendering. This does not break the scaffold, but it reduces polish.

### L3. Auth Pages Use Custom Inline CSS

Standalone auth pages define substantial inline CSS. That is acceptable for a small starter, but a reusable seeder should move stable auth patterns into shared assets or documented templates.

### L4. Button Text Casing Is Inconsistent

Some labels use title case, such as "Sign In" and "View page template". The enhanced writing/form skills generally prefer outcome-oriented sentence case. This is polish, not a blocker.

## Scorecard

| Dimension | Score | Assessment |
|---|---:|---|
| Project identity | 86 | Clear mission, coherent docs, useful panel model. |
| Auth correctness | 76 | Stronger than March, but refresh/logout model is broken/incomplete. |
| Session/JWT/cookie security | 82 | Good improvements. Needs endpoint tests and complete refresh design. |
| RBAC / permissions | 74 | Global roles and overrides are present. Needs more tests and cleaner service boundaries. |
| Multi-tenancy | 72 | Franchise-scoped model exists. Needs tenant switcher and stricter API contract proof. |
| Modular SaaS readiness | 45 | Stub only. Missing registry tables and real module gates. |
| Database design | 80 | Base migration is much improved. Runtime DDL and missing module registry remain. |
| API design | 42 | Login works structurally, but contract artifacts and several endpoints are broken. |
| UI/UX system | 64 | Functional Tabler shell, but not yet a reusable product-grade UI system. |
| Accessibility | 67 | Some fixes exist. Skip target and standalone auth pages need a full sweep. |
| Developer experience | 62 | Scripts/docs exist, but PHP path, tests, tooling, and plan status need work. |
| Documentation governance | 78 | Strong structure. Needs updated status and release evidence bundle. |
| Testability | 30 | PHPUnit dependency exists, but no tests/config found. |
| Release readiness | 44 | No CI/check scripts/evidence bundle/runbooks. |

## Recommended Remediation Roadmap

### Phase 1: Stabilise Auth API

**Goal:** No advertised auth endpoint should fail at runtime.

Tasks:

1. Rewrite or remove `refresh.php`, `logout.php`, and `logout-all.php` dependencies on missing `App\Http\Auth` classes.
2. Decide access-token-only vs access+refresh-token model.
3. Add refresh-token storage if keeping refresh.
4. Standardise `jsonResponse()` and `errorResponse()` across all endpoints.
5. Add integration tests for login, refresh, logout, logout-all.

Exit criteria:

- All auth endpoints run under PHP 8.3.
- API tests cover success and failure paths.
- Docs describe exactly which tokens exist and how revocation works.

### Phase 2: Add API Contract Artifacts

**Goal:** Make the API consumable by frontend/mobile/agents without reading PHP.

Tasks:

1. Add OpenAPI 3.1 contract.
2. Add auth model.
3. Add standard error model.
4. Add rate-limit policy.
5. Add idempotency map.
6. Add observability notes.

Exit criteria:

- Every endpoint has request, response, error, auth, and rate-limit documentation.
- Contract tests or a documented contract-test plan exist.

### Phase 3: Add Module Registry

**Goal:** Turn "multi-tenant ready" into "modular SaaS ready".

Tasks:

1. Add `tbl_modules`.
2. Add `tbl_franchise_modules`.
3. Add `ModuleRegistry` service.
4. Implement real `hasModuleAccess()` and `requireModuleAccess()`.
5. Gate menus by module and permission.
6. Add sample module config convention.

Exit criteria:

- Disabled modules disappear from nav.
- Direct access to disabled module routes is blocked.
- Enable/disable actions are audited.

### Phase 4: Quality Gates

**Goal:** Make the seeder safe to evolve.

Tasks:

1. Add PHPUnit config and focused tests.
2. Add PHPStan or Psalm.
3. Add formatter config.
4. Add Composer scripts for check commands.
5. Add a CI workflow or local verification script.

Exit criteria:

- One command runs lint, static analysis, and tests.
- New projects inherit the same quality gate.

### Phase 5: UI System Upgrade

**Goal:** Move from pages to reusable product primitives.

Tasks:

1. Add a documented shell contract.
2. Add `<main id="main-body" tabindex="-1">` consistently.
3. Add tenant context and optional tenant switcher pattern.
4. Add global search shell placeholder.
5. Add canonical examples for:
   - dashboard KPI strip,
   - server-side data table,
   - settings form,
   - empty/loading/error/success states,
   - permission-denied state,
   - disabled-module state.
6. Decide menu icon standard and enforce it.

Exit criteria:

- A new SaaS module can clone patterns without inventing UI structure.
- The scaffold demonstrates state completeness, not just blank dashboards.

### Phase 6: Release Evidence Bundle

**Goal:** Align with the enhanced skill-composition system.

Artifacts to add:

- `docs/architecture/context-map.md`
- `docs/architecture/critical-flows.md`
- `docs/data/entity-model.md`
- `docs/data/access-patterns.md`
- `docs/security/threat-model.md`
- `docs/api/openapi.yml`
- `docs/api/error-model.md`
- `docs/operations/runbook.md`
- `docs/operations/slo-alert-plan.md`
- `docs/testing/test-plan.md`
- `docs/release/release-plan.md`
- `docs/release/rollback-plan.md`

## March Audit Delta

The current repo appears to have fixed or partially fixed several March findings:

| March issue | Current state |
|---|---|
| Missing session hardening directives | Fixed in `src/config/session.php` and `api/bootstrap.php`. |
| Password pepper fallback | Fixed. Missing pepper now throws. |
| Cookie AES-CBC without HMAC | Fixed with AES-256-GCM. |
| JWT missing issuer/audience | Fixed in `TokenService`. |
| Missing security headers | Partially fixed. Root shared head includes them; panel/auth coverage needs verification/fix. |
| CORS wildcard | Partially fixed. Configurable allow-list exists, with dev wildcard fallback. |
| Migration missing core tables/FKs/collation | Fixed in base migration for core auth tables. |
| Missing audit table | Fixed in migration and `AuditService` exists. |
| `composer.lock` gitignored | Fixed. `.gitignore` no longer ignores it and lock file exists. |
| Missing skip link | Partially fixed. Link exists in topbar, but targets are inconsistent. |
| Missing aria-required/live alerts | Mostly improved in reviewed auth files. Needs final full sweep. |
| Member panel empty CTA | Fixed. |

The plan files under `docs/plans/template-standards/` should be updated to reflect this delta.

## Bottom Line

This is a credible SaaS auth seeder, not yet a world-class SaaS application scaffold. The strongest parts are project identity, tenant-aware auth, improved schema, and the three-panel mental model. The weakest parts are API contract/runtime consistency, module gating, automated verification, and the absence of skill-contract evidence artifacts.

Treat the next iteration as a hardening and productisation pass:

1. Make the advertised API endpoints actually work.
2. Add contracts and tests.
3. Implement real module gates.
4. Centralise shared panel includes.
5. Turn the UI shell into documented primitives with complete states.

Once those are done, this template can credibly serve as the base for high-quality SaaS projects instead of only a good authentication starter.
