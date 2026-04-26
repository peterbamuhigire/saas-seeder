<?php

declare(strict_types=1);

namespace App\Auth\Services;

use App\Auth\DTO\LoginDTO;
use PDO;

final class LoginAuthenticator
{
    public function __construct(private readonly PDO $db)
    {
    }

    /**
     * @param array<string, mixed> $franchiseContext
     * @return array<string, mixed>|null
     */
    public function authenticateStoredHash(LoginDTO $credentials, array $franchiseContext): ?array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_authenticate_user(?, ?, @p_user_id, @p_status, @p_password_hash)');
            $stmt->execute([$credentials->getUsername(), $franchiseContext['franchise_id']]);
            $stmt->closeCursor();

            $result = $this->db->query('SELECT @p_user_id as user_id, @p_status as status, @p_password_hash as stored_hash')
                ->fetch(PDO::FETCH_ASSOC);

            return is_array($result) ? $result : null;
        } catch (\PDOException $e) {
            error_log('sp_authenticate_user failed: ' . $e->getMessage());
            return $this->manualLookup($credentials, $franchiseContext['franchise_id'] ?? null);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function manualLookup(LoginDTO $credentials, mixed $franchiseId): ?array
    {
        if ($franchiseId === null) {
            $stmt = $this->db->prepare('SELECT id, password_hash FROM tbl_users WHERE (username = ? OR email = ?) AND franchise_id IS NULL LIMIT 1');
            $stmt->execute([$credentials->getUsername(), $credentials->getUsername()]);
        } else {
            $stmt = $this->db->prepare('SELECT id, password_hash FROM tbl_users WHERE (username = ? OR email = ?) AND franchise_id = ? LIMIT 1');
            $stmt->execute([$credentials->getUsername(), $credentials->getUsername(), $franchiseId]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return null;
        }

        return [
            'user_id' => $row['id'],
            'status' => 'SUCCESS',
            'stored_hash' => $row['password_hash'],
        ];
    }
}
