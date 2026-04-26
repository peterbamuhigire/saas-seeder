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

use App\Auth\DTO\LoginDTO;
use App\Auth\Helpers\{CookieHelper, PasswordHelper};
use App\Auth\Security\AuthAuditLogger;
use App\Auth\Services\{AuthService, PermissionService, TokenService};
use App\Auth\Services\AuditService;
use App\Auth\Token\{AccessTokenService, RefreshTokenRepository, RefreshTokenService};
use App\Config\Database;
use App\Http\Middleware\{MethodGuard, RateLimitMiddleware};
use App\Http\RateLimit\RateLimitPolicy;
use App\Http\Request\JsonRequest;
use App\Http\Response\{ApiError, ApiResponse};
use App\Observability\Logger;

MethodGuard::require(['POST']);
$body = JsonRequest::fromGlobals()->jsonBody();

// --- Validate input ---
$usernameOrEmail = trim((string) ($body['username'] ?? $body['email'] ?? ''));
$password        = (string) ($body['password'] ?? '');

if ($usernameOrEmail === '' || $password === '') {
    ApiResponse::error(new ApiError('VALIDATION_FAILED', 'Username/email and password are required', 422));
}

$clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
RateLimitMiddleware::enforce(RateLimitPolicy::loginIp(), $clientIp);
RateLimitMiddleware::enforce(RateLimitPolicy::loginIdentity(), $usernameOrEmail);

// --- Authenticate through centralized services ---
try {
    $db = Database::getInstance()->getConnection();
    $logger = Logger::fromGlobals();
    $audit = new AuthAuditLogger(new AuditService($db));
    $authService = new AuthService(
        $db,
        new TokenService($db),
        new PermissionService($db),
        new PasswordHelper(),
        new CookieHelper(),
        $audit
    );

    $loginDTO = new LoginDTO(
        $usernameOrEmail,
        $password,
        $clientIp,
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
        ApiResponse::error(new ApiError('AUTH_INVALID_CREDENTIALS', 'Invalid credentials', $httpCode));
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
        (int) ($_ENV['JWT_REFRESH_TTL'] ?? 2592000),
        $audit
    );
    $tokenPair = $refreshTokens->issuePair(
        $result->getUserId(),
        $result->getFranchiseId(),
        isset($body['device_id']) ? trim((string) $body['device_id']) : null,
        $_SERVER['HTTP_USER_AGENT'] ?? 'API Client',
        $clientIp
    );

    ApiResponse::success([
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
    ($logger ?? Logger::fromGlobals())->error('API login error', [
        'exception' => $e::class,
        'message' => $e->getMessage(),
    ]);
    ApiResponse::error(new ApiError('INTERNAL_SERVER_ERROR', 'Internal server error', 500));
}
