# SaaS Template (Tabler)

This repository is a starter template for SaaS projects with Tabler UI.

## What this script did

- Moved Tabler assets into public/assets/tabler
- Removed demo HTML files (except index, sign-in, sign-up, forgot-password)
- Converted those pages to PHP and moved them into public
- Created shared includes and basic admin/member panel pages

## Manual steps

1) Add API routing
- Create public/.htaccess (or vhost rules) to route /api to api/index.php

Example rule:
RewriteEngine On
RewriteRule ^api/(.*)$ /api/index.php?path=$1 [QSA,L]

2) Build API front controller
- Create api/index.php that reads the path and dispatches to endpoints
- Keep API files under api/ (outside public)

3) Add auth
- Create src/config/auth.php and enforce session checks on panel pages
- Redirect to sign-in.php when not logged in

4) Create a seeder page template
- Add public/_templates/seeder-page.php and clone it for new pages

5) Update menus
- Edit public/includes/menus/admin.php and public/includes/menus/member.php

6) Uploads and images
- Use public/uploads for shared images (product images, avatars)

7) Clean unused assets
- Delete any unused libraries in public/assets/tabler/libs to keep the template lean

8) Replace branding
- Update page title, logos, and favicon in public/includes/head.php and public/assets

## Recommended structure

public/
  adminpanel/
  memberpanel/
  includes/
  assets/
  uploads/

api/
src/

## Notes

- Keep UI pages in public/
- Keep API outside public/
- Use shared includes for both panels
