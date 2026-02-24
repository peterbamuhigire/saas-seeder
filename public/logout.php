<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';

use App\Config\Database;
use App\Auth\Services\{AuthService, TokenService, PermissionService};
use App\Auth\Helpers\{CookieHelper, PasswordHelper};
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$dotenv->required(['COOKIE_DOMAIN', 'COOKIE_ENCRYPTION_KEY', 'APP_ENV'])->notEmpty();

try {
    // Use getSession() — never raw $_SESSION
    $token = getSession('auth_token');

    if ($token) {
        $db = (new Database())->getConnection();
        $authService = new AuthService(
            $db,
            new TokenService($db),
            new PermissionService($db),
            new PasswordHelper(),
            new CookieHelper()
        );

        $authService->logout($token);
    }

    // Clear all prefixed session variables, then destroy
    clearPrefixedSession();
    session_destroy();

    header('Location: ./sign-in.php?msg=logout');
    exit();

} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    clearPrefixedSession();
    session_destroy();
    header('Location: ./sign-in.php?msg=logout');
    exit();
}
