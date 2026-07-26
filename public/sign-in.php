<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/config/autoloader.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/includes/security-headers.php';

use App\Auth\DTO\LoginDTO;
use App\Auth\Helpers\CookieHelper;
use App\Auth\Helpers\CSRFHelper;
use App\Auth\Helpers\PasswordHelper;
use App\Auth\Security\DemoAccessConfig;
use App\Auth\Services\AuthService;
use App\Auth\Services\PermissionService;
use App\Auth\Services\TokenService;
use App\Config\Database;
use App\Helpers\UiHelper;

$csrfHelper = new CSRFHelper();
$csrfToken = $csrfHelper->generateToken();
$loginBackground = UiHelper::getRandomLoginBackground();
$error = '';
$success = '';
$demoAccess = DemoAccessConfig::fromEnvironment();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    requireGuest();
}

$message = $_GET['msg'] ?? '';
if ($message === 'session_expired') {
    $error = 'Your session expired. Sign in again to continue.';
} elseif ($message === 'logout') {
    $success = 'You have been signed out.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $csrfHelper->validateToken($_POST['csrf_token'] ?? '');

        $isDemoLogin = ($_POST['login_mode'] ?? '') === 'demo';
        if ($isDemoLogin && !$demoAccess->isAvailable()) {
            throw new RuntimeException('Demo access is unavailable.');
        }

        $username = $isDemoLogin
            ? $demoAccess->username()
            : trim((string) ($_POST['username'] ?? ''));
        $password = $isDemoLogin
            ? $demoAccess->password()
            : (string) ($_POST['password'] ?? '');
        $rememberMe = !$isDemoLogin && isset($_POST['remember']);

        if ($username === '' || $password === '') {
            throw new RuntimeException('Credentials are required.');
        }

        $db = Database::getInstance()->getConnection();
        if ($isDemoLogin) {
            $statement = $db->prepare(
                "SELECT 1 FROM tbl_users
                 WHERE (username = :username OR email = :email)
                   AND user_type = 'super_admin'
                 LIMIT 1"
            );
            $statement->execute([
                'username' => $username,
                'email' => $username,
            ]);

            if ($statement->fetchColumn() === false) {
                throw new RuntimeException('The demo account must be a super administrator.');
            }
        }

        $authService = new AuthService(
            $db,
            new TokenService($db),
            new PermissionService($db),
            new PasswordHelper(),
            new CookieHelper()
        );

        $result = $authService->authenticate(new LoginDTO(
            $username,
            $password,
            (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown')
        ));

        if ($result->getStatus() !== 'SUCCESS') {
            $error = 'The username/email or password is incorrect.';
        } else {
            regenerateSession();

            if ($rememberMe) {
                try {
                    $session = $authService->createUserSession($result->getUserId(), true);
                    (new CookieHelper())->createSecureCookie('remember_token', $session->getToken(), 86400 * 30);
                } catch (Throwable $exception) {
                    error_log('Remember-me setup failed: ' . $exception->getMessage());
                }
            }

            $destination = (!$isDemoLogin && (int) (getSession('force_password_change') ?? 0) === 1)
                ? './change-password.php'
                : './index.php';
            header('Location: ' . $destination);
            exit;
        }
    } catch (Throwable $exception) {
        $error = 'Sign in could not be completed. Check your details and try again.';
        error_log('Login error: ' . $exception->getMessage());
    }
}

$pageTitle = 'Sign in';
$pageEyebrow = 'Secure workspace';
$pageHeading = 'Welcome back.';
$pageDescription = 'Use your workspace credentials to continue.';
$storyEyebrow = 'Authentication without the reinvention';
$storyHeading = 'Start secure. Ship sooner.';
$storyDescription = 'A readable PHP foundation for account security, multi-tenant roles, and API token lifecycle.';

require __DIR__ . '/includes/auth-page-start.php';
?>
<?php if ($error !== ''): ?>
    <div class="alert alert--error" role="alert" aria-live="polite"><?= authEscape($error) ?></div>
<?php endif; ?>
<?php if ($success !== ''): ?>
    <div class="alert alert--success" role="status" aria-live="polite"><?= authEscape($success) ?></div>
<?php endif; ?>

<form action="/sign-in.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= authEscape($csrfToken) ?>">

    <div class="field">
        <label for="username">Username or email</label>
        <div class="field__control">
            <input id="username" name="username" type="text" autocomplete="username" required value="<?= authEscape((string) ($_POST['username'] ?? '')) ?>">
        </div>
    </div>

    <div class="field">
        <label for="password">Password</label>
        <div class="field__control">
            <input id="password" name="password" type="password" autocomplete="current-password" required data-password-input>
            <button class="password-toggle" type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false">Show</button>
        </div>
    </div>

    <div class="form-row">
        <label class="check-label" for="remember">
            <input id="remember" name="remember" type="checkbox" value="1">
            <span>Keep me signed in</span>
        </label>
        <a class="text-link" href="/forgot-password.php">Forgot password?</a>
    </div>

    <button class="button button--full" type="submit">Sign in securely</button>
</form>

<?php if ($demoAccess->isAvailable()): ?>
    <div class="auth-divider" aria-hidden="true"><span>or</span></div>
    <form action="/sign-in.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= authEscape($csrfToken) ?>">
        <input type="hidden" name="login_mode" value="demo">
        <button class="button button--secondary button--full" type="submit">Explore the super-admin demo</button>
    </form>
<?php endif; ?>

<div class="auth-actions">
    <a class="text-link" href="/sign-up.php">Need an account?</a>
</div>
<?php require __DIR__ . '/includes/auth-page-end.php'; ?>
