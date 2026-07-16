<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/config/autoloader.php';
require_once __DIR__ . '/../src/config/session.php';

use App\Auth\Helpers\CSRFHelper;
use App\Auth\Services\UserService;
use App\Config\Database;
use App\Helpers\UiHelper;
use App\Http\Security\EnvironmentGuard;

$appEnv = (string) ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'development');
EnvironmentGuard::denyUnlessLocalDevelopment($appEnv, (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
require_once __DIR__ . '/includes/security-headers.php';

initSession();

$csrfHelper = new CSRFHelper();
$csrfToken = $csrfHelper->generateToken();
$loginBackground = UiHelper::getRandomLoginBackground();
$error = '';
$createdUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $csrfHelper->validateToken($_POST['csrf_token'] ?? '');

        $password = (string) ($_POST['password'] ?? '');
        if ($password !== (string) ($_POST['confirm_password'] ?? '')) {
            throw new InvalidArgumentException('Passwords do not match.');
        }

        $newUser = (new UserService(Database::getInstance()->getConnection()))->createUser([
            'username' => trim((string) ($_POST['username'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'password' => $password,
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'user_type' => 'super_admin',
            'franchise_id' => null,
            'force_password_change' => 1,
        ]);

        $createdUsername = $newUser['username'];
    } catch (InvalidArgumentException | RuntimeException $exception) {
        $error = $exception->getMessage();
    } catch (Throwable $exception) {
        $error = 'The account could not be created. Check the application log.';
        error_log('Super user creation failed: ' . $exception->getMessage());
    }
}

$pageTitle = 'Bootstrap super administrator';
$pageEyebrow = 'Local development tool';
$pageHeading = $createdUsername !== '' ? 'Administrator created.' : 'Create the first administrator.';
$pageDescription = 'This route is available only from a loopback address in a development environment.';
$storyEyebrow = 'One-time bootstrap';
$storyHeading = 'Establish the root of trust.';
$storyDescription = 'Create the initial administrator, sign in, and then remove or disable this scaffolding route.';

require __DIR__ . '/includes/auth-page-start.php';
?>
<?php if ($createdUsername !== ''): ?>
    <div class="alert alert--success" role="status">Account <strong><?= authEscape($createdUsername) ?></strong> was created and must change its password at first sign-in.</div>
    <a class="button button--full" href="/sign-in.php">Sign in</a>
<?php else: ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert--error" role="alert" aria-live="polite"><?= authEscape($error) ?></div>
    <?php endif; ?>
    <form action="/super-user-dev.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= authEscape($csrfToken) ?>">

        <div class="field">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" autocomplete="username" required value="<?= authEscape((string) ($_POST['username'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required value="<?= authEscape((string) ($_POST['email'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="first_name">First name</label>
            <input id="first_name" name="first_name" type="text" autocomplete="given-name" required value="<?= authEscape((string) ($_POST['first_name'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" type="text" autocomplete="family-name" required value="<?= authEscape((string) ($_POST['last_name'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="password">Temporary password</label>
            <div class="field__control">
                <input id="password" name="password" type="password" autocomplete="new-password" required data-password-input data-password-strength-input>
                <button class="password-toggle" type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false">Show</button>
            </div>
        </div>
        <div class="password-strength" data-password-strength data-score="0" hidden>
            <div class="password-strength__track" aria-hidden="true"><div class="password-strength__bar"></div></div>
            <span class="password-strength__text" data-password-strength-text aria-live="polite"></span>
        </div>
        <div class="field">
            <label for="confirm_password">Confirm temporary password</label>
            <div class="field__control">
                <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required data-password-input>
                <button class="password-toggle" type="button" data-password-toggle="confirm_password" aria-controls="confirm_password" aria-pressed="false">Show</button>
            </div>
        </div>

        <button class="button button--full" type="submit">Create administrator</button>
    </form>
<?php endif; ?>
<?php require __DIR__ . '/includes/auth-page-end.php'; ?>
