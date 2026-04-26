# SaaS Seeder Template

SaaS Seeder is a production-ready PHP 8.3 authentication and RBAC scaffold for multi-tenant SaaS products. It ships with web auth, API auth flows, module gates, migration governance, a reusable UI shell, and release-quality documentation.

## Core Capabilities

- Session and API authentication with rotating refresh tokens
- Role-based access control with franchise-aware overrides
- Module registry and tenant gates
- Security headers, rate limiting, CORS policy, and audit logging
- Governed MySQL migrations plus schema validation
- PHPUnit and PHPStan quality gates

## Quick Start

Prerequisites:

- PHP 8.3
- MySQL 8
- Composer
- WAMP, LAMP, or equivalent local stack

Install and run:

```powershell
composer install
.\scripts\setup\setup-database.ps1
.\scripts\server\start-server.ps1
.\scripts\quality\check.ps1
```

Create the initial super admin with `public/super-user-dev.php`, then remove or restrict that page outside development.

## Main Paths

- Web login: `public/sign-in.php`
- Franchise dashboard: `public/dashboard.php`
- Super admin panel: `public/adminpanel/`
- Member panel: `public/memberpanel/`
- API auth endpoints: `api/v1/auth/`

## Project Layout

- `src/`: application code
- `api/`: API runtime bootstrap and endpoints
- `public/`: web entry points and assets
- `database/`: migrations, schema, and seeds
- `scripts/`: setup, database, server, quality, and utility scripts
- `docs/`: architecture, API, operations, release, testing, and planning docs

## Documentation Entry Points

- `docs/overview/README.md`
- `docs/plans/april-world-class/README.md`
- `docs/api/API-DOCUMENTATION.md`
- `docs/data/migration-runbook.md`
- `docs/release/release-plan.md`

## Release Checklist

Before shipping:

- remove or restrict `super-user-dev.php`
- set production secrets in `.env`
- run `.\scripts\quality\check.ps1`
- run schema validation against the target MySQL instance
- review `docs/release/release-checklist.md` and `docs/release/rollback-plan.md`

## License

MIT
