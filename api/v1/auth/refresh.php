<?php
declare(strict_types=1);

/**
 * POST /auth/refresh
 * Rotates access and refresh tokens; revokes old refresh jti and persists new one.
 */

use App\Http\Auth\JwtService;
use App\Http\Auth\RefreshTokenStore;
use DateTime;

require_once __DIR__ . '/../../../bootstrap.php';

require_method('POST');
$body = read_json_body();
$token = $body['refresh_token'] ?? bearer_token();

if (!$token) {
    json_response(400, ['success' => false, 'message' => 'Refresh token is required']);
}

$jwt = new JwtService();

try {
    $claims = $jwt->verify($token, 'refresh');
} catch (\Exception $e) {
    json_response(401, ['success' => false, 'message' => $e->getMessage()]);
}

$store = new RefreshTokenStore(get_db());

// Revoke old refresh token jti
if (!empty($claims['jti'])) {
    $store->revokeByJti((string)$claims['jti']);
}

// Issue new pair with fresh jti (device preserved if present)
$newJti = bin2hex(random_bytes(16));
$newClaims = $claims;
$newClaims['jti'] = $newJti;

$accessToken = $jwt->issueAccessToken($newClaims);
$refreshToken = $jwt->issueRefreshToken($newClaims);

// Persist new refresh token
$store->store(
    userId: (int)($claims['sub'] ?? 0),
    franchiseId: (int)($claims['fid'] ?? 0),
    jti: $newJti,
    deviceId: $claims['did'] ?? null,
    expiresAt: (new DateTime())->setTimestamp(time() + (int)($_ENV['JWT_REFRESH_TTL'] ?? 2592000))
);

json_response(200, [
    'success' => true,
    'data' => [
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'token_type' => 'Bearer',
        'expires_in' => (int)($_ENV['JWT_ACCESS_TTL'] ?? 900),
    ],
]);
