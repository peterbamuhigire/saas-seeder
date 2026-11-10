# Reference sections moved out of CLAUDE.md

Moved verbatim on 2026-11-10 to keep the always-loaded file small (token economy; Codex reads at most 32 KiB of AGENTS.md). Open the section you need.

## Starting a New Project from Template

### End-to-End Workflow

```
Step 1: Generate documentation with SDLC-Docs-Engine
Step 2: Place documents in this template's intake directories
Step 3: Run scaffolding (database, branding, user types)
Step 4: Develop features following Skills standards
Step 5: Build mobile APIs alongside web features
```

### Step 1: SDLC-Docs-Engine → Template Intake

| Engine Phase | Engine Output | Template Intake Location |
|-------------|---------------|------------------------|
| Phase 01: Strategic Vision | PRD, Vision Statement | `docs/project-requirements/requirements.md` |
| Phase 02: Requirements | SRS §3.2 (functional) OR User Stories | `docs/project-requirements/requirements.md` |
| Phase 02: Requirements | Business rules, validation logic | `docs/project-requirements/business-rules.md` |
| Phase 02: Requirements | Stakeholder analysis, user roles | `docs/project-requirements/user-types.md` |
| Phase 02: Requirements | Use cases OR story maps | `docs/project-requirements/workflows.md` |
| Phase 03: Design | Database Design (ERDs, schema) | `database/schema/core-schema.sql` |
| Phase 03: Design | API Specification (OpenAPI 3.0) | `docs/api/` (reference during development) |
| Phase 03: Design | HLD / LLD | Architecture decisions for this CLAUDE.md |
| Phase 05: Testing | Test Strategy, Test Plans | Testing approach for the project |

**If SDLC-Docs-Engine outputs are not available**, developers can manually create the intake files following the formats below.

### Step 2: Place Project Requirements in `docs/project-requirements/`

#### 1. Project Requirements in `docs/project-requirements/`

Create detailed requirements and design specifications:

```
docs/project-requirements/
├── requirements.md       # Feature requirements & specifications
├── business-rules.md     # Business logic & validation rules
├── user-types.md         # User roles, types, and permissions
├── workflows.md          # Key user workflows and processes
└── ui-mockups/           # UI designs or wireframes (optional)
```

**What to include in each file:**

- **requirements.md**: Feature list, acceptance criteria, priorities
- **business-rules.md**: Validation rules, calculations, state machines
- **user-types.md**: Custom user types beyond template defaults, their permissions
- **workflows.md**: Step-by-step user journeys (e.g., "student enrollment process")

#### 2. Database Schema in `database/schema/`

Provide project-specific database schemas:

```
database/schema/
├── core-schema.sql       # Main database schema
├── seed-data.sql         # Sample/seed data (optional)
└── schema-diagram.png    # Database diagram (optional)
```

**Schema Requirements:**
- All franchise-scoped tables MUST have `franchise_id BIGINT UNSIGNED NOT NULL`
- Use `utf8mb4_unicode_ci` collation for text columns
- Include proper foreign keys and indexes
- Index `franchise_id` on all franchise-scoped tables

### AI Agent Scaffolding Process

When starting a new project, Claude should follow this exact sequence. After scaffolding, this is no longer a template — it is a real SaaS project under development.

#### Step 1: Read SDLC Documents

```
Read and understand all project documentation:
- docs/project-requirements/requirements.md  → Features, acceptance criteria
- docs/project-requirements/business-rules.md → Validation, calculations, state machines
- docs/project-requirements/user-types.md     → Roles, permissions, access levels
- docs/project-requirements/workflows.md      → User journeys, process flows
- database/schema/core-schema.sql             → Project-specific tables (if provided)
```

From these documents, identify:
- Custom user types needed (beyond super_admin/owner/staff)
- Project-specific database tables and relationships
- Permissions required for each module
- Business rules that affect schema design

#### Step 2: Create Database & Run Platform Migration

```powershell
# Option A: Use the scaffolding script (interactive)
.\scripts\setup\scaffold-project.ps1

# Option B: Manual steps
mysql -u root -e "CREATE DATABASE IF NOT EXISTS project_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root project_db < docs/seeder-template/migration.sql
```

This creates 16 platform tables:

| Table | Purpose |
|-------|---------|
| `tbl_franchises` | Tenant/organization records |
| `tbl_users` | User accounts (Argon2ID hashing) |
| `tbl_permissions` | Permission definitions |
| `tbl_global_roles` | Role definitions |
| `tbl_global_role_permissions` | Role-permission mapping |
| `tbl_franchise_role_overrides` | Per-tenant role overrides |
| `tbl_user_roles` | User-role assignments |
| `tbl_user_permissions` | User-level permission overrides |
| `tbl_user_sessions` | JWT session tracking |
| `tbl_login_attempts` | Brute-force protection |
| `tbl_audit_log` | Immutable audit trail |
| `tbl_system_settings` | Global system config (key-value) |
| `tbl_franchise_settings` | Per-tenant config (key-value) |
| `tbl_notifications` | In-app notifications |
| `tbl_file_uploads` | Uploaded file metadata |
| `tbl_api_signup_requests` | Franchise onboarding queue |

#### Step 3: Create Project-Specific Schema

Based on the SRS/requirements documents, create `database/schema/core-schema.sql` with project-specific tables. **Every tenant-scoped table MUST:**

```sql
-- Follow this pattern:
CREATE TABLE IF NOT EXISTS `tbl_example` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED NOT NULL,    -- REQUIRED for tenant isolation
  -- ... project columns ...
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_franchise` (`franchise_id`),        -- REQUIRED index
  CONSTRAINT `fk_example_franchise` FOREIGN KEY (`franchise_id`)
    REFERENCES `tbl_franchises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
```

Then apply: `mysql -u root project_db < database/schema/core-schema.sql`

#### Step 4: Customize User Types & Permissions

```sql
-- Based on user-types.md, extend the user_type enum:
ALTER TABLE tbl_users MODIFY user_type ENUM(
  'super_admin', 'owner', 'staff',
  'teacher', 'student'           -- School SaaS example
) NOT NULL DEFAULT 'staff';

-- Insert project-specific permissions:
INSERT INTO tbl_permissions (name, code, module, description) VALUES
('View Students', 'VIEW_STUDENTS', 'STUDENTS', 'View student list'),
('Enroll Student', 'ENROLL_STUDENT', 'STUDENTS', 'Enroll new students');
-- ... etc
```

#### Step 5: Configure & Brand

1. **Session prefix:** Update `SESSION_PREFIX` in `src/config/session.php`
2. **App name:** Update `$appName` in `public/includes/topbar.php`
3. **Logo:** Place `logo-light.png` in `public/assets/images/branding/`
4. **Login backgrounds:** Drop images in `public/assets/images/login-backgrounds/`
5. **System settings:** `UPDATE tbl_system_settings SET setting_value = 'My App' WHERE setting_key = 'app_name';`
6. **`.env`:** Configure database, secrets, CORS origins, APP_URL

#### Step 6: Create Super Admin & First Franchise

1. Visit `super-user-dev.php` to create the super admin account
2. **DELETE `super-user-dev.php`** immediately after
3. Create the first franchise via admin panel or SQL

#### Step 7: Update CLAUDE.md → Project CLAUDE.md

**This CLAUDE.md becomes the project's CLAUDE.md.** Update it with:
- Project name and description
- Custom user types and their permissions
- Key business rules from requirements
- Project-specific database tables
- Development priorities (feature roadmap)
- Session prefix value

Remove or archive the "Starting a New Project from Template" section — it's no longer needed.

#### Scaffolding Checklist

- [ ] SDLC documents read and understood
- [ ] Database created with `utf8mb4_unicode_ci`
- [ ] Platform migration applied (16 tables + SPs)
- [ ] Project-specific schema applied
- [ ] User types customised for the domain
- [ ] Project-specific permissions seeded
- [ ] Session prefix changed from `saas_app_`
- [ ] Branding updated (topbar, logo, backgrounds)
- [ ] `.env` configured with fresh secrets
- [ ] Super admin created via `super-user-dev.php`
- [ ] `super-user-dev.php` deleted
- [ ] First franchise created
- [ ] CLAUDE.md updated with project-specific content
- [ ] README.md updated with project details

### Example Project Structures

**School Management SaaS:**
```
docs/project-requirements/
├── requirements.md       # Student enrollment, grade management
├── business-rules.md     # GPA calculation, attendance rules
├── user-types.md         # student, teacher, parent, principal
└── workflows.md          # Enrollment process, grade submission

database/schema/
├── core-schema.sql       # students, classes, grades, attendance
└── seed-data.sql         # Sample schools, students, classes
```

**Restaurant Management SaaS:**
```
docs/project-requirements/
├── requirements.md       # POS, inventory, orders
├── business-rules.md     # Pricing, discounts, tax calculation
├── user-types.md         # customer, waiter, chef, manager
└── workflows.md          # Order flow, kitchen workflow

database/schema/
├── core-schema.sql       # menu_items, orders, inventory, tables
└── seed-data.sql         # Sample restaurants, menu items
```

