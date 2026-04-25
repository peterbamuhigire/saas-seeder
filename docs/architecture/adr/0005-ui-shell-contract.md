# ADR-0005: UI Shell Contract

Status: Accepted  
Date: 2026-04-26  
Phase: April World-Class Phase 02

## Context

Browser pages already use Tabler assets and include files under root, admin panel, and member panel folders. Without a shared shell contract, later pages will duplicate panel chrome, menu checks, alerts, and escaping behavior.

## Decision

Keep Tabler as the visual base and render pages through shared PHP shell components/includes.

The shell owns:

- HTML head and asset loading.
- Topbar, footer, and panel navigation.
- Page title, breadcrumbs, flash messages, and denial states.
- Module-aware menu rendering.
- Common form, alert, table, card, and empty-state primitives.
- Output escaping helpers at render boundaries.

Panel-specific includes may remain as adapters, but ordinary module pages must use the shared shell primitives once Phase 09 introduces them.

## Consequences

- Phase 09 builds reusable PHP primitives around existing Tabler patterns.
- Module menus consume ADR-0004 registry data.
- Accessibility and visual regression checks target the shared shell first.
- Pages keep business logic out of shell includes.

## Rejected Alternatives

| Alternative | Reason rejected |
|---|---|
| Continue page-local Tabler markup everywhere | Increases drift and makes accessibility/security fixes repetitive. |
| Replace Tabler during remediation | Too much scope for the remediation path and unnecessary for a production-ready scaffold. |
| Build an SPA shell now | Current stack is PHP-rendered pages; an SPA would add runtime and routing complexity outside the plan. |
