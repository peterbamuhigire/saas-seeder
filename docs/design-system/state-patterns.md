# State Patterns

Every module should define these states:

- Empty: no records yet.
- Loading: async or deferred work is in progress.
- Error: recoverable failure with next action.
- Success: completed operation.
- Permission denied: authenticated but missing permission.
- Module disabled: tenant does not have the module enabled.
