# Dependency Map

**Owner:** remediation lead.  
**Update trigger:** update when a phase dependency, critical-path decision, or parallel worker write scope changes.

## Critical Path

```text
01 Governance
  -> 02 Architecture and ADRs
    -> 03 API contract/runtime
      -> 04 Token lifecycle
        -> 05 Migration governance
          -> 06 Module registry
            -> 09 UI shell module/tenant UX
      -> 07 Security hardening
    -> 08 PHP modernization
      -> 10 Quality gates
        -> 11 Release evidence
          -> 12 Final certification
```

## Parallel Lanes

After Phase 02:

- API lane: 03 -> 04 -> 07 -> 10
- Data/module lane: 05 -> 06 -> 10
- UI lane: 09 -> 10
- PHP architecture lane: 08 -> 10
- Operations lane: 11 after evidence from 10

## Phase Dependency Matrix

| Phase | Must start after | Can run in parallel with | Blocks |
|---:|---|---|---|
| 01 | None | None | All implementation phases |
| 02 | 01 | None | 03, 04, 05, 06, 08, 09 |
| 03 | 01, 02 | 05 planning, 08 planning | 04, 07, API tests in 10 |
| 04 | 02, 03 | 05 planning after token decisions are stable | 05 final migration design, 07, auth certification |
| 05 | 02, Phase 4 token decisions | 08 service planning | 06, data tests in 10 |
| 06 | 05 | 09 interface planning | 09 module/tenant UX, module tests in 10 |
| 07 | 03, 04, 05 | 08 hardening support | 10 security gates, 11 release evidence |
| 08 | 02, 03, 05 | 05/07 if tests protect touched flows | 10 static-analysis and refactor gates |
| 09 | 01, 02, Phase 6 interface | 10 UI test planning | 10 accessibility/UI gates |
| 10 | Progressive evidence from 03-09 | 11 evidence planning | 11, 12 |
| 11 | 03-10 | None after 10 is green | 12 |
| 12 | 01-11 | None | Final certification |

## Blocking Decisions

| Decision | Blocks | ADR |
|---|---|---|
| Token model | Phases 03, 04, 07, 10 | `docs/architecture/adr/0001-auth-token-model.md` |
| API envelope | Phases 03, 04, 07, 10 | `docs/architecture/adr/0002-api-runtime-contract.md` |
| Migration ledger | Phases 05, 06, 10, 11 | `docs/architecture/adr/0003-migration-governance.md` |
| Module registry | Phases 06, 09, 10 | `docs/architecture/adr/0004-module-registry-model.md` |
| UI shell contract | Phases 09, 10, 12 | `docs/architecture/adr/0005-ui-shell-contract.md` |
| Quality gate model | Phases 08, 10, 11, 12 | `docs/architecture/adr/0006-quality-gate-model.md` |

## Phase Completion Order

1. Complete Phase 01.
2. Complete Phase 02 ADRs.
3. Complete Phase 03 before rewriting auth endpoints.
4. Complete Phase 04 before final security tests.
5. Complete Phase 05 before module registry implementation.
6. Complete Phase 06 before final tenant/module UI.
7. Complete Phase 07 before security certification.
8. Complete Phase 08 after tests can protect refactors.
9. Complete Phase 09 after shell/module interfaces exist.
10. Complete Phase 10 before release evidence.
11. Complete Phase 11 before final certification.
12. Complete Phase 12 last.
