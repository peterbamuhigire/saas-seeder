# Phase 4: Accessibility

**Clears:** 6 FAILs, 2 WARNs
**Depends on:** Nothing (runs in parallel with Phases 3-7)
**Files:** `public/includes/topbar.php`, `public/sign-in.php`, `public/super-user-dev.php`, `public/change-password.php`, `public/forgot-password.php`, `public/memberpanel/index.php`, `public/access-denied.php`

---

## Findings Addressed

| ID | Type | Issue | 
|----|------|-------|
| UX-F1 | FAIL | Skip-to-content link missing on all 10 pages |
| UX-F2 | FAIL | `aria-required="true"` missing on form inputs |
| UX-F3 | FAIL | `aria-live="polite"` missing on alert containers |
| UX-F4 | FAIL | `change-password.php` alerts lack `role="alert"` |
| UX-F5 | FAIL | `forgot-password.php` input lacks `required` |
| UX-F6 | FAIL | `memberpanel/index.php` empty state has no CTA |
| UX-W9 | WARN | `access-denied.php` unescaped icon variable |
| UX-W12 | WARN | Memberpanel CTA (same as F6) |

---

## Task 1: Skip-to-content link (fixes all 10 pages at once)

**FILE:** `public/includes/topbar.php`

**TASK:** Add a visually-hidden skip link as the very first element inside `<header>`.

**CODE:** Add immediately after `<header class="navbar navbar-expand-md d-print-none">`:
```html
<a href="#main-body" class="visually-hidden-focusable position-absolute" style="z-index:9999">Skip to main content</a>
```

**CONSTRAINTS:**
- All pages that use the topbar already have `id="main-body"` on their content area (set in skeleton.php, dashboard.php, etc.)
- Auth pages (sign-in, sign-up, change-password, forgot-password) don't use topbar — they need their own skip link at the top of `<body>` targeting their main form container
- `access-denied.php` doesn't use topbar — add skip link targeting `.access-denied-container`

**VALIDATION:**
- [ ] Tab once on any page and "Skip to main content" appears
- [ ] Pressing Enter on the skip link scrolls to main content

---

## Task 2: `aria-required="true"` on all required inputs

**FILES:** `sign-in.php`, `super-user-dev.php`, `change-password.php`

**TASK:** Add `aria-required="true"` to every `<input>` that has the `required` attribute.

**CHANGES:**
- `sign-in.php`: username input (~line 369), password input (~line 383)
- `super-user-dev.php`: first_name, last_name, username, email, password, confirm_password (6 inputs)
- `change-password.php`: current_password, new_password (2 inputs)

**VALIDATION:**
- [ ] `grep -n "required" public/sign-in.php public/super-user-dev.php public/change-password.php` — every `required` input also has `aria-required="true"`

---

## Task 3: `aria-live="polite"` and `role="alert"` on alert containers

**FILES:** `sign-in.php`, `super-user-dev.php`, `change-password.php`

**TASK:** Add `role="alert" aria-live="polite"` to all error/success alert `<div>` elements.

**CHANGES:**
- `sign-in.php`: error alert (~line 346), success alert (~line 353)
- `super-user-dev.php`: error alert (~line 240)
- `change-password.php`: error alert (~line 147), success alert (~line 154) — add both `role="alert"` and `aria-live="polite"`

**VALIDATION:**
- [ ] All alerts have both `role="alert"` and `aria-live="polite"`

---

## Task 4: forgot-password.php `required` attribute

**FILE:** `public/forgot-password.php`

**TASK:** Add `required aria-required="true"` to the identifier input (line 84).

**CODE:** Change:
```html
<input type="text" class="form-control" id="identifier" name="identifier" autocomplete="username">
```
To:
```html
<input type="text" class="form-control" id="identifier" name="identifier" autocomplete="username" required aria-required="true">
```

**VALIDATION:**
- [ ] Form cannot be submitted with empty field (browser blocks it before JS intercepts)

---

## Task 5: memberpanel/index.php empty state CTA

**FILE:** `public/memberpanel/index.php`

**TASK:** Add an action button to the empty state.

**CODE:** Add after `</p>` inside the `.empty` div:
```html
<div class="empty-action">
    <a href="/change-password.php" class="btn btn-primary">Update your password</a>
</div>
```

**VALIDATION:**
- [ ] Empty state shows a clickable CTA button

---

## Task 6: Escape icon variable in access-denied.php

**FILE:** `public/access-denied.php`

**TASK:** Add `htmlspecialchars()` to the icon class output at line 306.

**CODE:** Change:
```php
icon-tabler-<?php echo $message['icon']; ?>
```
To:
```php
icon-tabler-<?php echo htmlspecialchars($message['icon'], ENT_QUOTES, 'UTF-8'); ?>
```

**VALIDATION:**
- [ ] Page renders correctly with all 4 icon variants

---

## Status

| Task | Status |
|------|--------|
| 1: Skip-to-content | not-started |
| 2: aria-required | not-started |
| 3: aria-live + role | not-started |
| 4: forgot-password required | not-started |
| 5: memberpanel CTA | not-started |
| 6: Escape icon variable | not-started |
