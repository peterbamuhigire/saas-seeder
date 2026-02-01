<?php
namespace App\Auth\Helpers;

class PasswordHelper 
{
    private string $pepper;
    private array $options;

    public function __construct() 
    {
        $pepper = null;

        // Try to get pepper from various sources
        if (!empty($_ENV['PASSWORD_PEPPER'])) {
            $pepper = $_ENV['PASSWORD_PEPPER'];
        } elseif (!empty($_SERVER['PASSWORD_PEPPER'])) {
            $pepper = $_SERVER['PASSWORD_PEPPER'];
        } elseif (getenv('PASSWORD_PEPPER') !== false) {
            $pepper = getenv('PASSWORD_PEPPER');
        }

        // Use fallback if no pepper found
        $this->pepper = $pepper ?: 'fallback_pepper_value_for_dev';
        
        $this->options = [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ];
    }

    public function hashPassword(string $password): string 
    {
        $salt = bin2hex(random_bytes(16));
        $peppered = hash_hmac('sha256', $password, $this->pepper);
        $hash = password_hash($peppered . $salt, PASSWORD_ARGON2ID, $this->options);
        return $salt . $hash;
    }

    public function verifyPassword(string $password, string $storedHash): bool 
    {
        $salt = substr($storedHash, 0, 32);
        $hash = substr($storedHash, 32);
        $peppered = hash_hmac('sha256', $password, $this->pepper);
        return password_verify($peppered . $salt, $hash);
    }

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
