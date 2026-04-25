# Phase 09: UI Shell, Design System, Form UX, And Primitives

## Objective

Turn the functional Tabler pages into a reusable SaaS product shell with documented UI primitives, accessible forms, tenant/module context, state completeness, and visual consistency.

## Skills Applied

- `webapp-gui-design`
- `practical-ui-design`
- `design-audit`
- `form-ux-design`
- `modular-saas-architecture`

## Current Problems

- The topbar skip link targets `#main-body`, but pages do not consistently provide that target.
- Auth pages duplicate large inline CSS blocks.
- Menus are hardcoded includes, not data-driven navigation.
- UI has no project-level token files.
- There are no reusable PHP UI primitives.
- Dashboard pages show basic empty states but not canonical SaaS shell examples.

## Deliverables

Create:

- `src/UI/Layout/Shell.php`
- `src/UI/Layout/PageHeader.php`
- `src/UI/Layout/Topbar.php`
- `src/UI/Layout/Footer.php`
- `src/UI/Layout/Breadcrumbs.php`
- `src/UI/Layout/TenantContext.php`
- `src/UI/Navigation/MenuItem.php`
- `src/UI/Navigation/MenuRegistry.php`
- `src/UI/Navigation/MenuRenderer.php`
- `src/UI/Navigation/ActiveRoute.php`
- `src/UI/Form/FormRenderer.php`
- `src/UI/Form/Field.php`
- `src/UI/Form/TextInput.php`
- `src/UI/Form/PasswordInput.php`
- `src/UI/Form/Checkbox.php`
- `src/UI/Form/FormAlert.php`
- `src/UI/Form/ValidationSummary.php`
- `src/UI/Components/Button.php`
- `src/UI/Components/EmptyState.php`
- `src/UI/Components/StateBlock.php`
- `src/UI/Components/KpiStrip.php`
- `src/UI/Components/DataTable.php`
- `src/UI/Components/Pagination.php`
- `src/UI/Components/FilterBar.php`
- `src/UI/Components/TenantBadge.php`
- `src/UI/Components/ModuleBadge.php`
- `public/assets/css/seeder-tokens.css`
- `public/assets/css/seeder-components.css`
- `public/assets/js/seeder-ui.js`
- `public/assets/js/forms.js`
- `public/ui-examples/`
- `docs/design-system/README.md`
- `docs/design-system/tokens.md`
- `docs/design-system/tabler-usage.md`
- `docs/design-system/forms.md`
- `docs/design-system/components.md`
- `docs/design-system/state-patterns.md`
- `docs/design-system/menu-standard.md`
- `docs/design-system/icon-standard.md`
- `docs/implementation/ui-shell-contract.md`

## Work Breakdown

1. Define shell contract:
   - one `<main id="main-body" tabindex="-1">`,
   - consistent header slots,
   - tenant context slot,
   - global search placeholder,
   - account menu,
   - breadcrumbs.
2. Centralize panel includes so root/admin/member share head, foot, headers, topbar, and footer behaviour.
3. Create design token CSS.
4. Move repeated auth CSS into shared assets.
5. Normalize auth pages through `AuthLayout`.
6. Define menu registry with:
   - route,
   - label,
   - icon,
   - panel,
   - permission,
   - module,
   - active patterns.
7. Decide icon standard:
   - if PNG menu icons are mandatory, create asset path and audit script;
   - otherwise update docs to permit Tabler/Bootstrap icons consistently.
8. Add reusable state primitives:
   - empty,
   - loading,
   - error,
   - permission denied,
   - module disabled,
   - success.
9. Add canonical example pages:
   - dashboard KPI strip,
   - data table,
   - settings form,
   - detail page,
   - disabled module.
10. Add UI documentation and usage rules.

## Acceptance Criteria

- All authenticated shell pages have a working main landmark.
- Auth pages have equivalent skip/main accessibility.
- No new page-local inline CSS is needed for standard layouts.
- Menus contain no `href="#"` placeholders.
- Menu visibility matches permission and module checks.
- Every reusable component escapes output by default.
- Every form field has visible/accessibly named label, error slot, description slot, and state rules.
- Empty/loading/error/success states are documented and demonstrated.
- Mobile and desktop layouts have no overlapping text or clipped controls.

## Validation

Run:

```powershell
rg -n "href=\"#\"" public -g "*.php"
rg -n "<style>" public -g "*.php"
rg -n "id=\"main-body\"" public -g "*.php"
rg -n "aria-required=\"true\"" public -g "*.php"
```

Browser checks:

- 360px mobile,
- 768px tablet,
- 1280px laptop,
- 1920px desktop.

Accessibility checks:

- keyboard tab order,
- skip link focus,
- screen-reader alert announcements,
- contrast,
- reduced motion.

## Sub-Agent Use

Use a UI worker for shell and primitives, a separate worker for forms/auth pages, and a verifier for accessibility/static scans. Keep CSS and PHP component write scopes coordinated.

## Exit Gate

No new product module should be built until it can consume the shell, menu registry, state primitives, and form primitives.

