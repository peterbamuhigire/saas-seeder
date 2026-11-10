# CLAUDE.md

<!-- MODEL-ROUTING-BLOCK v2 2026-11-10 -->
## Model classes and token discipline (Claude Code, Codex and any runner; mandatory)
Name a class here, never a model version. The owner's routing file maps classes to current models (Knowledge Bank, Development, Anthropic Models: `model-routing.json`; handoff AM-01), so a model upgrade needs no edit in this repository.
- **Classes:** **M Mundane** (fastest and cheapest; mechanical work with a clear right answer); **N Normal** (default; executes an approved plan); **X Expert** (strongest standard class; plans, judges, hard debugging, final review); **S Special occasion** (premium class above X; rare; only through the seven-test gate, AM-01 s5, including approved billing). A new model is classified by relative price and vendor positioning (AM-01 s1.1). With no S class available, X is the ceiling.
- **Escalate one class, on a trigger only:** two failed acceptance checks; an undecidable choice; unreviewed money, tenant, security or legal work. Drop back after the hard step.
- **Tokens, both runners:** start-up files at most 8,000 tokens and `AGENTS.md` at most 32 KiB (Codex stops at its byte cap); one task per session, clear or new thread between tasks, compact near 80%; plan to a file, execute in a fresh session (resume a plan from its small `RESUME.md` only); read the router section then one `SKILL.md`, search instead of loading; consult only the engine whose domain matches the task; subagents and tools return summaries, cap tool output (`tool_output_token_limit`), disable unused MCP tools; stable prefix first, no instruction edits mid-session; parallel agents only with a written budget; non-interactive runs for bulk (Batch API only with an approved API budget); effort medium by default, high needs a reason, the maximum needs approval.
- **Class S case for this repository (A-14, AM-02):** none; N maintains the template; keep its instruction file small because children inherit it. Everything else: X plans, N executes, M for mechanical work.
- **Monthly Kaizen, first step:** Anthropic currency check (AM-01 s8) and the token-budget lint; record `Anthropic currency check YYYY-MM-DD: no change|changed`.
<!-- /MODEL-ROUTING-BLOCK -->


This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**IMPORTANT:** When this template is scaffolded into a new project, this CLAUDE.md becomes that project's CLAUDE.md. Update it with project-specific details during scaffolding.

## Project Overview

**SaaS Seeder Template** - A production-ready, multi-tenant SaaS starter template with complete authentication, RBAC, and three-tier panel architecture for building SaaS applications like school management systems, restaurant platforms, or medical portals.

## Development Pipeline

This template is part of a three-system pipeline:

```
┌─────────────────────────┐     ┌──────────────────────┐     ┌─────────────────────────┐
│  1. SDLC-Docs-Engine    │────▶│  2. SaaS Seeder      │────▶│  3. Skills-Driven Dev   │
│  (srs-skills repo)      │     │  (this template)     │     │  (~/.claude/skills/)    │
│                         │     │                      │     │                         │
│  Generates:             │     │  Provides:           │     │  Enforces:              │
│  - PRD / Vision         │     │  - Auth + RBAC       │     │  - 60+ coding standards │
│  - SRS / User Stories   │     │  - Multi-tenancy     │     │  - PHP, MySQL, UI/UX    │
│  - HLD / LLD            │     │  - Three-tier panels │     │  - Security, a11y       │
│  - DB Design / ERDs     │     │  - Session + JWT     │     │  - Architecture patterns│
│  - API Specification    │     │  - API bootstrap     │     │  - Mobile API design    │
│  - Test Strategy        │     │  - Page templates    │     │  - SDLC documentation   │
└─────────────────────────┘     └──────────────────────┘     └─────────────────────────┘
```

### System 1: SDLC-Docs-Engine (Input)

The [SDLC-Docs-Engine](https://github.com/peterbamuhigire/srs-skills) generates IEEE/ISO-compliant project documentation across 10 SDLC phases (63 skills). Its outputs are the **input** to this template.

### System 2: SaaS Seeder Template (This Repo)

This template provides the scaffolding — auth, RBAC, multi-tenancy, session management, panel architecture. It is cloned and customised for each new project.

### System 3: Claude Code Skills (Standards Enforcement)

The 60+ skills in `~/.claude/skills/` are the **authoritative coding standards**. All development MUST follow them. They are not suggestions — they are enforced rules distilled from 20+ authoritative books.

**Core web SaaS skill stack (always loaded):**
- `webapp-gui-design` — Tabler/Bootstrap 5 UI patterns
- `form-ux-design` — Cross-platform form UX
- `practical-ui-design` — Colour, typography, layout system
- `vibe-security-skill` — OWASP Top 10 baseline
- `php-modern-standards` — PHP 8+ OOP, PSR, strict types
- `mysql-best-practices` — Schema design, indexing, multi-tenant isolation
- `dual-auth-rbac` — Session + JWT authentication
- `multi-tenant-saas-architecture` — Tenant isolation, three-panel separation
- `api-error-handling` — Standardised API error responses
- `api-pagination` — Offset pagination for REST APIs

**The web app also serves as the API backend for mobile apps.** The MySQL database designed here IS the database for Android and iOS apps. API endpoints built here serve both web frontend and mobile clients.

## Reference sections (read on demand)

These sections were moved verbatim to `docs/claude-reference.md`. Open the one that applies to the task; do not load the whole file.

- Starting a New Project from Template (`docs/claude-reference.md#starting-a-new-project-from-template`)

## Setup & Installation

```bash
# Install dependencies
composer install

# Setup database (Windows PowerShell)
.\setup-database.ps1

# Fix database collations and create franchises table (if needed)
.\fix-database.ps1

# Start development server
php -S localhost:8000 -t public/
```

### First-Time Setup

1. Run database migration: `.\setup-database.ps1`
2. Fix collations (if errors): `.\fix-database.ps1`
3. Create super admin: Visit `http://localhost:8000/super-user-dev.php`
4. **DELETE `super-user-dev.php`** immediately after creating the first super admin
5. Login: Visit `http://localhost:8000/sign-in.php`

**CRITICAL:** Use `super-user-dev.php` to create admin users, NOT the migration default credentials. The password hashing differs (Argon2ID vs bcrypt).

### `super-user-dev.php` Lifecycle

This file is a **scaffolding-only** tool. Its sole purpose is to create the initial super_admin account when bootstrapping a new project. It is NOT part of the running application.

**Lifecycle:**
1. Run database migration
2. Visit `super-user-dev.php` to create the first super admin
3. **DELETE the file** — it is never needed again

**Safety guards:**
- Blocked when `APP_ENV=production` (returns 403)
- Even so, always delete it after scaffolding — defence in depth

**After deletion**, all user management goes through:
- `UserService` (programmatic user creation)
- Admin panel user management pages (future, per-project)

## Three-Tier Panel Architecture

**This is the MOST IMPORTANT architectural concept.** The system has three distinct tiers:

### 1. Super Admin Panel (`/public/adminpanel/`)
- **Purpose:** System-wide management of all franchises
- **User Type:** `super_admin`
- **Use Case:** SaaS operator managing multiple schools/restaurants/clinics
- **Access:** Only users with `user_type = 'super_admin'`

### 2. Franchise Admin Panel (`/public/` root)
- **Purpose:** Franchise-specific management (THE MAIN WORKSPACE)
- **User Type:** `owner`, `staff`
- **Use Case:** School principal managing their school, restaurant manager managing their location
- **Files:** `dashboard.php`, `skeleton.php` (template), and custom franchise management pages
- **Access:** Franchise owners and staff with appropriate permissions

### 3. End User Panel (`/public/memberpanel/`)
- **Purpose:** Self-service portal for end users
- **User Type:** `member`, `student`, `customer`, `patient` (customizable)
- **Use Case:** Students viewing grades, customers viewing orders
- **Access:** End users can only see their own data

### Routing Logic

```php
// index.php shows landing page with navigation options based on user type
// Super admins: buttons to adminpanel OR dashboard
// Franchise admins (owner/staff): button to dashboard
// End users: button to memberpanel
```

**Key Principle:**
- `/public/` root = Franchise admin workspace (NOT a redirect router anymore)
- `/memberpanel/` = End user portal (students, customers)
- `/adminpanel/` = Super admin system (multi-franchise management)

## Session Management (PREFIX SYSTEM)

**CRITICAL:** All session variables use a prefix system (`saas_app_` by default).

```php
// ALWAYS use helper functions, NEVER raw $_SESSION
setSession('user_id', 123);          // Sets $_SESSION['saas_app_user_id']
$userId = getSession('user_id');     // Gets $_SESSION['saas_app_user_id']
hasSession('user_id');               // Checks if exists
```

**To customize for your SaaS:**
1. Change `SESSION_PREFIX` in `src/config/session.php`
2. Example: `'school_'`, `'restaurant_'`, `'clinic_'`

**Session timeout:** 30 minutes (1800 seconds)

## Authentication & Security

### Single Source of Truth

| Concern | Owner | Rule |
|---------|-------|------|
| **User creation** | `UserService` | Only class allowed to INSERT into `tbl_users` — validates, hashes, and inserts in one place |
| Password hashing | `PasswordHelper` | Only class allowed to hash/verify — never raw `password_hash()`/`password_verify()` |
| Session writes after login | `AuthService::authenticate()` | `sign-in.php` only calls authenticate(), never writes sessions itself |
| API authentication | `AuthService::authenticate()` | API login uses the same AuthService as web sign-in — no separate auth path |
| Session reads/writes | `setSession()` / `getSession()` | Never use raw `$_SESSION[...]` |
| CSRF tokens | `CSRFHelper` | Uses session helpers internally; token clears on logout |
| Auth result success | `AuthResult::getStatus() === 'SUCCESS'` | Always uppercase `'SUCCESS'` |
| `isSuperAdmin()` check | `getSession('user_type')` | Never `$_SESSION['user_type']` directly |
| JWT token issuance | `TokenService` | Secret key MUST be in `.env` — never auto-generated at runtime |
| Cookie encryption | `CookieHelper` | Encryption key MUST be in `.env` — never auto-generated at runtime |

### Password Hashing
Uses **Argon2ID** with salt + pepper (NOT bcrypt):
```php
// Hash: salt(32 chars) + Argon2ID(HMAC-SHA256(password, pepper) + salt)
$passwordHelper = new PasswordHelper();
$hash = $passwordHelper->hashPassword($password);
$valid = $passwordHelper->verifyPassword($password, $storedHash);
```

**Environment Variables Required:**
- `PASSWORD_PEPPER` - Global secret for password hashing (64+ chars recommended)
- `COOKIE_ENCRYPTION_KEY` - AES-256-CBC key for encrypted cookies
- `JWT_SECRET_KEY` - JWT signing key (auto-generated if missing)

### Auth Functions (in `src/config/auth.php`)

```php
requireAuth();                              // Redirect to login if not authenticated
requireGuest();                             // Redirect to index if already logged in
isLoggedIn();                               // Check login status
requirePermissionGlobal('PERMISSION_CODE'); // Enforce RBAC permission
hasPermissionGlobal('PERMISSION_CODE');     // Check RBAC permission (boolean)
```

### Access Control Enforcement

Automatic enforcement in `src/config/auth.php` (lines 159-195):
- Non-admins accessing `/adminpanel/` → Redirected to `/memberpanel/`
- Super admins CAN access all three tiers
- Franchise admins (owner/staff) CAN access `/public/` root AND `/memberpanel/`

## Database Structure

### Key Tables
- `tbl_users` - User accounts (franchise_id, user_type, password_hash)
- `tbl_franchises` - Franchise/tenant information
- `tbl_permissions` - Global permission definitions
- `tbl_global_roles` - Role definitions
- `tbl_user_roles` - User-role assignments
- `tbl_user_permissions` - User-level permission overrides
- `tbl_user_sessions` - Active JWT sessions

### Multi-Tenancy

**All queries MUST filter by `franchise_id`** (except super_admin operations):

```php
// CORRECT
$stmt = $db->prepare("SELECT * FROM students WHERE franchise_id = ?");
$stmt->execute([getSession('franchise_id')]);

// WRONG - will leak data between franchises!
$stmt = $db->prepare("SELECT * FROM students");
```

### Stored Procedures

The system uses stored procedures with SQL fallbacks:
- `sp_authenticate_user` - User authentication
- `sp_get_user_data` - Fetch user profile
- `sp_get_user_permissions` - Permission retrieval
- `sp_create_user_session` - JWT session creation

**If stored procedure fails, PHP fallback code executes automatically.**

## Creating New Pages

### For Franchise Admin Pages (in `/public/` root):

```php
<?php
require_once __DIR__ . '/../src/config/auth.php';

// Require authentication
requireAuth();

// Optional: Check specific permission
requirePermissionGlobal('PERMISSION_CODE');

// Set page configuration
$pageTitle = 'Page Title';
$panel = 'admin'; // Use admin menu for franchise pages

// Get franchise context
$franchiseId = getSession('franchise_id');
$userType = getSession('user_type');
?>
<!doctype html>
<html lang="en">
<head>
   <?php include __DIR__ . "/includes/head.php"; ?>
</head>
<body>
    <?php include __DIR__ . "/includes/topbar.php"; ?>
    <!-- Your content -->
    <?php include __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
```

Use `skeleton.php` as a template.

### For Member Panel Pages (in `/public/memberpanel/`):

```php
<?php
require_once __DIR__ . '/../../src/config/auth.php';
requireAuth();

$pageTitle = 'My Page';
$panel = 'member';

// CRITICAL: Filter by user_id
$userId = getSession('user_id');
$franchiseId = getSession('franchise_id');
?>
```

## File Paths

**CRITICAL:** Always use `__DIR__` for includes:

```php
// From /public/
require_once __DIR__ . '/../src/config/auth.php';
include __DIR__ . "/includes/head.php";

// From /public/adminpanel/
require_once __DIR__ . '/../../src/config/auth.php';
include __DIR__ . '/../includes/head.php';

// From /public/memberpanel/
require_once __DIR__ . '/../../src/config/auth.php';
include __DIR__ . '/../includes/head.php';
```

## RBAC (Role-Based Access Control)

### Permission System

```php
// Check permission (returns boolean)
if (hasPermissionGlobal('INVOICE_CREATE')) {
    // Show button
}

// Require permission (throws exception/redirects if denied)
requirePermissionGlobal('INVOICE_DELETE');
```

**Permission Hierarchy:**
1. Super admins bypass all permission checks (always allowed)
2. User-level permission overrides (franchise-scoped)
3. Global role permissions with franchise overrides

### Permission Versioning

`tbl_franchises.permission_version` tracks permission changes:
- Increment when changing franchise permissions
- Invalidates cached permissions in active JWT tokens
- Forces permission re-check on next request

## Common Pitfalls

### 1. Session Cookie Issues on localhost
Session cookies require HTTPS by default. The code handles this:
```php
// Automatically disabled on HTTP (localhost)
ini_set('session.cookie_secure', $isHttps ? '1' : '0');
```

### 2. Password Hashing Mismatch
**NEVER use `password_hash()` directly!** Always use `PasswordHelper`:
```php
// CORRECT
$passwordHelper = new PasswordHelper();
$hash = $passwordHelper->hashPassword($password);

// WRONG - won't match login
$hash = password_hash($password, PASSWORD_BCRYPT);
```

### 3. Franchise Data Leakage
**Always filter by franchise_id:**
```php
$franchiseId = getSession('franchise_id');
// Use in ALL queries for multi-tenant data
```

### 4. Panel Structure Confusion
- **`/public/` root** = Franchise admin pages (NOT just index redirect)
- **`/memberpanel/`** = End user portal
- **`/adminpanel/`** = Super admin only

### 5. Direct User INSERT (Bypass UserService)
**NEVER write raw INSERT/UPDATE on `tbl_users`!** Always use `UserService`:
```php
// CORRECT — centralized validation, hashing, and insertion
$userService = new UserService($db);
$newUser = $userService->createUser([
    'username'     => 'john',
    'email'        => 'john@example.com',
    'password'     => 'SecurePass1!',
    'first_name'   => 'John',
    'last_name'    => 'Doe',
    'user_type'    => 'staff',
    'franchise_id' => 1,
]);

// WRONG — bypasses validation, may use wrong hashing
$db->prepare("INSERT INTO tbl_users ...")->execute([...]);
```

## Environment Configuration

Required `.env` variables:
```env
DB_HOST=localhost
DB_NAME=saas_seeder
DB_USER=root
DB_PASSWORD=

COOKIE_DOMAIN=localhost
COOKIE_ENCRYPTION_KEY=your-32-char-key
PASSWORD_PEPPER=your-64-char-pepper

APP_ENV=development
JWT_SECRET_KEY=your-64-char-key
```

**CRITICAL:** `JWT_SECRET_KEY` and `COOKIE_ENCRYPTION_KEY` are now **required** — the
application will throw a `RuntimeException` if they are missing. Generate them with:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

## Documentation

- `README.md` - Setup and quick start
- `docs/PANEL-STRUCTURE.md` - Complete three-tier architecture guide with examples
- `docs/guides/AUTHENTICATION-GUIDE.md` - Auth system details
- `docs/api/API-DOCUMENTATION.md` - API endpoints
- `docs/reference/QUICK-REFERENCE.md` - Quick reference cheat sheet

## Development Workflow

**All development MUST follow the skills in `~/.claude/skills/`.** These are the authoritative coding standards — not suggestions.

1. **Plan features:** Use `feature-planning` skill to create specs and implementation plans in `docs/plans/`
2. **Create database changes:** Follow `mysql-best-practices` skill. Add migrations to `database/migrations-production/` (idempotent, non-destructive)
3. **Create franchise admin pages:** In `/public/` root, clone `skeleton.php`. Follow `webapp-gui-design` + `practical-ui-design` skills
4. **Create end user pages:** In `/public/memberpanel/`. Same UI skills apply
5. **Build API endpoints:** In `/api/v1/`. Follow `api-error-handling` + `api-pagination` skills. These endpoints serve both web and mobile clients
6. **Add permissions:** Define in `tbl_permissions`, check with `requirePermissionGlobal()`. Follow `dual-auth-rbac` skill
7. **Security:** Apply `vibe-security-skill` to every endpoint and page
8. **Test with different user types:** super_admin, owner, staff, member

### Mobile API Design

The web app IS the backend for mobile apps. When building features:
- Every user-facing action must have a corresponding API endpoint
- Use JWT authentication for mobile (session for web) — both paths go through `AuthService`
- Follow `api-pagination` skill for list endpoints
- Follow `api-error-handling` skill for consistent error responses
- Mobile apps (Android/iOS) are built separately using their own skills but share this database and API

## Security Checklist Before Production

- [ ] **DELETE `super-user-dev.php`** (scaffolding tool — must not exist in production)
- [ ] Set `PASSWORD_PEPPER` to random 64+ character string
- [ ] Set `COOKIE_ENCRYPTION_KEY` to random 32+ character string (required — app will crash without it)
- [ ] Set `JWT_SECRET_KEY` to random 64+ character string (required — app will crash without it)
- [ ] Change `APP_ENV` to `production`
- [ ] Enable HTTPS (session cookies require it in production)
- [ ] Change `SESSION_PREFIX` from `saas_app_` to your app-specific prefix
- [ ] Review all queries for proper franchise_id filtering
- [ ] Verify all user creation goes through `UserService` (no raw INSERT on `tbl_users`)

## Customizing for Your SaaS

**IMPORTANT:** For new projects, follow the "Starting a New Project from Template" workflow at the top of this guide. Use SDLC-Docs-Engine outputs or provide requirements manually in `docs/project-requirements/` and schema in `database/schema/`.

### Scaffolding Steps (After Requirements Are In Place)

1. **Change branding:** Update "SaaS Seeder" to your app name throughout (topbar `$appName`, page titles, footer)
2. **Change session prefix:** `SESSION_PREFIX` in `src/config/session.php` (e.g., `'school_'`, `'restaurant_'`)
3. **Add custom user types:** Modify `tbl_users.user_type` enum based on your domain
4. **Add franchise fields:** Extend `tbl_franchises` table with domain-specific fields
5. **Apply custom schema:** Load project schema from `database/schema/core-schema.sql`
6. **Create your domain models:** Add to `src/` with PSR-4 autoloading, `declare(strict_types=1)`, `final class`
7. **Update this CLAUDE.md:** This file becomes the project's CLAUDE.md — add project-specific user types, business rules, custom tables, and development priorities
8. **Update README.md:** Change title, description, setup steps to match the project
9. **Delete scaffolding tools:** Remove `super-user-dev.php` after creating the first admin
10. **Configure `.env`:** Set all required secrets, `APP_URL`, `CORS_ALLOWED_ORIGINS`, `SESSION_PREFIX`

## Design standard (binding)

- **Standard:** Chwezi Product Excellence Standard. Adopted by Lead Developer Peter Bamuhigire and the Chwezi Core Systems design team, effective 01 November 2026 until further communication. It applies to all UI work in this repository (every screen, flow, component and release) and supersedes conflicting design guidance here from 01 November 2026.
- **Read first** (resolve engine locations through the global engine-routing table; do not hard-code paths):
  - design engine `chwezi-design-engine`: `doctrine/product-excellence-standard.md` (binding text) and the skill `chwezi-product-excellence-standard` (`skills/00-cross-cutting-ops-qa-a11y/`: rules catalogue, product profiles, screen blueprints, scorecard); visual tokens from `chwezi-business-app-house-style`.
  - dev engine `chwezi-dev-engine`: `frontend-architecture/references/product-excellence-engineering-gates.md`.
- **Product profile:** **Template**: no fixed profile. Every product scaffolded from it must declare its profile (Maduuka, Medic8, Farm Acre, Aqar, SpannaMax, Education/LMS or general business) in its own CLAUDE.md.
- **Most relevant screen blueprints:** S01 role-aware business dashboard; S10 approvals and expense requests; S11 document and contract management.
- **Release gate:** 13 hard gates (PX-GATE-01 to PX-GATE-13). PASS needs every applicable gate clear with MEASURED evidence, a weighted score >= 85/100 and no dimension below 2. An applicable gate left NOT_ASSESSED counts as open (BLOCKED); 70-84 with owned, dated remediation is CONDITIONAL (pilot only).
- **Evidence required:** screenshots at compact and expanded widths, light and dark, 200% text, Arabic RTL and the longest declared language; a screen-reader and keyboard accessibility walkthrough; Core Web Vitals (LCP, INP, CLS) on a throttled mobile profile, lab data labelled as lab. An independent verifier signs every release; a human domain reviewer signs financial or clinical changes.
- **Money:** stored as integer minor units (or exact decimal strings), never floats; displayed decimals follow the currency's ISO 4217 minor unit, so UGX and RWF show 0 decimals (`USh 45,000`, never `USh 45,000.00`).
- **Document numbers (hard gate PX-GATE-13, rule PX-DATA-013):** contiguous per tenant per document type, allocated at issue inside the save transaction, sent as strings, provisional while offline, voided never deleted.
- **Existing guidance in conflict:** `docs/design-system/` (Tabler primitives) is superseded where it conflicts. This section propagates into scaffolded projects.
