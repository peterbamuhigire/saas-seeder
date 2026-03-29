<?php
declare(strict_types=1);

namespace App\Auth\Services;

use PDO;

/**
 * AuditService — Immutable audit trail for privileged operations.
 *
 * Logs actions to tbl_audit_log. This table is append-only by design —
 * application code should never UPDATE or DELETE audit records.
 */
final class AuditService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
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
        $stmt = $this->db->prepare("
            INSERT INTO tbl_audit_log
              (user_id, franchise_id, action, entity_type, entity_id, details, ip_address, user_agent, created_at)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $userId,
            $franchiseId,
            $action,
            $entityType !== '' ? $entityType : null,
            $entityId,
            !empty($details) ? json_encode($details) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}
