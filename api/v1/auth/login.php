<?php
declare(strict_types=1);

/**
 * POST /auth/login
 * Authenticates user (email/username + password), issues access/refresh tokens, and records refresh jti for revocation.
 */

use App\Http\Auth\JwtService;
use App\Http\Auth\RefreshTokenStore;
use PDO;
use DateTime;

require_once __DIR__ . '/../../../bootstrap.php';

require_method('POST');
$body = read_json_body();

$usernameOrEmail = trim((string)($body['username'] ?? $body['email'] ?? ''));
$password = (string)($body['password'] ?? '');
$deviceId = trim((string)($body['device_id'] ?? ''));

if ($usernameOrEmail === '' || $password === '') {
    json_response(422, ['success' => false, 'message' => 'Username/email and password are required']);
}

$db = get_db();
// Prefer stored procedure when available (ensures consistent validation/business rules)
try {
    $stmt = $db->prepare('CALL sp_auth_login(:identifier)');
    $stmt->execute(['identifier' => $usernameOrEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    // Fallback to direct query if SP not present
    $stmt = $db->prepare('
        SELECT id, franchise_id, username, email, password_hash, user_type, is_active
        FROM tbl_users
        WHERE (email = :identifier OR username = :identifier)
        LIMIT 1
    ');
    $stmt->execute(['identifier' => $usernameOrEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user || !password_verify($password, $user['password_hash'] ?? '')) {
    json_response(401, ['success' => false, 'message' => 'Invalid credentials']);
}

if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
    json_response(403, ['success' => false, 'message' => 'User is inactive']);
}

$jwt = new JwtService();
$sessionJti = bin2hex(random_bytes(16));
$baseClaims = [
    'sub' => (int)$user['id'],
    'fid' => (int)$user['franchise_id'],
    'ut'  => $user['user_type'] ?? 'staff',
    'did' => $deviceId !== '' ? $deviceId : null,
    'jti' => $sessionJti,
];

$accessToken = $jwt->issueAccessToken(array_filter($baseClaims, fn($v) => $v !== null));
$refreshToken = $jwt->issueRefreshToken(array_filter($baseClaims, fn($v) => $v !== null));

// Persist refresh token metadata for revocation
$store = new RefreshTokenStore($db);
$store->store(
    userId: (int)$user['id'],
    franchiseId: (int)$user['franchise_id'],
    jti: $sessionJti,
    deviceId: $deviceId !== '' ? $deviceId : null,
    expiresAt: (new DateTime())->setTimestamp(time() + (int)($_ENV['JWT_REFRESH_TTL'] ?? 2592000))
);

json_response(200, [
    'success' => true,
    'data' => [
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'token_type' => 'Bearer',
        'expires_in' => (int)($_ENV['JWT_ACCESS_TTL'] ?? 900),
        'user' => [
            'id' => (int)$user['id'],
            'franchise_id' => (int)$user['franchise_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'user_type' => $user['user_type'] ?? null,
        ],
    ],
]);
