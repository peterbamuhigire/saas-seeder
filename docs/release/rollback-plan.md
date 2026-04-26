# Rollback Plan

Code can roll back through the normal deployment mechanism. Database changes that affect auth, tokens, permissions, rate limiting, or audit posture are forward-fix-first after production traffic because removing them would destroy security evidence or session state assumptions.

If rollback is required:

1. Stop new deploy traffic.
2. Restore the previous application version.
3. Keep new security, token, rate-limit, and audit tables in place.
4. Forward-fix schema incompatibilities rather than dropping token or rate-limit data.
5. Record the rollback decision and residual risk in the release evidence file.
