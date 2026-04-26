<?php

declare(strict_types=1);

namespace App\Modules;

use PDO;
use Throwable;

final class ModuleAccessService
{
    public function __construct(
        private readonly PDO $db,
        private readonly ?ModuleRegistry $registry = null
    ) {
    }

    public function hasAccess(?int $franchiseId, string $moduleCode, ?string $userType = null): bool
    {
        $code = strtoupper(trim($moduleCode));
        if ($code === '') {
            return false;
        }

        if ($userType === 'super_admin') {
            return true;
        }

        try {
            $module = ($this->registry ?? new ModuleRegistry($this->db))->find($code);
            if ($module === null || $module->status !== 'active') {
                return false;
            }

            if ($module->isCore) {
                return true;
            }

            if ($franchiseId === null || $franchiseId <= 0) {
                return false;
            }

            $stmt = $this->db->prepare(
                'SELECT status
                 FROM tbl_franchise_modules
                 WHERE franchise_id = ? AND module_code = ?
                 LIMIT 1'
            );
            $stmt->execute([$franchiseId, $code]);
            $status = $stmt->fetchColumn();

            return $status === 'enabled';
        } catch (Throwable $e) {
            error_log('Module access check failed: ' . $e->getMessage());
            return false;
        }
    }
}
