<?php
declare(strict_types=1);

/**
 * POST /api/v1/auth/logout
 * Revokes the current refresh token/device and optional access token.
 */

use App\Auth\Token\{AccessTokenService, RefreshTokenRepository, RefreshTokenService};
use App\Config\Database;
use App\Http\Middleware\{BearerAuth, MethodGuard, RateLimitMiddleware};
use App\Http\RateLimit\RateLimitPolicy;
use App\Http\Request\JsonRequest;
use App\Http\Response\{ApiError, ApiResponse};

require_once __DIR__ . '/../../../bootstrap.php';

MethodGuard::require(['POST']);
$body = JsonRequest::fromGlobals()->jsonBody();
$bearerToken = BearerAuth::token();
$refreshToken = trim((string) ($body['refresh_token'] ?? $bearerToken ?? ''));
$accessToken = isset($body['access_token'])
    ? trim((string) $body['access_token'])
    : (isset($body['refresh_token']) ? $bearerToken : null);

if ($refreshToken === '') {
    ApiResponse::error(new ApiError('AUTH_REFRESH_TOKEN_REQUIRED', 'Refresh token is required', 400));
}

RateLimitMiddleware::enforce(RateLimitPolicy::logout(), $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

$db = Database::getInstance()->getConnection();
$accessTokens = new AccessTokenService($db);
$accessTokenForRevocation = null;

if (is_string($accessToken) && trim($accessToken) !== '' && $accessTokens->validateClaims($accessToken) !== null) {
    $accessTokenForRevocation = $accessToken;
}

$refreshTokens = new RefreshTokenService(
    $db,
    $accessTokens,
    new RefreshTokenRepository($db),
    (int) ($_ENV['JWT_REFRESH_TTL'] ?? 2592000)
);

$refreshTokens->revokeCurrentDevice($refreshToken, $accessTokenForRevocation);

ApiResponse::success(null, 'Logged out for this device');
