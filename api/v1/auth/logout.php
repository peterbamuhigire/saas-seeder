<?php
declare(strict_types=1);

/**
 * POST /api/v1/auth/logout
 * Revokes the current refresh token/device and optional access token.
 */

use App\Auth\Token\{AccessTokenService, RefreshTokenRepository, RefreshTokenService};

require_once __DIR__ . '/../../../bootstrap.php';

require_method('POST');
$body = read_json_body();
$bearerToken = bearer_token();
$refreshToken = trim((string) ($body['refresh_token'] ?? $bearerToken ?? ''));
$accessToken = isset($body['access_token'])
    ? trim((string) $body['access_token'])
    : (isset($body['refresh_token']) ? $bearerToken : null);

if ($refreshToken === '') {
    errorResponse('Refresh token is required', 400);
}

$db = get_db();
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

jsonResponse(true, null, 'Logged out for this device');
