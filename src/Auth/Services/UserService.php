<?php
declare(strict_types=1);

namespace App\Auth\Services;

use PDO;
use App\Auth\Helpers\PasswordHelper;

/**
 * UserService — Single Source of Truth for user account management.
 *
 * ALL user creation, password changes, and account updates MUST go through
 * this service. Never write raw INSERT/UPDATE on tbl_users elsewhere.
 *
 * Consumers:
 *   - super-user-dev.php  (scaffolding tool — creates initial super_admin)
 *   - API register endpoint (franchise signup)
 *   - Admin panel user management (future)
 *   - Member self-service (future)
 */
final class UserService
{
    private PDO $db;
    private PasswordHelper $passwordHelper;

    public function __construct(PDO $db, ?PasswordHelper $passwordHelper = null)
    {
        $this->db = $db;
        $this->passwordHelper = $passwordHelper ?? new PasswordHelper();
    }

    /**
     * Create a new user account.
     *
     * @param array{
     *   username: string,
     *   email: string,
     *   password: string,
     *   first_name: string,
     *   last_name: string,
     *   user_type: string,
     *   franchise_id: int|null,
     *   phone?: string,
     *   status?: string,
     *   force_password_change?: int
     * } $data
     *
     * @return array{id: int, username: string, email: string, user_type: string}
     * @throws \InvalidArgumentException on validation failure
     * @throws \RuntimeException on duplicate or DB failure
     */
    public function createUser(array $data): array
    {
        // --- Validate required fields ---
        $required = ['username', 'email', 'password', 'first_name', 'last_name', 'user_type'];
        foreach ($required as $field) {
            if (empty(trim((string) ($data[$field] ?? '')))) {
                throw new \InvalidArgumentException("Field '{$field}' is required.");
            }
        }

        $username  = trim($data['username']);
        $email     = trim($data['email']);
        $password  = $data['password'];
        $firstName = trim($data['first_name']);
        $lastName  = trim($data['last_name']);
        $userType  = $data['user_type'];
        $franchiseId = $data['franchise_id'] ?? null;
        $phone     = trim($data['phone'] ?? '');
        $status    = $data['status'] ?? 'active';
        $forcePasswordChange = (int) ($data['force_password_change'] ?? 0);

        // --- Validate email format ---
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address format.');
        }

        // --- Validate user_type ---
        $allowedTypes = ['super_admin', 'owner', 'distributor', 'staff', 'member'];
        if (!in_array($userType, $allowedTypes, true)) {
            throw new \InvalidArgumentException(
                "Invalid user_type '{$userType}'. Allowed: " . implode(', ', $allowedTypes)
            );
        }

        // --- Super admins must have NULL franchise_id ---
        if ($userType === 'super_admin' && $franchiseId !== null) {
            throw new \InvalidArgumentException('Super admin accounts must have NULL franchise_id.');
        }

        // --- Non-super-admin users must have a franchise_id ---
        if ($userType !== 'super_admin' && $franchiseId === null) {
            throw new \InvalidArgumentException("Non-super-admin users must have a franchise_id.");
        }

        // --- Validate password strength ---
        $passwordErrors = $this->passwordHelper->validatePasswordStrength($password);
        if (!empty($passwordErrors)) {
            throw new \InvalidArgumentException(implode(' ', $passwordErrors));
        }

        // --- Check for duplicate username or email ---
        $this->assertUniqueUser($username, $email, $franchiseId);

        // --- Hash password through PasswordHelper (Argon2ID + salt + pepper) ---
        $hashedPassword = $this->passwordHelper->hashPassword($password);

        // --- Insert ---
        $stmt = $this->db->prepare("
            INSERT INTO tbl_users
              (franchise_id, username, user_type, email, password_hash, first_name, last_name, phone, status, force_password_change, created_at)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $success = $stmt->execute([
            $franchiseId,
            $username,
            $userType,
            $email,
            $hashedPassword,
            $firstName,
            $lastName,
            $phone !== '' ? $phone : null,
            $status,
            $forcePasswordChange,
        ]);

        if (!$success) {
            throw new \RuntimeException('Failed to create user. Check database logs.');
        }

        $userId = (int) $this->db->lastInsertId();

        return [
            'id'        => $userId,
            'username'  => $username,
            'email'     => $email,
            'user_type' => $userType,
        ];
    }

    /**
     * Hash a password through the centralized PasswordHelper.
     *
     * Use this when you need a hash without creating a full user
     * (e.g. API signup requests that store a hash before verification).
     */
    public function hashPassword(string $password): string
    {
        return $this->passwordHelper->hashPassword($password);
    }

    /**
     * Validate password strength without creating a user.
     *
     * @return string[] Array of error messages (empty = valid)
     */
    public function validatePasswordStrength(string $password): array
    {
        return $this->passwordHelper->validatePasswordStrength($password);
    }

    /**
     * Check that username and email are not already taken.
     *
     * @throws \RuntimeException if a duplicate exists
     */
    private function assertUniqueUser(string $username, string $email, ?int $franchiseId): void
    {
        // Username must be globally unique
        $stmt = $this->db->prepare("SELECT id FROM tbl_users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            throw new \RuntimeException("Username '{$username}' is already taken.");
        }

        // Email must be unique within the same franchise (or globally for super admins)
        if ($franchiseId === null) {
            $stmt = $this->db->prepare("SELECT id FROM tbl_users WHERE email = ? AND franchise_id IS NULL LIMIT 1");
            $stmt->execute([$email]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM tbl_users WHERE email = ? AND franchise_id = ? LIMIT 1");
            $stmt->execute([$email, $franchiseId]);
        }
        if ($stmt->fetch()) {
            throw new \RuntimeException("Email '{$email}' is already registered.");
        }
    }
}
