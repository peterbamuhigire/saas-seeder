<?php

declare(strict_types=1);

namespace App\Auth\Security;

use PDO;

final class LoginAttemptService
{
    public function __construct(
        private readonly PDO $db,
        private readonly int $maxAttempts = 5,
        private readonly int $lockoutMinutes = 15
    ) {
    }

    public function recordFailure(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE tbl_users
             SET failed_login_attempts = failed_login_attempts + 1,
                 locked_until = CASE
                   WHEN failed_login_attempts + 1 >= ? THEN DATE_ADD(NOW(), INTERVAL ? MINUTE)
                   ELSE locked_until
                 END
             WHERE id = ?'
        );
        $stmt->execute([$this->maxAttempts, $this->lockoutMinutes, $userId]);
    }

    public function reset(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE tbl_users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?');
        $stmt->execute([$userId]);
    }
}
