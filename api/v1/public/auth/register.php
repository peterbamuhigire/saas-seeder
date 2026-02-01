<?php
declare(strict_types=1);

/**
 * POST /public/auth/register
 * Captures franchise signup request and issues a verification token (email/SMS delivery to be integrated).
 */

require_once __DIR__ . '/../../../../bootstrap.php';

use DateTime;
use PDO;

require_method('POST');
$body = read_json_body();

$email = trim((string)($body['email'] ?? ''));
$password = (string)($body['password'] ?? '');
$franchiseName = trim((string)($body['franchise_name'] ?? ''));
$plan = trim((string)($body['plan'] ?? 'trial'));
$language = trim((string)($body['language'] ?? 'en'));
$country = trim((string)($body['country'] ?? ''));
$currency = trim((string)($body['currency'] ?? 'UGX'));
$phone = trim((string)($body['phone'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(422, ['success' => false, 'message' => 'Valid email is required']);
}
if ($password === '' || strlen($password) < 8) {
    json_response(422, ['success' => false, 'message' => 'Password must be at least 8 characters']);
}
if ($franchiseName === '') {
    json_response(422, ['success' => false, 'message' => 'franchise_name is required']);
}

$db = get_db();
ensure_signup_table($db);

// Prevent duplicate signup for same email pending/verified
$check = $db->prepare('SELECT id, is_verified FROM tbl_api_signup_requests WHERE email = ? ORDER BY id DESC LIMIT 1');
$check->execute([$email]);
if ($row = $check->fetch(PDO::FETCH_ASSOC)) {
    if ((int)$row['is_verified'] === 0) {
        json_response(409, ['success' => false, 'message' => 'Signup already pending verification']);
    }
}

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
    password_hash($password, PASSWORD_BCRYPT),
    $verifyToken,
    (new DateTime('+1 day'))->format('Y-m-d H:i:s'),
]);

json_response(201, [
    'success' => true,
    'message' => 'Signup created. Please verify email/SMS.',
    'data' => [
        'verify_token' => $verifyToken, // In production, send via email/SMS instead of returning
    ],
]);

function ensure_signup_table(PDO $db): void {
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->exec($sql);
}
