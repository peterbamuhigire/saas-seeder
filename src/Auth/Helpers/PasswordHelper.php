<?php
declare(strict_types=1);

namespace App\Auth\Helpers;

final class PasswordHelper
{
    private string $pepper;
    private array $options;

    public function __construct()
    {
        $pepper = null;

        if (!empty($_ENV['PASSWORD_PEPPER'])) {
            $pepper = $_ENV['PASSWORD_PEPPER'];
        } elseif (!empty($_SERVER['PASSWORD_PEPPER'])) {
            $pepper = $_SERVER['PASSWORD_PEPPER'];
        } elseif (getenv('PASSWORD_PEPPER') !== false && getenv('PASSWORD_PEPPER') !== '') {
            $pepper = getenv('PASSWORD_PEPPER');
        }

        if (!$pepper) {
            throw new \RuntimeException(
                'PASSWORD_PEPPER is not set in .env. '
                . 'Generate one with: php -r "echo bin2hex(random_bytes(32));"'
            );
        }
        $this->pepper = $pepper;

        $this->options = [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ];
    }

    public function hashPassword(string $password): string
    {
        // 32-byte salt = 64 hex chars
        $salt = bin2hex(random_bytes(32));
        $peppered = hash_hmac('sha256', $password, $this->pepper);
        $hash = password_hash($peppered . $salt, PASSWORD_ARGON2ID, $this->options);
        return $salt . $hash;
    }

    public function verifyPassword(string $password, string $storedHash): bool
    {
        // Backward-compatible: detect salt length by checking hash prefix position
        // New format: 64-char salt + Argon2ID hash (starts with $argon2id$)
        // Old format: 32-char salt + Argon2ID hash (starts with $argon2id$)
        $argonPos = strpos($storedHash, '$argon2id$');
        if ($argonPos === false) {
            return false;
        }

        $salt = substr($storedHash, 0, $argonPos);
        $hash = substr($storedHash, $argonPos);
        $peppered = hash_hmac('sha256', $password, $this->pepper);
        return password_verify($peppered . $salt, $hash);
    }

    /**
     * @return string[] Array of error messages (empty = valid)
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain uppercase letters';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain lowercase letters';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain numbers';
        }

        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
            $errors[] = 'Password must contain special characters';
        }

        return $errors;
    }
}
