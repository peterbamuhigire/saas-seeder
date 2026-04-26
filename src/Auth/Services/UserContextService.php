<?php

declare(strict_types=1);

namespace App\Auth\Services;

use PDO;

final class UserContextService
{
    public function __construct(private readonly PDO $db)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findFranchiseContext(string $usernameOrEmail): ?array
    {
        $stmt = $this->db->prepare('SELECT franchise_id FROM tbl_users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }
}
