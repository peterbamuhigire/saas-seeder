# Container Map

Phase: April World-Class Phase 02  
Status: Accepted architecture baseline

## Runtime Containers

| Container | Path | Responsibility | Primary callers | Data access |
|---|---|---|---|---|
| Browser UI | `public/*.php`, `public/adminpanel`, `public/memberpanel` | Human-facing PHP pages, Tabler shell, form posts, redirects, session cookie workflows. | Super admin, franchise owner, staff, member/end user. | Indirect through services and `src/config/auth.php`; page-local reads are allowed only for simple display after guards. |
| API v1 | `api/bootstrap.php`, `api/v1/**` | JSON API endpoints, CORS, request parsing, bearer auth, refresh token rotation, error envelope. | API clients and future SPA/mobile clients. | Through service layer and migration-governed tables. |
| Auth/RBAC Services | `src/Auth/**` | Credential verification, token issue/validation, password hashing, cookies, CSRF, permissions, audit. | Browser UI and API v1. | PDO, stored procedures, auth/RBAC tables. |
| Shared Config | `src/config/**` | Database connection, autoloading, session lifecycle, web guard helpers. | Browser UI, services, API bootstrap. | PDO and PHP session storage. |
| Database | MySQL 8.0 | Tenant, users, roles, sessions, audit, settings, signup requests, future module and refresh-token ledgers. | Services and setup scripts. | utf8mb4 tables and stored procedures. |
| Dev/Setup Scripts | `scripts/setup`, `scripts/db`, `scripts/server`, `scripts/utils` | Dependency installation, database setup, local server start, maintenance utilities. | Setup operator and AI coding agent. | MySQL CLI, Composer, filesystem. |
| Static Assets | `public/assets/**`, `public/uploads/**` | Tabler, vendor assets, branding, login backgrounds, uploaded files. | Browser UI. | Filesystem; uploaded metadata belongs in `tbl_file_uploads`. |

## Entry Points

| Entry point | Container | Flow mapping |
|---|---|---|
| `/`, `/index.php` | Browser UI | Web shell route selection. |
| `/sign-in.php` | Browser UI | Web login. |
| `/sign-up.php` | Browser UI | Public signup page. |
| `/forgot-password.php` | Browser UI | Password recovery request. |
| `/change-password.php` | Browser UI | Forced or voluntary password change. |
| `/logout.php` | Browser UI | Web logout. |
| `/dashboard.php` | Browser UI | Franchise dashboard. |
| `/adminpanel/index.php` | Browser UI | Super-admin dashboard. |
| `/memberpanel/index.php` | Browser UI | Member portal. |
| `/access-denied.php` | Browser UI | Access denial view. |
| `/skeleton.php` | Browser UI | Guarded page template. |
| `/session-test.php` | Browser UI | Development session diagnostic; disabled outside local development by policy in Phase 07. |
| `/super-user-dev.php` | Browser UI | Development super-user utility; disabled outside local development by policy in Phase 07. |
| `/api/v1/auth/login.php` | API v1 | API login. |
| `/api/v1/auth/refresh.php` | API v1 | API refresh rotation. |
| `/api/v1/auth/logout.php` | API v1 | API device logout. |
| `/api/v1/auth/logout-all.php` | API v1 | API account logout. |
| `/api/v1/public/auth/register.php` | API v1 | Public franchise signup request. |
| `/assets/**`, `/uploads/**`, `/favicon*`, `/robots.txt`, `/sitemap.xml` | Static Assets | Static asset and metadata delivery. |

## Trust Boundaries

| Boundary | Crossing | Required control |
|---|---|---|
| Internet to Browser UI | HTTP request to PHP page. | Session cookie validation, CSRF on mutating forms, output encoding, security headers. |
| Internet to API v1 | JSON request to endpoint. | CORS policy, JSON parsing limit, bearer-token validation, API error envelope. |
| Browser UI/API to Database | PDO query or stored procedure. | Parameter binding, transaction boundaries, migration-owned schema. |
| Setup operator to Database | Scripted MySQL command. | Explicit script path, repeatable migrations, ledger and rollback evidence. |
| Static uploads to Browser | File path or upload render. | MIME validation, metadata ledger, no executable upload paths. |
