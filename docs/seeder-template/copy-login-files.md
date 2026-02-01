# Login and Logout Files to Copy from Maduuka

Copy these files from the Maduuka project into your new template repository.
Adjust paths if your new repo uses public/ as the web root.

## Core login and logout pages

- sign-in.php
- logout.php
- forgot-password.php
- sign-up.php (optional if you support self registration)
- access-denied.php

## Auth config and services

- src/config/auth.php
- src/config/database.php
- src/config/autoloader.php
- src/Auth/Services/AuthService.php
- src/Auth/Services/TokenService.php
- src/Auth/PermissionService.php
- src/Auth/Helpers/PasswordHelper.php
- src/Auth/Helpers/CSRFHelper.php
- src/Auth/Helpers/CookieHelper.php
- src/Auth/DTO/LoginDTO.php
- src/Auth/DTO/AuthResult.php
- src/Auth/DTO/AuthDTO.php

## Optional auth middleware

- src/Auth/Middleware/AuthMiddleware.php
- src/Auth/Middleware/PermissionMiddleware.php
- src/Auth/Middleware/RoleMiddleware.php

## UI includes referenced by login

- includes/foot.php
- includes/footer.php
- includes/infotexthtml.php (if referenced by your login page)

## JavaScript helpers used by login

- src/js/validatelogin.js (if referenced)

## Notes for the new template

- Maduuka uses root-level pages. The new standard is public/ as the web root.
- Update asset paths and include paths accordingly.
- Each panel should have its own includes folder in public/adminpanel/includes and public/memberpanel/includes.
- Keep API files outside public/ and route /api through the web server.
- Update CLAUDE.md with mysql.exe and php.exe paths for the dev machine.
