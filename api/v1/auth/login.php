<?php
declare(strict_types=1);

/**
 * POST /api/v1/auth/login
 *
 * Authenticates user (email/username + password), issues an access token plus
 * rotating opaque refresh token, and returns user data.
 *
 * Uses the same AuthService + PasswordHelper + TokenService chain as
 * the web sign-in flow — single source of truth for authentication.
 */

require_once __DIR__ . '/../../../bootstrap.php';

require_method('POST');
$body = read_json_body();

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
use App\Auth\Token\{AccessTokenService, RefreshTokenRepository, RefreshTokenService};
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
    $accessTokens = new AccessTokenService($db);

    if ($result->getToken() !== null) {
        $accessTokens->revokeToken($result->getToken());
    }

    $refreshTokens = new RefreshTokenService(
        $db,
        $accessTokens,
        new RefreshTokenRepository($db),
        (int) ($_ENV['JWT_REFRESH_TTL'] ?? 2592000)
    );
    $tokenPair = $refreshTokens->issuePair(
        $result->getUserId(),
        $result->getFranchiseId(),
        isset($body['device_id']) ? trim((string) $body['device_id']) : null,
        $_SERVER['HTTP_USER_AGENT'] ?? 'API Client',
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    );

    jsonResponse(true, [
        'access_token' => $tokenPair->accessToken,
        'refresh_token' => $tokenPair->refreshToken,
        'token_type'   => $tokenPair->tokenType,
        'expires_in'   => $tokenPair->expiresIn,
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
