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

// Validate required environment variables
$dotenv->required(['COOKIE_DOMAIN', 'COOKIE_ENCRYPTION_KEY', 'APP_ENV'])->notEmpty();

try {
    // Get current token
    $token = $_SESSION['auth_token'] ?? null;

    if ($token) {
        $db = (new Database())->getConnection();
        $authService = new AuthService(
            $db,
            new TokenService($db),
            new PermissionService($db),
            new PasswordHelper(),
            new CookieHelper()
        );

        // Invalidate token and remove cookie
        $authService->logout($token);
    }

    // Clear session
    session_unset();
    session_destroy();

    // Redirect with success message
    header('Location: ./sign-in.php?logout=success');
    exit();

} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    header('Location: ./sign-in.php?error=logout_failed');
    exit();
}
