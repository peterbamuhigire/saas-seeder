<?php
declare(strict_types=1);

/**
 * POST /api/v1/auth/logout-all
 * Revokes all refresh token families and access sessions for the user.
 */

use App\Auth\Token\{AccessTokenService, RefreshTokenRepository, RefreshTokenService};

require_once __DIR__ . '/../../../bootstrap.php';

require_method('POST');
$accessToken = bearer_token();

if ($accessToken === null || trim($accessToken) === '') {
    errorResponse('Bearer access token is required', 401);
}

$db = get_db();
$accessTokens = new AccessTokenService($db);
$claims = $accessTokens->validateClaims($accessToken);

if ($claims === null) {
    errorResponse('Invalid access token', 401);
}

$refreshTokens = new RefreshTokenService(
    $db,
    $accessTokens,
    new RefreshTokenRepository($db),
    (int) ($_ENV['JWT_REFRESH_TTL'] ?? 2592000)
);
$refreshTokens->revokeAllForUser($claims->userId);

jsonResponse(true, null, 'Logged out from all devices');
