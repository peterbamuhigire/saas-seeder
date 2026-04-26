<?php

declare(strict_types=1);

namespace App\Modules;

use App\Auth\Services\AuditService;
use App\Observability\AuditEvent;
use RuntimeException;
use PDO;

final class ModuleLifecycleService
{
    public function __construct(
        private readonly PDO $db,
        private readonly ModuleRegistry $registry,
        private readonly ModuleDependencyResolver $dependencies,
        private readonly ?AuditService $audit = null
    ) {
    }

    public function enable(int $franchiseId, string $moduleCode, ?int $actorUserId = null): void
    {
        $code = strtoupper($moduleCode);
        $module = $this->registry->find($code);
        if ($module === null || $module->status !== 'active') {
            throw new RuntimeException('Module is not available.');
        }

        $missing = $this->dependencies->missingDependencies($franchiseId, $code);
        if ($missing !== []) {
            throw new RuntimeException('Missing module dependencies: ' . implode(', ', $missing));
        }

        $stmt = $this->db->prepare(
            "INSERT INTO tbl_franchise_modules (franchise_id, module_code, status, enabled_at, enabled_by)
             VALUES (?, ?, 'enabled', CURRENT_TIMESTAMP, ?)
             ON DUPLICATE KEY UPDATE status = 'enabled', enabled_at = CURRENT_TIMESTAMP, enabled_by = VALUES(enabled_by), disabled_at = NULL, disabled_by = NULL"
        );
        $stmt->execute([$franchiseId, $code, $actorUserId]);
        $this->audit(AuditEvent::MODULE_ENABLED, $franchiseId, $code, $actorUserId);
    }

    public function disable(int $franchiseId, string $moduleCode, ?int $actorUserId = null, bool $allowCore = false): void
    {
        $code = strtoupper($moduleCode);
        $module = $this->registry->find($code);
        if ($module !== null && $module->isCore && !$allowCore) {
            throw new RuntimeException('Core modules cannot be disabled.');
        }

        $stmt = $this->db->prepare(
            "UPDATE tbl_franchise_modules
             SET status = 'disabled', disabled_at = CURRENT_TIMESTAMP, disabled_by = ?
             WHERE franchise_id = ? AND module_code = ?"
        );
        $stmt->execute([$actorUserId, $franchiseId, $code]);
        $this->audit(AuditEvent::MODULE_DISABLED, $franchiseId, $code, $actorUserId);
    }

    private function audit(string $action, int $franchiseId, string $moduleCode, ?int $actorUserId): void
    {
        if ($this->audit === null) {
            return;
        }

        $this->audit->log($action, $actorUserId, $franchiseId, 'module', null, ['module_code' => $moduleCode]);
    }
}
