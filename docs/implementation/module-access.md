# Module Access

Use `hasModuleAccess('MODULE_CODE')` for UI visibility and `requireModuleAccess('MODULE_CODE')` for route guards.

Disabled direct access redirects to `/module-disabled.php?module=MODULE_CODE`. Navigation providers should filter entries by the same module code so route and menu behavior stay aligned.
