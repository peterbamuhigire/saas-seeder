<?php

declare(strict_types=1);

namespace App\Observability;

final class AuditEvent
{
    public const AUTH_LOGIN_SUCCESS = 'auth.login.success';
    public const AUTH_LOGIN_FAILURE = 'auth.login.failure';
    public const AUTH_LOCKOUT = 'auth.lockout';
    public const AUTH_PASSWORD_CHANGED = 'auth.password.changed';
    public const AUTH_TOKEN_REFRESHED = 'auth.token.refreshed';
    public const AUTH_TOKEN_REUSE_DETECTED = 'auth.token.reuse_detected';
    public const AUTH_LOGOUT = 'auth.logout';
    public const AUTH_LOGOUT_ALL = 'auth.logout_all';
    public const PERMISSION_OVERRIDE = 'permission.override';
    public const MODULE_ENABLED = 'module.enabled';
    public const MODULE_DISABLED = 'module.disabled';
    public const MIGRATION_APPLIED = 'migration.applied';
}
