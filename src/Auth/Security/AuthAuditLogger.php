<?php

declare(strict_types=1);

namespace App\Auth\Security;

use App\Auth\Services\AuditService;

final class AuthAuditLogger
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    /**
     * @param array<string, mixed> $details
     */
    public function log(string $event, ?int $userId, ?int $franchiseId, array $details = []): void
    {
        $this->audit->log($event, $userId, $franchiseId, 'auth', $userId, $details);
    }
}
