<?php
declare(strict_types=1);

/**
 * POST /api/v1/public/auth/register
 *
 * Captures franchise signup request and issues a verification token.
 * Email/SMS delivery to be integrated per project.
 *
 * Password hashing is delegated to UserService (single source of truth).
 */

require_once __DIR__ . '/../../../../bootstrap.php';

use App\Auth\Services\UserService;
use App\Config\Database;
use PDO;

// --- Method guard ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// --- Read JSON body ---
$rawBody = file_get_contents('php://input');
$body = json_decode($rawBody ?: '', true);
if (!is_array($body)) {
    errorResponse('Invalid JSON body', 400);
}

$email         = trim((string) ($body['email'] ?? ''));
$password      = (string) ($body['password'] ?? '');
$franchiseName = trim((string) ($body['franchise_name'] ?? ''));
$plan          = trim((string) ($body['plan'] ?? 'trial'));
$language      = trim((string) ($body['language'] ?? 'en'));
$country       = trim((string) ($body['country'] ?? ''));
$currency      = trim((string) ($body['currency'] ?? 'UGX'));
$phone         = trim((string) ($body['phone'] ?? ''));

// --- Validate input ---
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    errorResponse('Valid email is required', 422);
}
if ($franchiseName === '') {
    errorResponse('franchise_name is required', 422);
}

// Validate password strength through UserService
$db = (new Database())->getConnection();
$userService = new UserService($db);

$passwordErrors = $userService->validatePasswordStrength($password);
if (!empty($passwordErrors)) {
    errorResponse(implode(' ', $passwordErrors), 422);
}

// Ensure signup table exists
ensureSignupTable($db);

// Prevent duplicate signup for same email pending/verified
$check = $db->prepare('SELECT id, is_verified FROM tbl_api_signup_requests WHERE email = ? ORDER BY id DESC LIMIT 1');
$check->execute([$email]);
if ($row = $check->fetch(PDO::FETCH_ASSOC)) {
    if ((int) $row['is_verified'] === 0) {
        errorResponse('Signup already pending verification', 409);
    }
}

// Hash password through UserService (single source of truth)
$hashedPassword = $userService->hashPassword($password);

$verifyToken = bin2hex(random_bytes(16));
$stmt = $db->prepare('
    INSERT INTO tbl_api_signup_requests
    (email, phone, franchise_name, plan_code, language, country, currency, password_hash, verify_token, verify_token_expires_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
');
$stmt->execute([
    $email,
    $phone !== '' ? $phone : null,
    $franchiseName,
    $plan,
    $language,
    $country !== '' ? $country : null,
    $currency !== '' ? $currency : null,
    $hashedPassword,
    $verifyToken,
    date('Y-m-d H:i:s', strtotime('+1 day')),
]);

jsonResponse(true, [
    'verify_token' => $verifyToken, // In production, send via email/SMS instead of returning
], 'Signup created. Please verify email/SMS.', 201);

function ensureSignupTable(PDO $db): void
{
    $sql = "CREATE TABLE IF NOT EXISTS `tbl_api_signup_requests` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `email` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(50) NULL,
        `franchise_name` VARCHAR(255) NOT NULL,
        `plan_code` VARCHAR(100) NOT NULL,
        `language` VARCHAR(10) DEFAULT 'en',
        `country` VARCHAR(80) NULL,
        `currency` VARCHAR(10) NULL,
        `password_hash` VARCHAR(255) NOT NULL,
        `verify_token` VARCHAR(64) NOT NULL,
        `verify_token_expires_at` DATETIME NOT NULL,
        `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
        `verified_at` DATETIME NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_email_latest` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sql);
}
