# SaaS Seeder Template Guide

This seeder template is a reusable blueprint for starting new web-based SaaS projects with a consistent structure, a production-ready authentication flow, and a proven RBAC model. The goal is speed and consistency: copy the template into a new repository, update environment variables, run one migration script, and log in as the default super user to an empty dashboard with placeholder stats. From there, you can focus on real product features instead of rebuilding the same scaffolding repeatedly.

The template combines a clean UI shell, a standard admin panel, a flexible member panel, and an API boundary designed for multi-tenant SaaS. The admin panel name is fixed (adminpanel). The member panel is intentionally generic so it can be repurposed as a customer portal, patient portal, distributor portal, or any other user-facing experience. Each panel has its own includes folder for easy customization without accidental cross-panel styling or markup drift. Shared assets and uploads live in one place, so product images and common files do not get duplicated.

## Seeder skill usage

Use the saas-seeder skill (also referred to as seeder-script) to bootstrap a new repository from this template. A single prompt like "Using the seeder-script skill, prepare this repository for Academia Pro" should prompt for MySQL credentials, franchise details, and the super user, then create the database, import optional schema dumps, run the auth/RBAC migration, and verify first login readiness.

## What this template gives you

1. A predictable public-facing web root (public/) with adminpanel and memberpanel directories.
2. Per-panel includes folders (head.php, topbar.php, footer.php, foot.php) that you can copy into future panels.
3. An API directory outside public/ (api/), designed to be routed to /api via web server rules.
4. A database schema baseline for users, RBAC, sessions, and login security.
5. Stored procedures that match the login flow used in Maduuka.
6. A default super user (root) for first login.
7. Clear instructions for copying login/logout files from Maduuka and wiring them into the new repo.

## How this accelerates new SaaS projects

Instead of starting from a blank repo, this template gives you a proven foundation. You avoid days of rework and reduce the risk of subtle auth bugs because the login and RBAC patterns are already defined. The UI structure keeps all pages under public/, which is the standard going forward. You can immediately scaffold new pages by cloning the panel includes and building new screens, while the API layer remains outside public for safety and long-term maintainability. By keeping conventions consistent across projects, you can reuse scripts, migrations, and onboarding workflows with confidence.

## Core structure

- public/ is the web root
- public/adminpanel/ is the fixed admin panel
- public/memberpanel/ is the flexible member panel
- public/adminpanel/includes/ and public/memberpanel/includes/ are separate
- public/assets/ holds shared CSS, JS, and images
- public/uploads/ holds shared file uploads
- api/ contains all backend endpoints
- src/ contains services, auth, and config

## Database conventions to maintain

The template follows Maduuka-style database conventions:

- tbl_users is the source of truth for authentication and user identity.
- user_type drives panel routing and super admin permissions.
- super_admin users are allowed to have franchise_id = NULL.
- RBAC is managed by permissions, roles, and user role assignments.
- Permission codes are UPPERCASE_WITH_UNDERSCORES.
- User sessions are stored and tracked in tbl_user_sessions.
- Failed login attempts are tracked in tbl_login_attempts.

The migration script in this folder creates the minimal set of tables and procedures required for the login flow to work. It does not create every business table. It is deliberately small and focused so you can run it as an initial seed and then add project-specific schema later.

## Authentication and RBAC overview

Authentication is built around these tables:

- tbl_users: stores username, password hash, user_type, and status
- tbl_user_sessions: stores tokens and session metadata
- tbl_login_attempts: tracks failed attempts and lockout logic

Authorization is built around these tables:

- tbl_permissions: global permission definitions
- tbl_global_roles: global role definitions
- tbl_global_role_permissions: permissions assigned to global roles
- tbl_user_roles: maps users to global roles
- tbl_user_permissions: user-level permission overrides
- tbl_role_permissions: optional local roles to permissions (included for compatibility)

The login flow uses stored procedures to keep business logic close to the data and consistent across apps. The procedures included in the migration script match what Maduuka expects. This means the AuthService and PermissionService classes can be copied directly without breaking their assumptions.

## Default super user

The migration script inserts a default super user:

- Username: root
- Email: <peter@techguypeter.com>
- Password: password
- user_type: super_admin
- franchise_id: NULL

This user is intentionally minimal and must be changed in any real deployment. After the first login, you should update the password, add proper franchises, and create real roles and permissions for your application.

## Environment variables to set

Before login will work, set these environment variables in your new repository:

- DB_HOST
- DB_PORT
- DB_NAME
- DB_USER
- DB_PASSWORD
- DB_CHARSET
- COOKIE_DOMAIN
- COOKIE_ENCRYPTION_KEY
- APP_ENV

These are used by the database connection and by authentication middleware in the login and logout flows.

## Quick start workflow

1. Copy the template into a new repository.
2. Set your environment variables in .env.
3. Update CLAUDE.md with the mysql.exe and php.exe paths for the dev machine.
4. If database/schema contains SQL dumps, import them before the auth/RBAC migration.
5. Run the migration script in docs/seeder-template/migration.sql against your database.
6. Copy the login/logout files listed in docs/seeder-template/copy-login-files.md from Maduuka into the new repo.
7. Start your PHP server with public/ as the web root.
8. Login as root using the default credentials.
9. You should land on an empty admin dashboard with placeholder stats.

## Why per-panel includes

Each panel has its own includes folder so you can customize the UI independently. Admin workflows and member experiences often diverge quickly, and this separation avoids conflicts. To add a new panel in the future, copy an existing includes folder, update branding and navigation, and link the new panel to its own index page.

## API routing convention

All APIs live outside public/ and should be routed to /api by the web server. This keeps the attack surface small and allows cleaner URL structures. The routing is done at the web server level, not inside PHP pages.

## Placeholder dashboard behavior

The template expects the first login to land on an empty dashboard with placeholder stats. This is intentional: it proves that authentication and routing work without depending on any business data. It also gives you a known landing page to expand as you build features.

## What to customize next

After the first login works, the next steps are always the same:

- Replace branding in the panel includes.
- Update navigation menus to match your module plan and menu rules.
- Add your first business tables and APIs.
- Build the real dashboard widgets.
- Introduce real roles and permissions for your project.

### Menu design rules (apply in all panels)

- Minimalistic, easy on the eye.
- Group items by job role so a user can find their work in one menu.
- Max **5 submenus** per menu.
- Max **6 items** per submenu.
- If more items are needed, add **one** extra submenu level (no deeper).
- Use Bootstrap Icons on **all** menu headings and entries (`bi-*`).
- Prefer fewer pages by grouping related functions on a single page with permissioned sections/tabs/cards.

### Sample module menus (dummy items)

**Finance** `bi-cash-stack`

- Overview `bi-speedometer2`: Summary, KPIs, Cash Position
- Billing `bi-receipt`: Invoices, Credit Notes, Payments
- Accounts `bi-journal-text`: AR, AP, Journals, Chart of Accounts
- Treasury `bi-bank`: Bank Reconciliation, Transfers, Cashbook
- Reports `bi-file-bar-graph`: P&L, Balance Sheet, Cash Flow, Taxes

**HR & Payroll** `bi-people`

- People `bi-person-badge`: Directory, Profiles, Documents
- Attendance `bi-calendar-check`: Clocking, Shifts, Leave
- Payroll `bi-cash-coin`: Pay Runs, Deductions, Benefits, Payslips
- Compliance `bi-clipboard-check`: Taxes, Pension, Contracts

**Stores & Inventory** `bi-box-seam`

- Catalog `bi-boxes`: Items, Categories, Units
- Stock `bi-stack`: On Hand, Adjustments, Transfers
- Purchasing `bi-bag`: Requisitions, Purchase Orders, GRN
- Warehousing `bi-house-gear`: Locations, Bin Cards, Pick/Pack
- Reports `bi-file-bar-graph`: Valuation, Slow Movers, Stock Ledger

**System Settings** `bi-gear`

- Access Control `bi-shield-lock`: Roles, Permissions, Users
- Organization `bi-building`: Company Profile, Branches, Departments
- Integrations `bi-plug`: Email/SMS, Payments, API Keys
- System `bi-sliders`: Preferences, Audit Logs, Backups

## Files in this directory

- migration.sql: one SQL script to create users, RBAC tables, and auth procedures
- copy-login-files.md: exact list of login/logout files to copy from Maduuka

This guide is designed to be handed to any assistant or developer. It describes the template clearly and explains how it produces a working login in minutes, without requiring a full rebuild of auth and RBAC on every project.
