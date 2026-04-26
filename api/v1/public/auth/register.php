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
use App\Http\Middleware\{MethodGuard, RateLimitMiddleware};
use App\Http\RateLimit\RateLimitPolicy;
use App\Http\Request\JsonRequest;
use App\Http\Response\{ApiError, ApiResponse};

MethodGuard::require(['POST']);
$body = JsonRequest::fromGlobals()->jsonBody();

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
    ApiResponse::error(new ApiError('VALIDATION_FAILED', 'Valid email is required', 422));
}
if ($franchiseName === '') {
    ApiResponse::error(new ApiError('VALIDATION_FAILED', 'franchise_name is required', 422));
}

RateLimitMiddleware::enforce(RateLimitPolicy::registerIp(), $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
RateLimitMiddleware::enforce(RateLimitPolicy::registerIdentity(), $email);

// Validate password strength through UserService
$db = Database::getInstance()->getConnection();
$userService = new UserService($db);

$passwordErrors = $userService->validatePasswordStrength($password);
if (!empty($passwordErrors)) {
    ApiResponse::error(new ApiError('VALIDATION_FAILED', implode(' ', $passwordErrors), 422));
}

// Prevent duplicate signup for same email pending/verified
$check = $db->prepare('SELECT id, is_verified FROM tbl_api_signup_requests WHERE email = ? ORDER BY id DESC LIMIT 1');
$check->execute([$email]);
if ($row = $check->fetch(PDO::FETCH_ASSOC)) {
    if ((int) $row['is_verified'] === 0) {
        ApiResponse::error(new ApiError('SIGNUP_ALREADY_PENDING', 'Signup already pending verification', 409));
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

$response = [];
if (($_ENV['APP_ENV'] ?? 'development') !== 'production') {
    $response['verify_token'] = $verifyToken;
}

ApiResponse::success($response, 'Signup created. Please verify email/SMS.', 201);
