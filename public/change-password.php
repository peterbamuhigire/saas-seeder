<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/config/autoloader.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/includes/security-headers.php';

use App\Auth\Helpers\CSRFHelper;
use App\Auth\Helpers\PasswordHelper;
use App\Auth\Services\AuditService;
use App\Config\Database;
use App\Helpers\UiHelper;
use App\Observability\AuditEvent;

requireAuth();

$csrfHelper = new CSRFHelper();
$csrfToken = $csrfHelper->generateToken();
$loginBackground = UiHelper::getRandomLoginBackground();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $csrfHelper->validateToken($_POST['csrf_token'] ?? '');

        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');

        if ($currentPassword === '' || $newPassword === '') {
            $error = 'Enter both your current password and a new password.';
        } else {
            $passwordHelper = new PasswordHelper();
            $validationErrors = $passwordHelper->validatePasswordStrength($newPassword);

            if ($validationErrors !== []) {
                $error = implode(' ', $validationErrors);
            } else {
                $db = Database::getInstance()->getConnection();
                $userId = (int) getSession('user_id');
                $statement = $db->prepare('SELECT password_hash FROM tbl_users WHERE id = ? LIMIT 1');
                $statement->execute([$userId]);
                $row = $statement->fetch(PDO::FETCH_ASSOC);

                if (!$row || !$passwordHelper->verifyPassword($currentPassword, (string) $row['password_hash'])) {
                    $error = 'The current password is incorrect.';
                } else {
                    $statement = $db->prepare('UPDATE tbl_users SET password_hash = ?, force_password_change = 0, updated_at = NOW() WHERE id = ?');
                    $statement->execute([$passwordHelper->hashPassword($newPassword), $userId]);

                    (new AuditService($db))->log(
                        AuditEvent::AUTH_PASSWORD_CHANGED,
                        $userId,
                        (int) (getSession('franchise_id') ?? 0) ?: null,
                        'user',
                        $userId,
                        ['force_password_change_cleared' => true]
                    );

                    setSession('force_password_change', 0);
                    $success = 'Password changed. You can continue to your workspace.';
                }
            }
        }
    } catch (Throwable $exception) {
        $error = 'The password could not be changed. Try again.';
        error_log('Password change failed: ' . $exception->getMessage());
    }
}

$pageTitle = 'Change password';
$pageEyebrow = 'Account security';
$pageHeading = 'Choose a new password.';
$pageDescription = 'Use at least 12 characters and combine uppercase, lowercase, numbers, and symbols.';
$storyEyebrow = 'Credential hygiene';
$storyHeading = 'Make the next secret stronger.';
$storyDescription = 'Passwords are validated at the edge, hashed with Argon2id, and never written to application logs.';

require __DIR__ . '/includes/auth-page-start.php';
?>
<?php if ($error !== ''): ?>
    <div class="alert alert--error" role="alert" aria-live="polite"><?= authEscape($error) ?></div>
<?php endif; ?>
<?php if ($success !== ''): ?>
    <div class="alert alert--success" role="status" aria-live="polite"><?= authEscape($success) ?></div>
    <a class="button button--full" href="/index.php">Continue to workspace</a>
<?php else: ?>
    <form action="/change-password.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= authEscape($csrfToken) ?>">

        <div class="field">
            <label for="current_password">Current password</label>
            <div class="field__control">
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" required data-password-input>
                <button class="password-toggle" type="button" data-password-toggle="current_password" aria-controls="current_password" aria-pressed="false">Show</button>
            </div>
        </div>

        <div class="field">
            <label for="new_password">New password</label>
            <div class="field__control">
                <input id="new_password" name="new_password" type="password" autocomplete="new-password" required data-password-input data-password-strength-input>
                <button class="password-toggle" type="button" data-password-toggle="new_password" aria-controls="new_password" aria-pressed="false">Show</button>
            </div>
        </div>

        <div class="password-strength" data-password-strength data-score="0" hidden>
            <div class="password-strength__track" aria-hidden="true"><div class="password-strength__bar"></div></div>
            <span class="password-strength__text" data-password-strength-text aria-live="polite"></span>
        </div>

        <button class="button button--full" type="submit">Change password</button>
    </form>
    <?php if ((int) (getSession('force_password_change') ?? 0) !== 1): ?>
        <div class="auth-actions"><a class="text-link" href="/index.php">Cancel</a></div>
    <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/includes/auth-page-end.php'; ?>
