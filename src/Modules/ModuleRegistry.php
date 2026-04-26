<?php

declare(strict_types=1);

namespace App\Modules;

use PDO;

final class ModuleRegistry
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function find(string $moduleCode): ?ModuleManifest
    {
        $stmt = $this->db->prepare(
            'SELECT code, name, version, is_core, status, config
             FROM tbl_modules
             WHERE code = ?
             LIMIT 1'
        );
        $stmt->execute([strtoupper($moduleCode)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? ModuleManifest::fromArray($row) : null;
    }

    /**
     * @return list<ModuleManifest>
     */
    public function activeModules(): array
    {
        $rows = $this->db
            ->query("SELECT code, name, version, is_core, status, config FROM tbl_modules WHERE status = 'active' ORDER BY name")
            ->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static fn (array $row): ModuleManifest => ModuleManifest::fromArray($row), $rows);
    }
}
