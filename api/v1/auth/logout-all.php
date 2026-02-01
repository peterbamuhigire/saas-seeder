<?php
declare(strict_types=1);

/**
 * POST /auth/logout-all
 * Revokes all refresh tokens for the user (or a specific device_id when provided).
 */

use App\Http\Auth\RefreshTokenStore;

require_once __DIR__ . '/../middleware.php';

require_method('POST');
$auth = require_auth();

$body = read_json_body();
$deviceId = isset($body['device_id']) ? trim((string)$body['device_id']) : null;

$store = new RefreshTokenStore(get_db());
if ($deviceId !== null && $deviceId !== '') {
    $store->revokeAllForUserDevice($auth['user_id'], $deviceId);
    $msg = 'Logged out for device';
} else {
    $store->revokeAllForUser($auth['user_id']);
    $msg = 'Logged out from all devices';
}

json_response(200, [
    'success' => true,
    'message' => $msg,
]);
