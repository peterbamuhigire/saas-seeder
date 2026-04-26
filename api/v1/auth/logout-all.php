<?php
declare(strict_types=1);

/**
 * POST /api/v1/auth/logout-all
 * Revokes all refresh token families and access sessions for the user.
 */

use App\Auth\Token\{AccessTokenService, RefreshTokenRepository, RefreshTokenService};
use App\Config\Database;
use App\Http\Middleware\{BearerAuth, MethodGuard, RateLimitMiddleware};
use App\Http\RateLimit\RateLimitPolicy;
use App\Http\Response\{ApiError, ApiResponse};

require_once __DIR__ . '/../../../bootstrap.php';

MethodGuard::require(['POST']);
$accessToken = BearerAuth::token();

if ($accessToken === null || trim($accessToken) === '') {
    ApiResponse::error(new ApiError('AUTH_BEARER_TOKEN_REQUIRED', 'Bearer access token is required', 401));
}

RateLimitMiddleware::enforce(RateLimitPolicy::logout(), $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

$db = Database::getInstance()->getConnection();
$accessTokens = new AccessTokenService($db);
$claims = $accessTokens->validateClaims($accessToken);

if ($claims === null) {
    ApiResponse::error(new ApiError('AUTH_INVALID_TOKEN', 'Invalid access token', 401));
}

$refreshTokens = new RefreshTokenService(
    $db,
    $accessTokens,
    new RefreshTokenRepository($db),
    (int) ($_ENV['JWT_REFRESH_TTL'] ?? 2592000)
);
$refreshTokens->revokeAllForUser($claims->userId);

ApiResponse::success(null, 'Logged out from all devices');
