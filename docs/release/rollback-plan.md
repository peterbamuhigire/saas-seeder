# Rollback Plan

Code can roll back through the normal deployment mechanism. Database changes in phases 5-7 are forward-fix-only after production traffic because they affect security and token state.

If rollback is required:

1. Stop new deploy traffic.
2. Restore the previous application version.
3. Keep new security tables in place.
4. Forward-fix schema incompatibilities rather than dropping token or rate-limit data.
