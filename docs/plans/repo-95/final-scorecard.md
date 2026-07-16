# Repository 95 Final Scorecard

Date: 2026-07-16  
Baseline: 55/100  
Final: 95/100  
Rubric: unchanged from `docs/plans/repo-95/spec.md`

## Outcome

The repository reaches 95/100 on the same ten-dimension rubric used for the baseline. The forty-point gain comes from reproduced runtime, database, browser, static-analysis, dependency, and test evidence. No dimension is credited for an unrun check.

| Dimension | Baseline | Final | Change | Final evidence |
|---|---:|---:|---:|---|
| Product integrity and flow completeness | 5 | 9 | +4 | The API is reachable through the documented server; unavailable registration and recovery capabilities are explicit disabled states rather than fake submissions |
| Visual system and authored design quality | 4 | 10 | +6 | Shared auth shell; Bricolage Grotesque plus Hanken Grotesk; ink, amber, and blue token palette; broken logo and generic gradient treatment removed |
| Accessibility and responsive behavior | 5 | 10 | +5 | One `h1`, skip target, persistent labels, visible focus, reduced-motion handling, 44-51px controls, and no horizontal overflow at 390px |
| Architecture and maintainability | 8 | 9 | +1 | Existing modular-monolith boundary preserved; shared auth assets, environment guard, router, and runtime-discovery helpers remove duplication and machine coupling |
| Security and privacy | 6 | 10 | +4 | Enforced CSP, generic credential failures, guarded diagnostics/bootstrap, hash-only signup verification tokens, 12-character password floor, and no raw refresh exception responses |
| API, data, and tenant discipline | 7 | 9 | +2 | API bootstrap paths corrected; JSON smoke response reproduced; 22-table schema validated with five checksum-ledgered migrations and a proven no-op rerun |
| Tests and enforceable quality gates | 5 | 9 | +4 | PHPStan level 6, mandatory formatter/analyser/test binaries, 61 non-duplicated tests with 356 assertions, and 41.97% line coverage |
| Operations and developer experience | 5 | 10 | +5 | Dynamic PHP, Composer, and MySQL discovery; truthful local router; idempotent setup; CI MySQL service, dependency audit, migration validation, and rerun proof |
| Performance and supply-chain discipline | 6 | 9 | +3 | No external font dependency, two variable WOFF2 files, clean locked dependency audit, runtime asset allow-list through page references, and same-origin CSP |
| Documentation and evidence integrity | 3 | 10 | +7 | GPL licence aligned, active plan corrected, historical 99.99 claims marked superseded, and command/browser/database evidence recorded with residual risks |
| **Total** | **55/100** | **95/100** | **+40** | See `verification.md` |

## Design Engine Verdict

The pre-remediation design surface was capped below 60 by two hard gates: banned Inter usage and undersized controls. Both gates now pass.

- Display face: Bricolage Grotesque, variable WOFF2, OFL 1.1.
- Body and UI face: Hanken Grotesk, variable WOFF2, OFL 1.1.
- Pairing reason: a distinctive product display face over a calm, readable interface face.
- Palette: deep ink and navy establish trust; amber provides branded emphasis; blue is reserved for interactive states.
- Embedded payload: 262,456 bytes across two variable font files, served from the application origin with `font-display: swap`.
- Browser result: intended fonts loaded, semantic headings exposed, no console messages, compliant control targets, and no desktop or 390px overflow.

## Residual Risks

- Public registration and email password recovery remain intentionally disabled until a product supplies verification delivery, reset-token consumption, abuse controls, and tenant assignment policy.
- The legacy `PermissionService` remains the largest complexity hotspot and should be decomposed when its compatibility signatures can be retired.
- Source distributions still carry substantial Tabler demonstration assets. Runtime pages load only the referenced core CSS, JavaScript, fonts, and selected imagery, but a future repository-size pass should remove unused demos from source history.
- CSP blocks inline scripts but retains inline styles for legacy authenticated screens. Removing the remaining legacy style attributes is the next policy-tightening step.
- Browser evidence covers the in-app Chromium runtime at desktop and 390px. A release serving a new browser matrix should add automated cross-browser execution.

These are bounded follow-up concerns, not open design, security, database, or quality-gate blockers for the 95 target.
