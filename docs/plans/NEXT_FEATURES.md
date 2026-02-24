# Next Features

Prioritized roadmap for SaaS Seeder Template development.

---

## Recently Completed

### Auth Single Source of Truth (2026-02-25)
Fixed 10 auth violations: AuthResult status normalization, CSRFHelper prefix enforcement, logout token fix, duplicate session writes in sign-in.php, API login/register using PasswordHelper, access-denied.php prefix fix, PermissionService super_admin check fix.

Redesigned sign-in.php, super-user-dev.php, change-password.php (new), and forgot-password.php with split-panel UI.

---

## High Priority

### Email-Based Password Reset
`forgot-password.php` shows a placeholder notice. Needs:
- Token generation stored in DB
- Email sending via PHPMailer or similar
- `reset-password.php` token validation page

### User Management CRUD (Admin Panel)
`/public/adminpanel/` quick actions are placeholder `#` links. Needs:
- Franchise listing and management
- System user listing with role assignment
- System settings page

### Franchise Admin Dashboard
`/public/dashboard.php` needs real content:
- Stats fetched from DB (users, activity, etc.)
- Charts/graphs using Chart.js or Tabler charts

---

## Medium Priority

### Member Panel Content
`/public/memberpanel/` is mostly empty. Needs:
- Profile page (view/edit own data)
- Activity history
- Domain-specific content (varies by SaaS type)

### API Rate Limiting
`docs/api/API-DOCUMENTATION.md` notes this as planned. Add rate limiting middleware to `api/bootstrap.php`.

### Audit Trail
Log all create/update/delete operations to an `tbl_audit_log` table with user_id, franchise_id, action, table_name, record_id, old_values, new_values.

---

## Low Priority

### OpenAPI / Swagger Spec
Generate from existing API endpoints. Useful for mobile app development.

### Two-Factor Authentication (2FA)
TOTP-based 2FA using Google Authenticator or similar.

### Password Reset via SMS
Alternative to email-based reset for markets without reliable email.
