# Repository 95 Verification Evidence

Date: 2026-07-16  
Environment: PHP 8.3.28, MySQL 8.4.7, Windows WAMP, in-app Chromium browser

## Automated Quality Gate

Command:

```powershell
.\scripts\quality\check.ps1
```

Result:

- PHP lint passed.
- PHPStan level 6 passed with no errors across `src/` and `api/`.
- PHP CS Fixer dry run passed with no changed files.
- PHPUnit passed: 61 tests and 356 assertions.
- The PHPUnit suite map was repaired so Unit, Feature, and Static suites run once; the previous `All` overlap had duplicated tests.

Coverage command:

```powershell
$env:XDEBUG_MODE = 'coverage'
$php = .\scripts\quality\find-php.ps1
& $php .\vendor\bin\phpunit --coverage-text --colors=never
```

Coverage result:

| Measure | Baseline | Final |
|---|---:|---:|
| Classes | 6.25% | 45.68% |
| Methods | 21.37% | 45.38% |
| Lines | 26.05% | 41.97% |

High-risk token services retain strong line coverage: refresh token repository 87.50%, refresh token service 82.86%, and access token service 71.79%. User creation is 94.12%, and the shared UI component library is directly exercised.

## Database and Migration Evidence

Command, run twice:

```powershell
.\scripts\setup\setup-database.ps1
.\scripts\setup\setup-database.ps1
```

Result:

- The configured `saas_seeder` database exists.
- Schema validation reports 22 utf8mb4 tables.
- Five migrations are recorded with SHA-256 checksums.
- Migration 0003 now guards columns and indexes for installations whose consolidated base already contains token-lifecycle fields.
- Migration 0005 replaces plaintext signup verification tokens with a unique hash-only column.
- The second run reported all five migrations as already applied and changed nothing.

## HTTP and API Evidence

The development server uses `public/` as its document root and `scripts/server/router.php` to dispatch only real PHP files below `api/`.

| Route or check | Result |
|---|---|
| `GET /sign-in.php` | 200 with enforced same-origin CSP |
| `POST /api/v1/auth/login` with `{}` | 422 JSON, stable error envelope, request ID, and security headers |
| `POST /api/v1/public/auth/register` with default config | 403 `FEATURE_DISABLED` |
| `GET /.well-known/security.txt` | Published contact, expiry, and preferred language |
| `GET /session-test.php` from loopback development | Configuration-only diagnostics; no session ID, values, raw session array, or server metadata |
| Development/setup route from production or non-loopback | Denied by `EnvironmentGuard` |

The original API failure was reproduced before correction: the route first returned 404 under the documented server, then exposed an incorrect bootstrap path. The final endpoint returns JSON through the real API bootstrap.

## Browser and Accessibility Evidence

Desktop inspection used the default 1536 by 686 viewport. Responsive inspection used an explicit 390 by 844 viewport and reset it after the check.

| Evidence | Result |
|---|---|
| Computed body font | `Hanken Grotesk` |
| Computed heading font | `Bricolage Grotesque` |
| Font loading state | loaded |
| Username input | 374 by 51 CSS pixels |
| Password input | 374 by 51 CSS pixels |
| Password toggle | 64 by 44 CSS pixels |
| Submit button | 374 by 50 CSS pixels |
| Recovery link | 116 by 44 CSS pixels |
| Desktop overflow | none |
| 390px overflow | none; document and client widths both 390px |
| Mobile panel width | 358px |
| Inline scripts on auth, recovery, registration, denial, and bootstrap screens | zero |
| Browser console messages after inspected flows | none |

The semantic snapshot exposes a skip link, one level-one page heading, an associated username field, an associated password field, named password toggle, checkbox, recovery link, and submit button. Recovery and registration pages expose status content and a return action, not inactive forms.

## Dependency and Licence Evidence

Command:

```powershell
composer audit --locked --no-interaction
```

Result: no security vulnerability advisories in the lock file.

The repository licence is GPL-3.0-or-later in `composer.json`, `LICENSE`, and README. Both web fonts ship with their OFL 1.1 text beside the WOFF2 asset. No font is loaded from a third-party origin.

## Documentation Consistency

The project instruction references `skills/update-claude-documentation/SKILL.md`, but no project-local `skills/` directory or matching skill file exists. The fallback documentation check therefore used direct route, licence, command, plan-status, test-count, migration-count, and stale-certification scans. Historical April self-ratings are retained as history but explicitly marked superseded.
