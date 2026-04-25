<?php
declare(strict_types=1);

/**
 * POST /api/v1/auth/refresh
 * Rotates opaque refresh tokens and issues a new access token.
 */

use App\Auth\Token\{AccessTokenService, RefreshTokenRepository, RefreshTokenService};

require_once __DIR__ . '/../../../bootstrap.php';

require_method('POST');
$body = read_json_body();
$token = trim((string) ($body['refresh_token'] ?? bearer_token() ?? ''));

if ($token === '') {
    errorResponse('Refresh token is required', 400);
}

try {
    $db = get_db();
    $refreshTokens = new RefreshTokenService(
        $db,
        new AccessTokenService($db),
        new RefreshTokenRepository($db),
        (int) ($_ENV['JWT_REFRESH_TTL'] ?? 2592000)
    );
    $pair = $refreshTokens->rotate(
        $token,
        isset($body['device_id']) ? trim((string) $body['device_id']) : null,
        $_SERVER['HTTP_USER_AGENT'] ?? 'API Client',
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    );

    jsonResponse(true, [
        'access_token' => $pair->accessToken,
        'refresh_token' => $pair->refreshToken,
        'token_type' => $pair->tokenType,
        'expires_in' => $pair->expiresIn,
    ], 'Token refreshed');
} catch (\RuntimeException $e) {
    $status = str_contains($e->getMessage(), 'reuse detected') ? 409 : 401;
    errorResponse($e->getMessage(), $status);
}
