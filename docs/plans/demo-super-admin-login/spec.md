# Demo Super-Admin Login

## Status

Completed on 2026-07-26.

## Goal

Expose a one-click demo login on the sign-in page when demo access is explicitly
configured, and route that login through the existing authentication flow as a
super administrator.

## Requirements

- The demo button is hidden by default.
- `DEMO_MODE=true`, a non-production `APP_ENV`, and non-empty demo credentials
  are all required before the button is rendered or accepted.
- Demo credentials remain server-side and are never rendered into HTML.
- Demo login submissions use the existing CSRF validation.
- The configured account must exist and have `user_type=super_admin`.
- Successful demo login uses `AuthService` so password verification, lockout,
  session hydration, token generation, and audit behavior remain unchanged.
- Normal username/password login behavior remains unchanged.

## Configuration

```dotenv
DEMO_MODE=true
DEMO_SUPER_ADMIN_USERNAME=demo-admin
DEMO_SUPER_ADMIN_PASSWORD=replace-with-the-demo-account-password
```

Use `APP_ENV=demo`, `development`, `local`, or `staging`. Demo access is always
disabled when `APP_ENV=production`.

## Verification

- Unit tests cover disabled, incomplete, non-production, and production config.
- UI static tests verify the demo form is CSRF-protected and does not render
  credential variables.
- Targeted PHPUnit: 8 tests and 128 assertions pass.
- Direct PHP syntax checks pass for every changed PHP file.
- PHP-CS-Fixer passes for the new in-scope PHP files. The repository-wide dry
  run still reports pre-existing line-ending differences in untouched files.
- PHPStan's existing scan of `public/sign-in.php` reports the pre-existing
  dynamic `authEscape()` helper as undiscovered.
