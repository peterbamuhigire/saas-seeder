# Phase 3: PHP Modernization

**Clears:** 6 FAILs, 1 WARN
**Depends on:** Phase 2 (PasswordHelper changes must be done first)
**Files:** All ~20 PHP files in `src/`

---

## Findings Addressed

| ID | Type | Issue |
|----|------|-------|
| A-3.1 | FAIL | `declare(strict_types=1)` missing from ~23 files |
| A-3.2 | FAIL | Missing type hints on params and returns |
| A-3.4 | FAIL | Classes not marked `final` |
| A-3.5 | FAIL | DTOs not `readonly` |
| A-3.6 | FAIL | Constructor promotion not used |
| A-3.15 | FAIL | No code quality tooling |
| A-3.16 | WARN | Cross-platform file naming (`src/config/` lowercase) |

---

## Task 1: Add `declare(strict_types=1)` to all src files

**FILES:** Every `.php` file in `src/` that lacks it (~23 files).

**TASK:** Add `declare(strict_types=1);` as the first statement after `<?php` in every file.

**CONSTRAINTS:**
- Must be the very first statement after `<?php` (before namespace)
- Do NOT add to `public/` page files (they include src files, not the other way around)
- DO add to `api/v1/public/auth/register.php` (already has it — verify)

**VALIDATION:**
- [ ] `grep -rL "strict_types" src/ --include="*.php"` returns empty (no files missing it)
- [ ] `php -l` on every modified file

---

## Task 2: Mark all classes `final`

**FILES:** All class files in `src/Auth/`, `src/Config/`, `src/Helpers/`

**TASK:** Add `final` keyword to every concrete class.

**LIST:**
- `src/Auth/Services/AuthService.php` → `final class AuthService`
- `src/Auth/Services/TokenService.php` → `final class TokenService`
- `src/Auth/Services/PermissionService.php` → `final class PermissionService`
- `src/Auth/PermissionService.php` → `final class PermissionService` (before consolidation in Phase 5)
- `src/Auth/Helpers/PasswordHelper.php` → `final class PasswordHelper`
- `src/Auth/Helpers/CookieHelper.php` → `final class CookieHelper`
- `src/Auth/Helpers/CSRFHelper.php` → `final class CSRFHelper`
- `src/Config/Database.php` → `final class Database`
- `src/Helpers/UiHelper.php` → `final class UiHelper`

**CONSTRAINTS:**
- Do NOT mark interface files or abstract classes as `final`
- `UserService` is already `final` — skip

**VALIDATION:**
- [ ] `grep -rn "^class " src/ --include="*.php"` returns empty (all should be `final class`)

---

## Task 3: Make DTOs `final readonly` with constructor promotion

**FILE:** `src/Auth/DTO/AuthResult.php`

**TASK:** Convert to `final readonly` class with promoted constructor properties.

**CODE:**
```php
<?php
declare(strict_types=1);

namespace App\Auth\DTO;

final readonly class AuthResult
{
    public function __construct(
        private int $userId,
        private ?int $franchiseId,
        private string $username,
        private string $status,
        private array $userData = [],
        private ?string $token = null,
        private ?string $message = null,
    ) {}

    public function isSuccessful(): bool { return $this->status === 'SUCCESS'; }
    public function getUserId(): int { return $this->userId; }
    public function getFranchiseId(): ?int { return $this->franchiseId; }
    public function getUsername(): string { return $this->username; }
    public function getStatus(): string { return $this->status; }
    public function getUserData(): array { return $this->userData; }
    public function getToken(): ?string { return $this->token; }
    public function getMessage(): ?string { return $this->message; }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'franchiseId' => $this->franchiseId,
            'username' => $this->username,
            'status' => $this->status,
            'userData' => $this->userData,
            'token' => $this->token,
            'message' => $this->message,
        ];
    }
}
```

**ALSO:** Same treatment for `src/Auth/DTO/LoginDTO.php`.

**CONSTRAINTS:**
- Requires PHP 8.2+ for `readonly` classes. Update `composer.json` to require `>=8.2`.

**VALIDATION:**
- [ ] `php -l src/Auth/DTO/AuthResult.php`
- [ ] `php -l src/Auth/DTO/LoginDTO.php`
- [ ] Existing callers (`AuthService`, `sign-in.php`, API login) still work

---

## Task 4: Add return types to all functions

**FILES:** `src/config/session.php`, `src/config/auth.php`, `src/Config/Database.php`

**TASK:** Add explicit return types to all functions missing them.

**KEY CHANGES:**
- `setSession($key, $value): void`
- `getSession(string $key, mixed $default = null): mixed`
- `hasSession(string $key): bool`
- `clearPrefixedSession(): void`
- `requireAuth(): void`
- `requireGuest(): void`
- `isLoggedIn(): bool`
- `logout(): void`
- `Database::closeConnection(): void`
- `Database::beginTransaction(): bool`
- `Database::commit(): bool`
- `Database::rollback(): bool`
- `Database::lastInsertId(): string|false`

**VALIDATION:**
- [ ] `php -l` on all modified files

---

## Task 5: Fix cross-platform file naming

**TASK:** Rename `src/config/` directory to `src/Config/` to match the `App\Config` namespace.

**CONSTRAINTS:**
- Git on Windows is case-insensitive. Must use `git mv` in two steps:
  1. `git mv src/config src/config_temp`
  2. `git mv src/config_temp src/Config`
- Update all `require_once` paths that reference `src/config/` to `src/Config/`
- The files inside (`session.php`, `auth.php`, `autoloader.php`) keep their lowercase names (they're not namespaced classes)

**VALIDATION:**
- [ ] `ls src/Config/` shows `session.php`, `auth.php`, `autoloader.php`, `database.php`
- [ ] All `require_once` paths updated
- [ ] Application loads without errors

---

## Task 6: Add code quality tooling

**FILES:** `composer.json`, `phpstan.neon` (new), `.github/workflows/ci.yml` (new, optional)

**TASK:** Add PHPStan at level 5 and configure it for the project.

**CODE:** `phpstan.neon`:
```neon
parameters:
    level: 5
    paths:
        - src
    excludePaths:
        - src/Auth/Middleware  # Non-functional stubs, removed in Phase 5
```

**ALSO:** Add to `composer.json` scripts:
```json
"scripts": {
    "analyse": "phpstan analyse",
    "lint": "php -l src/"
}
```

Add `phpstan/phpstan` to `require-dev`.

**VALIDATION:**
- [ ] `composer run analyse` runs without fatal errors (warnings expected initially)

---

## Status

| Task | Status |
|------|--------|
| 1: strict_types | not-started |
| 2: final classes | not-started |
| 3: readonly DTOs | not-started |
| 4: Return types | not-started |
| 5: File naming | not-started |
| 6: Quality tooling | not-started |
