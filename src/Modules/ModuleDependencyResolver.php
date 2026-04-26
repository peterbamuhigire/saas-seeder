<?php

declare(strict_types=1);

namespace App\Modules;

use PDO;

final class ModuleDependencyResolver
{
    public function __construct(private readonly PDO $db)
    {
    }

    /**
     * @return list<string>
     */
    public function missingDependencies(int $franchiseId, string $moduleCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.depends_on_module_code
             FROM tbl_module_dependencies d
             LEFT JOIN tbl_franchise_modules fm
               ON fm.franchise_id = ?
              AND fm.module_code = d.depends_on_module_code
              AND fm.status = 'enabled'
             WHERE d.module_code = ?
               AND fm.module_code IS NULL"
        );
        $stmt->execute([$franchiseId, strtoupper($moduleCode)]);

        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
}
