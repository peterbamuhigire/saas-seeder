# Phase 08: PHP Modernization And Service Architecture

## Objective

Modernize PHP structure to make the scaffold maintainable, type-safe, testable, and consistent with PHP 8.3 standards.

## Skills Applied

- `php-modern-standards`
- `system-architecture-design`
- `skill-composition-standards`

## Current Problems

- Composer allows PHP `>=8.0` despite the project standard being PHP 8.3.
- DTOs are final but not readonly value objects.
- Some services have broad responsibilities, especially `AuthService`.
- API/bootstrap uses global helpers.
- Direct `new Database()` calls coexist with `Database::getInstance()`.
- No static analysis or formatter config exists.

## Deliverables

Create or update:

- `composer.json`
- `phpstan.neon`
- `.php-cs-fixer.php` or `phpcs.xml`
- `src/Auth/DTO/AuthResult.php`
- `src/Auth/DTO/LoginDTO.php`
- `src/Auth/Services/AuthService.php`
- `src/Auth/Services/UserSessionService.php`
- `src/Auth/Services/UserContextService.php`
- `src/Auth/Services/LoginAuthenticator.php`
- `src/Config/AppConfig.php`
- `src/Config/Env.php`
- `src/Database/ConnectionFactory.php`
- `docs/implementation/php-modernization.md`
- `docs/implementation/service-boundaries.md`

## Work Breakdown

1. Change Composer PHP constraint to `^8.3`.
2. Add a `license` field matching the repo license.
3. Add Composer scripts:
   - `lint`,
   - `analyse`,
   - `format`,
   - `test`,
   - `check`.
4. Convert DTOs to `final readonly` with constructor promotion.
5. Keep backwards-compatible getters if current callers use them.
6. Refactor `AuthService` into smaller collaborators:
   - credential lookup,
   - password verification,
   - user context loading,
   - session hydration,
   - audit logging.
7. Standardize DB connection acquisition.
8. Centralize environment reads.
9. Remove dead or duplicate helpers after tests exist.
10. Add strict types and return types everywhere not already covered.

## Acceptance Criteria

- Composer enforces PHP 8.3.
- Static analysis passes at the chosen baseline.
- Formatting tool passes.
- DTOs are readonly.
- Auth flow behaviour remains unchanged except where previous phases intentionally changed token model.
- No security-critical service silently swallows configuration errors.
- Database connection approach is consistent.

## Validation

Run:

```powershell
composer validate --strict
composer check
rg -n "new Database\\(" src api public
rg -n "readonly class|final readonly" src\Auth\DTO
```

If Composer still depends on PATH, use WAMP PHP wrapper scripts from Phase 10.

## Sub-Agent Use

Use one PHP worker for DTO/config modernization. Use another worker for `AuthService` decomposition only after tests exist. Use a verifier for static analysis output.

## Exit Gate

This phase should not change auth semantics without tests from Phase 10 covering the existing behaviour.

