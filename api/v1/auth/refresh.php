<?php
declare(strict_types=1);

/**
 * POST /api/v1/auth/refresh
 * Rotates opaque refresh tokens and issues a new access token.
 */

use App\Auth\Token\{AccessTokenService, RefreshTokenRepository, RefreshTokenService};
use App\Auth\Security\AuthAuditLogger;
use App\Auth\Services\AuditService;
use App\Config\Database;
use App\Http\Middleware\{BearerAuth, MethodGuard, RateLimitMiddleware};
use App\Http\RateLimit\RateLimitPolicy;
use App\Http\Request\JsonRequest;
use App\Http\Response\{ApiError, ApiResponse};
use App\Observability\Logger;

require_once __DIR__ . '/../../../bootstrap.php';

MethodGuard::require(['POST']);
$body = JsonRequest::fromGlobals()->jsonBody();
$token = trim((string) ($body['refresh_token'] ?? BearerAuth::token() ?? ''));

if ($token === '') {
    ApiResponse::error(new ApiError('AUTH_REFRESH_TOKEN_REQUIRED', 'Refresh token is required', 400));
}

RateLimitMiddleware::enforce(RateLimitPolicy::refresh(), $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

try {
    $db = Database::getInstance()->getConnection();
    $logger = Logger::fromGlobals();
    $refreshTokens = new RefreshTokenService(
        $db,
        new AccessTokenService($db),
        new RefreshTokenRepository($db),
        (int) ($_ENV['JWT_REFRESH_TTL'] ?? 2592000),
        new AuthAuditLogger(new AuditService($db))
    );
    $pair = $refreshTokens->rotate(
        $token,
        isset($body['device_id']) ? trim((string) $body['device_id']) : null,
        $_SERVER['HTTP_USER_AGENT'] ?? 'API Client',
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    );

    ApiResponse::success([
        'access_token' => $pair->accessToken,
        'refresh_token' => $pair->refreshToken,
        'token_type' => $pair->tokenType,
        'expires_in' => $pair->expiresIn,
    ], 'Token refreshed');
} catch (\RuntimeException $e) {
    ($logger ?? Logger::fromGlobals())->warning('API refresh rejected', [
        'exception' => $e::class,
        'message' => $e->getMessage(),
    ]);
    $status = str_contains($e->getMessage(), 'reuse detected') ? 409 : 401;
    $code = $status === 409 ? 'AUTH_REFRESH_REUSE_DETECTED' : 'AUTH_INVALID_REFRESH_TOKEN';
    ApiResponse::error(new ApiError($code, $e->getMessage(), $status));
}
