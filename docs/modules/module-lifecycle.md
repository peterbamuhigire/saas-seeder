# Module Lifecycle

1. Register the module in `tbl_modules`.
2. Register dependencies in `tbl_module_dependencies`.
3. Enable the module per tenant in `tbl_franchise_modules`.
4. Gate navigation and direct routes by module code.
5. Audit enable/disable events through `ModuleLifecycleService`.

Core modules require an explicit override flag before they can be disabled.
