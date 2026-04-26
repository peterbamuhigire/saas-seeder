# Coverage Baseline

Current automated baseline:

- PHPUnit: 53 tests, 247 assertions.
- Security-critical token lifecycle tests cover access tokens, refresh rotation, reuse detection, and revocation.
- Module access tests cover core, disabled, missing, and super-admin paths.
- UI static tests cover placeholder link removal and shell landmark smoke checks.

Line coverage is not yet enforced because Xdebug/PCOV is not required in the local WAMP workflow.
