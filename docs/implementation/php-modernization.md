# PHP Modernization

The project now targets PHP 8.3 through Composer. DTOs should be `final readonly` value objects with constructor promotion and backwards-compatible getters where callers already depend on them.

Quality tooling:

- PHPStan starts at level 1 to support the current legacy surface.
- PHP-CS-Fixer uses PSR-12 in dry-run mode for `composer format`.
- Local PowerShell scripts discover WAMP PHP without requiring PATH changes.
