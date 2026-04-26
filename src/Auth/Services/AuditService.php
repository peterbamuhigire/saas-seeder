<?php

declare(strict_types=1);

namespace App\Auth\Services;

use App\Observability\RequestContext;
use PDO;

/**
 * AuditService — Immutable audit trail for privileged operations.
 *
 * Logs actions to tbl_audit_log. This table is append-only by design —
 * application code should never UPDATE or DELETE audit records.
 */
final class AuditService
{
    public function __construct(
        private readonly PDO $db,
        private readonly ?RequestContext $context = null
    ) {
    }

    /**
     * Log an auditable action.
     *
     * @param string   $action     Action code (e.g., 'USER_CREATED', 'PERMISSION_CHANGED')
     * @param int|null $userId     Acting user ID (null for system actions)
     * @param int|null $franchiseId Franchise scope (null for system-wide)
     * @param string   $entityType Entity affected (e.g., 'user', 'role', 'franchise')
     * @param int|null $entityId   ID of affected entity
     * @param array    $details    Additional context (stored as JSON)
     */
    public function log(
        string $action,
        ?int $userId = null,
        ?int $franchiseId = null,
        string $entityType = '',
        ?int $entityId = null,
        array $details = [],
    ): void {
        $stmt = $this->db->prepare('
            INSERT INTO tbl_audit_log
              (user_id, franchise_id, action, entity_type, entity_id, details, ip_address, user_agent, created_at)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ');

        $stmt->execute([
            $userId,
            $franchiseId,
            $action,
            $entityType !== '' ? $entityType : null,
            $entityId,
            $this->encodeDetails($details),
            $this->requestContext()->ipAddress(),
            $this->requestContext()->userAgent(),
        ]);
    }

    /**
     * @param array<string, mixed> $details
     */
    private function encodeDetails(array $details): ?string
    {
        $payload = $this->requestContext()->withAuditDetails($details);

        return $payload === [] ? null : json_encode($payload, JSON_UNESCAPED_SLASHES);
    }

    private function requestContext(): RequestContext
    {
        return $this->context ?? RequestContext::fromGlobals();
    }
}
