# Phase 6: Supply Chain

**Clears:** 2 FAILs, 0 WARNs
**Depends on:** Nothing (runs in parallel)
**Files:** `.gitignore`, `composer.json`, `public/sign-in.php`, `public/super-user-dev.php`, `public/change-password.php`, `public/forgot-password.php`

---

## Findings Addressed

| ID | Type | Issue |
|----|------|-------|
| S-52 | FAIL | `composer.lock` gitignored — non-reproducible builds |
| S-53 | FAIL | CDN resources without SRI hashes (SweetAlert2 in 4 files) |

---

## Task 1: Commit composer.lock

**FILE:** `.gitignore`

**TASK:** Remove `composer.lock` from `.gitignore` and commit the lock file.

**STEPS:**
1. Remove the `composer.lock` line from `.gitignore`
2. Run `composer install` to generate `composer.lock`
3. `git add .gitignore composer.lock`
4. Commit

**VALIDATION:**
- [ ] `composer.lock` is tracked by git
- [ ] `composer install` on a fresh clone installs exact versions

---

## Task 2: Add SRI hashes to CDN resources (or bundle locally)

**TASK:** Bundle SweetAlert2 locally instead of loading from CDN. This eliminates the SRI requirement entirely and removes an external dependency.

**STEPS:**
1. Download SweetAlert2 CSS and JS:
   - `curl -o public/assets/vendor/sweetalert2/sweetalert2.min.css https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css`
   - `curl -o public/assets/vendor/sweetalert2/sweetalert2.min.js https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js`
2. Update all 4 files to use local paths:
   - `sign-in.php`: Replace CDN CSS link and JS script
   - `super-user-dev.php`: Same
   - `change-password.php`: Same
   - `forgot-password.php`: Same

**CODE:** Replace:
```html
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
```
With:
```html
<link href="/assets/vendor/sweetalert2/sweetalert2.min.css" rel="stylesheet">
```

Replace:
```html
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```
With:
```html
<script src="/assets/vendor/sweetalert2/sweetalert2.min.js"></script>
```

**CONSTRAINTS:**
- Create `public/assets/vendor/sweetalert2/` directory
- Pin a specific version (e.g., 11.14.5) — note the version in a comment
- Add `public/assets/vendor/` to the project (NOT gitignored)

**VALIDATION:**
- [ ] `grep -rn "cdn.jsdelivr" public/ --include="*.php"` returns empty
- [ ] SweetAlert2 works on sign-in page

---

## Status

| Task | Status |
|------|--------|
| 1: Commit composer.lock | not-started |
| 2: Bundle SweetAlert2 locally | not-started |
