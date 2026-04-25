# Release Agents Guide

## Ownership

- **Owner:** release owner for operations, release evidence, rollback posture, and certification.
- **Update trigger:** update this file when release gates, runbooks, rollback policy, SLOs, known-exception rules, or certification workflow changes.

## Scope

This guide governs release documentation under `docs/release/` and release-adjacent operations evidence.

## Release Rules

- A release candidate must link validation evidence from testing, security, data, API, UI, and operations.
- Known exceptions must state reason, impact, mitigation, owner, and expiry date.
- Rollback posture must be documented for migrations, configuration changes, and deployable code changes.
- Final April certification must link the scorecard, risk register, decision log, and artifact index.

## Review Checklist

- No critical risk remains open without an accepted exception.
- Release evidence identifies exact commands, reviewer, date, and residual risk.
- Documentation status in `docs/plans/AGENTS.md` and `docs/plans/INDEX.md` matches the actual roadmap state.
