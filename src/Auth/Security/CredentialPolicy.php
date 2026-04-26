<?php

declare(strict_types=1);

namespace App\Auth\Security;

final class CredentialPolicy
{
    /**
     * @return list<string>
     */
    public function validatePassword(string $password): array
    {
        $errors = [];
        if (strlen($password) < 12) {
            $errors[] = 'Password must be at least 12 characters.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must include an uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must include a lowercase letter.';
        }
        if (!preg_match('/\d/', $password)) {
            $errors[] = 'Password must include a number.';
        }

        return $errors;
    }
}
