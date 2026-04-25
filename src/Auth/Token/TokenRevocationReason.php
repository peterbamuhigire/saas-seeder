<?php
declare(strict_types=1);

namespace App\Auth\Token;

enum TokenRevocationReason: string
{
    case LOGOUT = 'logout';
    case LOGOUT_ALL = 'logout_all';
    case ROTATED = 'rotated';
    case REUSE_DETECTED = 'reuse_detected';
    case EXPIRED = 'expired';
    case ADMIN_REVOKED = 'admin_revoked';
}
