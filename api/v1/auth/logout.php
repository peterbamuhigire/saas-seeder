<?php
declare(strict_types=1);

/**
 * POST /auth/logout
 * Revokes the provided refresh token (device-level logout).
 */

use App\Http\Auth\JwtService;
use App\Http\Auth\RefreshTokenStore;

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
} catch (Exception $e) {
    json_response(401, ['success' => false, 'message' => $e->getMessage()]);
}

$store = new RefreshTokenStore(get_db());
if (!empty($claims['jti'])) {
    $store->revokeByJti((string)$claims['jti']);
}

json_response(200, [
    'success' => true,
    'message' => 'Logged out for this device',
]);
