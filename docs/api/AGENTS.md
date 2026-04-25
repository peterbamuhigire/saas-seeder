# API Agents Guide

## Ownership

- **Owner:** API owner for API contracts, examples, runtime docs, and endpoint behavior.
- **Update trigger:** update this file when API envelope rules, auth model, endpoint lifecycle, rate-limit policy, CORS policy, or OpenAPI requirements change.

## Scope

This guide governs API documentation under `docs/api/` and the contract evidence required before API endpoint implementation.

## API Rules

- OpenAPI is the canonical API contract once Phase 03 creates `docs/api/openapi.yml`.
- Endpoint behavior must match documented examples, error codes, auth requirements, and response envelopes.
- API docs must identify idempotency posture, rate limits, request ID behavior, and authentication mode.
- Auth endpoints must stay synchronized with the token lifecycle ADR and token storage policy.

## Validation Expectations

- API examples are checked against actual field names.
- Method errors and malformed JSON return documented JSON errors.
- Any endpoint change updates OpenAPI, examples, and feature tests in the same phase.
