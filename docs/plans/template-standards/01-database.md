# Phase 1: Database Foundation

**Clears:** 8 FAILs, 3 WARNs
**Depends on:** Nothing (runs first)
**Files:** `docs/seeder-template/migration.sql`, `database/migrations-production/001-standards-compliance.sql`

---

## Findings Addressed

| ID | Type | Issue |
|----|------|-------|
| A-4.1 | FAIL | Missing `COLLATE utf8mb4_unicode_ci` on all tables |
| A-4.2 | FAIL | Missing `ROW_FORMAT=DYNAMIC` on all tables |
| A-4.6 | FAIL | Zero foreign key constraints |
| A-4.9 | FAIL | `tbl_user_sessions.franchise_id` signed vs unsigned |
| A-4.10 | FAIL | `tbl_franchises` missing from main migration |
| A-4.11 | FAIL | `tbl_franchise_role_overrides` missing entirely |
| A-4.12 | FAIL | Default seed user has bcrypt hash (incompatible) |
| A-1.11 | FAIL | No audit trail table (partially — table only, logic in Phase 7) |
| A-4.13 | WARN | `sp_get_user_permissions` ignores franchise overrides |
| A-4.14 | WARN | Application DB user not root (documented only) |
| A-5.5 | WARN | `tbl_distributors` referenced but missing (remove reference instead) |

---

## Task 1: Rewrite migration.sql

**FILE:** `docs/seeder-template/migration.sql`

**TASK:** Complete rewrite of the base migration to include all required tables, correct collation, ROW_FORMAT, foreign keys, and remove the incompatible seed user.

**CONSTRAINTS:**
- Every CREATE TABLE must include `COLLATE utf8mb4_unicode_ci` and `ROW_FORMAT=DYNAMIC`
- Every `franchise_id` column must be `BIGINT UNSIGNED`
- All FK relationships must be explicit with `ON DELETE` / `ON UPDATE` actions
- `tbl_franchises` must be created BEFORE `tbl_users` (dependency order)
- Remove the default bcrypt-hashed seed user INSERT
- Add `tbl_franchise_role_overrides` table
- Add `tbl_audit_log` table

**THINK STEP-BY-STEP:**
1. Reorder table creation: `tbl_franchises` first, then `tbl_permissions`, `tbl_global_roles`, `tbl_users`, then junction tables
2. Add `COLLATE utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC` to every CREATE TABLE
3. Fix `tbl_user_sessions.franchise_id` to `BIGINT UNSIGNED DEFAULT NULL`
4. Add all FK constraints with `ON DELETE CASCADE` for junction tables, `ON DELETE RESTRICT` for entity references
5. Add `tbl_franchise_role_overrides` with PKs and FKs
6. Add `tbl_audit_log` (immutable — no UPDATE/DELETE by design)
7. Remove the default user INSERT (super-user-dev.php handles initial user creation)
8. Update stored procedures: `sp_get_user_permissions` should consult `tbl_franchise_role_overrides` and `tbl_user_permissions`

**VALIDATION:**
- [ ] `mysql -u root saas_seeder < migration.sql` executes without errors
- [ ] `SHOW CREATE TABLE tbl_users` shows `utf8mb4_unicode_ci` and `ROW_FORMAT=DYNAMIC`
- [ ] `SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'saas_seeder' AND CONSTRAINT_TYPE = 'FOREIGN KEY'` returns FK rows for all relationships
- [ ] No default user exists after fresh migration
- [ ] `tbl_franchise_role_overrides` and `tbl_audit_log` exist

---

## Task 2: Create production migration

**FILE:** `database/migrations-production/001-standards-compliance.sql`

**TASK:** Non-destructive, idempotent migration for existing deployments. Adds missing tables and corrects collation/FKs without dropping data.

**CONSTRAINTS:**
- Must use `CREATE TABLE IF NOT EXISTS` for new tables
- Must use `ALTER TABLE ... ADD CONSTRAINT IF NOT EXISTS` pattern (or check `information_schema` before adding)
- Must be idempotent — safe to run multiple times
- Must not drop or recreate existing tables

**THINK STEP-BY-STEP:**
1. Create `tbl_franchises` if not exists
2. Create `tbl_franchise_role_overrides` if not exists
3. Create `tbl_audit_log` if not exists
4. ALTER each table to `CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`
5. ALTER `tbl_user_sessions.franchise_id` to `BIGINT UNSIGNED DEFAULT NULL`
6. Add FK constraints (check if they exist first via `information_schema`)

**VALIDATION:**
- [ ] Running twice produces no errors
- [ ] Existing data is preserved
- [ ] `php -l` on any PHP file that references these tables

---

## Task 3: Remove tbl_distributors reference from AuthService

**FILE:** `src/Auth/Services/AuthService.php`

**TASK:** Remove the distributor_code lookup block (lines ~222-234) that references `tbl_distributors`. This is project-specific code that leaked into the template.

**CONSTRAINTS:**
- Keep the `distributor` user type in the ENUM (it's a valid template type)
- Only remove the JOIN query that depends on `tbl_distributors`
- Do not change any other AuthService logic

**VALIDATION:**
- [ ] `php -l src/Auth/Services/AuthService.php`
- [ ] No references to `tbl_distributors` remain in src/

---

## Status

| Task | Status |
|------|--------|
| 1: Rewrite migration.sql | not-started |
| 2: Production migration | not-started |
| 3: Remove distributor reference | not-started |
