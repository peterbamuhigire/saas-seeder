<?php

declare(strict_types=1);

namespace App\Modules;

final readonly class ModuleManifest
{
    /**
     * @param list<string> $dependencies
     * @param array<string, mixed> $config
     */
    public function __construct(
        public string $code,
        public string $name,
        public string $version = '1.0.0',
        public bool $isCore = false,
        public string $status = 'active',
        public array $dependencies = [],
        public array $config = []
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        $config = [];
        if (isset($row['config']) && is_string($row['config']) && $row['config'] !== '') {
            $decoded = json_decode($row['config'], true);
            $config = is_array($decoded) ? $decoded : [];
        } elseif (isset($row['config']) && is_array($row['config'])) {
            $config = $row['config'];
        }

        return new self(
            strtoupper((string) ($row['code'] ?? '')),
            (string) ($row['name'] ?? ''),
            (string) ($row['version'] ?? '1.0.0'),
            (bool) ($row['is_core'] ?? false),
            (string) ($row['status'] ?? 'active'),
            [],
            $config
        );
    }
}
