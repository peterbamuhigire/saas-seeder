# Repository 95 Baseline Audit

Date: 2026-07-16  
Auditor: Codex, applying `design-system-skills`, `website-skills`, and `skills-web-dev`  
Verdict: 55/100; focused remediation required

## Executive Finding

The backend contains real security and architecture work, but the repository is not production-ready as currently claimed. The highest-risk gap is evidence integrity: the release documents award 99.99/100 while the documented quality command skips PHPStan, MySQL is not exercised, the API cannot be reached through the documented development server, and browser checks expose broken assets and unfinished critical flows.

## Reproduced Evidence

| Check | Result |
|---|---|
| PHPUnit | 57 tests, 267 assertions passed |
| Xdebug coverage | 6.25% classes, 21.37% methods, 26.05% lines |
| PHP lint | Passed |
| PHPStan through PowerShell gate | Not installed; script printed a message and exited 0 |
| Composer dependency audit | No known advisories in the lock file |
| MySQL service | WAMP MySQL and MariaDB services stopped; live schema check not run at baseline |
| Tracked repository | 3,329 files, about 124.14 MiB |
| Tracked public assets | 2,974 files, 122.43 MiB |
| Documented API URL | `/api/v1/auth/login.php` returned 404 under the documented `-t public` server |
| Brand asset | `/assets/images/branding/logo-light.png` returned 404; repository file is `logo-light.png.png` |
| Security contact | `/.well-known/security.txt` returned 404 |

## Blocking Findings

| Priority | Domain | Evidence | Impact | Owning remediation skill |
|---|---|---|---|---|
| P0 | Security/privacy | `public/session-test.php` prints the session ID, all prefixed values, raw `$_SESSION`, and server fields without an environment guard | A mistakenly deployed diagnostic exposes authentication and tenant context | `web-app-security-audit` |
| P0 | Release integrity | `scripts/quality/analyse.ps1` exits 0 when PHPStan is absent | CI/local checks can report success without static analysis | `world-class-engineering` |
| P0 | Runtime contract | `scripts/server/start-server.ps1` serves only `public/` while advertising `/api/v1/` outside that document root | API onboarding fails immediately | `system-architecture-design` |
| P0 | Design slop | Auth, setup, and denial pages import Inter from `rsms.me` | Fails the design doctrine's banned-font gate | `font-selection-and-pairing` |
| P0 | Accessibility | Sign-in password toggle has an 18px icon, zero padding, and no larger target box | Fails WCAG 2.2 SC 2.5.8 | `accessibility-wcag-2-2-compliance` |
| P0 | Product truth | Recovery and registration are presented as critical routes but do not complete those jobs | Operators can ship a starter with dead recovery/onboarding paths | `webapp-gui-design` plus domain implementation |
| P0 | Documentation | `docs/plans/april-world-class/final-scorecard.md` awards 99.99 in every dimension despite explicit unrun checks | Reviewers cannot trust release evidence | `world-class-engineering` evidence pack |

## High Findings

| Domain | Evidence | Required correction |
|---|---|---|
| Authentication | Web login distinguishes nonexistent users from invalid passwords | Return one generic credential failure while retaining detailed audit events |
| CSP | Sensitive pages send only `Content-Security-Policy-Report-Only` and allow unsafe inline scripts/styles | Move scripts/styles to owned assets and enforce the strongest compatible policy |
| UI semantics | Auth pages use `h2` as the page heading; small floating labels use low-contrast grey | Use a single `h1`, persistent visible labels, compliant contrast, and associated errors |
| Asset integrity | Logo URL is broken on every auth screen | Rename or reference the real asset and add an automated asset test |
| Developer tooling | Server/install scripts pin `C:\wamp64\bin\php\php8.3.28\php.exe`; schema script pins MySQL 8.0.31 while this machine has 8.4.7 | Discover compatible runtimes and fail with precise setup guidance |
| Licence | README says MIT; `composer.json` and `LICENSE` say GPL-3.0-or-later | Preserve the existing GPL licence and correct the README |
| Testing | Only 26.05% of source lines execute under the suite; multiple policies and UI primitives are untouched | Add risk-focused tests and publish current coverage honestly |

## What Must Be Preserved

- Argon2id password hashing and pepper support.
- CSRF validation and secure session flags.
- Opaque rotating refresh tokens, hashed token storage, reuse detection, and family revocation.
- Prepared PDO statements across observed data paths.
- Request IDs, structured logging primitives, audit events, module gates, and tenant-aware permission checks.
- The modular-monolith boundary and numbered migration approach.
- Existing mobile reflow on the sign-in page; no horizontal overflow was observed at 390px.

## Score Rationale

The ten-dimension worksheet and target are defined in `docs/plans/repo-95/spec.md`. The score is not the mean of the old plan's self-ratings. It is the sum of reproduced repository evidence, with the pre-remediation result hard-capped at 65 as requested and the design surface further capped by its failing typography and WCAG gates.

