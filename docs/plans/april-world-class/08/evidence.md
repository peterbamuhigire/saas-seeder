# Phase 08 Evidence

Implemented:

- Composer now targets PHP `^8.3` and declares `GPL-3.0-or-later`.
- Added Composer scripts for lint, analyse, format, test, and check.
- Added PHPStan and PHP-CS-Fixer config.
- Converted `AuthResult` and `LoginDTO` to `final readonly` DTOs with backwards-compatible getters.
- Added environment/config helpers and a shared database connection factory.
- Replaced direct `new Database()` usage in `src`, `api`, and `public`.
- Split AuthService responsibilities into `LoginAuthenticator`, `UserContextService`, and `UserSessionService`.
- Added modernization and service boundary docs.
- Renamed `src/config/database.php` to `src/config/Database.php` for PSR-4 compatibility.

Validation:

- `composer validate --strict` passed.
- `composer check` passed.
- `rg -n "new Database\\(" src api public` returned no matches.
- `rg -n "readonly class|final readonly" src\Auth\DTO` shows both auth DTOs.
