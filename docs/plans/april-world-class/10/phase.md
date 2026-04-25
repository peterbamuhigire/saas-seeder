# Phase 10: Automated Tests, CI, Static Analysis, And Quality Gates

## Objective

Create the automated quality system that proves the scaffold remains world-class as it evolves.

## Skills Applied

- `php-modern-standards`
- `api-design-first`
- `database-design-engineering`
- `design-audit`
- `skill-composition-standards`

## Current Problems

- PHPUnit is a dev dependency but no test harness exists.
- No PHPStan/Psalm config exists.
- No formatter config exists.
- No CI workflow exists.
- PHP is not available on PATH in the current environment.
- There is no one-command quality gate.

## Deliverables

Create:

- `phpunit.xml`
- `tests/bootstrap.php`
- `tests/Unit/Auth/PasswordHelperTest.php`
- `tests/Unit/Auth/CookieHelperTest.php`
- `tests/Unit/Auth/AccessTokenServiceTest.php`
- `tests/Unit/Auth/RefreshTokenServiceTest.php`
- `tests/Unit/Auth/PermissionServiceTest.php`
- `tests/Unit/Modules/ModuleAccessServiceTest.php`
- `tests/Unit/Http/ApiResponseTest.php`
- `tests/Unit/Http/RateLimiterTest.php`
- `tests/Feature/Api/AuthLoginTest.php`
- `tests/Feature/Api/AuthRefreshTest.php`
- `tests/Feature/Api/AuthLogoutTest.php`
- `tests/Feature/Api/RateLimitTest.php`
- `tests/Feature/Web/PanelAccessTest.php`
- `tests/Feature/Modules/DisabledModuleAccessTest.php`
- `tests/Feature/Security/HttpHeadersTest.php`
- `tests/Database/MigrationTest.php`
- `tests/Accessibility/ShellAccessibilityTest.php`
- `tests/Ui/UiStaticRulesTest.php`
- `tests/Support/DatabaseTestCase.php`
- `tests/Support/FakeClock.php`
- `tests/Support/FakeRequest.php`
- `tests/Support/Fixtures/`
- `phpstan.neon`
- `.php-cs-fixer.php` or `phpcs.xml`
- `.github/workflows/ci.yml`
- `scripts/quality/check.ps1`
- `scripts/quality/lint-php.ps1`
- `scripts/quality/analyse.ps1`
- `scripts/quality/test.ps1`
- `scripts/quality/find-php.ps1`
- `docs/testing/test-plan.md`
- `docs/testing/coverage-baseline.md`
- `docs/operations/local-quality-gate.md`

## Work Breakdown

1. Add PHP discovery script for WAMP/local environments.
2. Add Composer scripts:
   - `lint`,
   - `analyse`,
   - `format`,
   - `test`,
   - `test:unit`,
   - `test:feature`,
   - `test:security`,
   - `check`.
3. Add PHPUnit bootstrap.
4. Add DB test fixture strategy:
   - disposable schema,
   - transaction rollback,
   - seed fixture.
5. Add unit tests for helpers/services.
6. Add feature tests for API endpoints.
7. Add migration tests.
8. Add UI static tests:
   - no `href="#"`,
   - main landmark present,
   - required inputs have ARIA,
   - dynamic alerts have role/live where applicable.
9. Add security header tests.
10. Add CI workflow on push and pull request.
11. Publish artifacts:
    - test summary,
    - coverage report if configured,
    - static analysis output.

## Acceptance Criteria

- `.\scripts\quality\check.ps1` runs locally without requiring manual PHP PATH changes.
- `composer check` works in environments where PHP/Composer are on PATH.
- CI runs lint, static analysis, and tests.
- Security-critical services have unit tests.
- Advertised API endpoints have feature tests.
- Migrations have at least one disposable-database validation path.
- UI shell accessibility has automated smoke coverage.

## Validation

Run:

```powershell
.\scripts\quality\check.ps1
composer check
```

Expected outputs:

- PHP lint clean.
- Static analysis clean at configured baseline.
- PHPUnit green.
- No skipped critical tests without documented reason.

## Sub-Agent Use

Use workers by test layer:

- auth/API tests,
- database/migration tests,
- UI/accessibility static tests,
- CI/tooling scripts.

Avoid overlapping edits to `composer.json` and CI config.

## Exit Gate

No later phase can be certified unless this quality gate is green.

