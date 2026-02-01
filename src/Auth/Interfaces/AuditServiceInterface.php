<?php
// /src/Auth/Interfaces/AuditServiceInterface.php

namespace App\Auth\Interfaces;

interface AuditServiceInterface 
{
    /**
     * Log audit event
     */
    public function logAudit(
        int $userId,
        string $action,
        string $tableName,
        int $recordId,
        ?array $oldValues,
        ?array $newValues
    ): void;
    
    /**
     * Get audit logs for entity
     */
    public function getAuditLogs(string $tableName, int $recordId): array;
    
    /**
     * Get audit logs for user
     */
    public function getUserAuditLogs(int $userId): array;
}
