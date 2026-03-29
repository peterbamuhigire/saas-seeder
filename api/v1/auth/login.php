<?php
declare(strict_types=1);

/**
 * POST /api/v1/auth/login
 *
 * Authenticates user (email/username + password) via the centralized
 * AuthService, issues an access token, and returns user data.
 *
 * Uses the same AuthService + PasswordHelper + TokenService chain as
 * the web sign-in flow — single source of truth for authentication.
 */

require_once __DIR__ . '/../../../bootstrap.php';

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

// --- Validate input ---
$usernameOrEmail = trim((string) ($body['username'] ?? $body['email'] ?? ''));
$password        = (string) ($body['password'] ?? '');

if ($usernameOrEmail === '' || $password === '') {
    errorResponse('Username/email and password are required', 422);
}

// --- Authenticate through centralized services ---
use App\Auth\Services\{AuthService, TokenService, PermissionService};
use App\Auth\Helpers\{PasswordHelper, CookieHelper};
use App\Auth\DTO\LoginDTO;
use App\Config\Database;

try {
    $db = (new Database())->getConnection();
    $authService = new AuthService(
        $db,
        new TokenService($db),
        new PermissionService($db),
        new PasswordHelper(),
        new CookieHelper()
    );

    $loginDTO = new LoginDTO(
        $usernameOrEmail,
        $password,
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        $_SERVER['HTTP_USER_AGENT'] ?? 'API Client'
    );

    $result = $authService->authenticate($loginDTO);

    if ($result->getStatus() !== 'SUCCESS') {
        $statusCodes = [
            'USER_NOT_FOUND'    => 401,
            'INVALID_PASSWORD'  => 401,
            'ACCOUNT_LOCKED'    => 423,
            'ACCOUNT_INACTIVE'  => 403,
            'ACCOUNT_SUSPENDED' => 403,
        ];
        $httpCode = $statusCodes[$result->getStatus()] ?? 401;
        errorResponse('Invalid credentials', $httpCode);
    }

    $userData = $result->getUserData();

    jsonResponse(true, [
        'access_token' => $result->getToken(),
        'token_type'   => 'Bearer',
        'expires_in'   => 900,
        'user'         => [
            'id'           => $result->getUserId(),
            'franchise_id' => $result->getFranchiseId(),
            'username'     => $result->getUsername(),
            'email'        => $userData['email'] ?? null,
            'user_type'    => $userData['user_type'] ?? null,
        ],
    ], 'Login successful');

} catch (\Exception $e) {
    error_log('API login error: ' . $e->getMessage());
    errorResponse('Internal server error', 500);
}
