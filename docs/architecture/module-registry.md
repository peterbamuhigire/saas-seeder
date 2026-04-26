# Module Registry

The module registry separates global product capability from tenant availability.

`ModuleRegistry` reads global module metadata. `ModuleAccessService` answers tenant-scoped access questions and treats super-admin users as globally allowed. `ModuleLifecycleService` enables/disables modules and checks dependencies before enablement.

Direct route access should check authentication first, module access second, and permission third.
