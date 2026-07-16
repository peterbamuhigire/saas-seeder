<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/config/autoloader.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/includes/security-headers.php';

use App\Helpers\UiHelper;

if (isLoggedIn()) {
    header('Location: ./index.php');
    exit;
}

$loginBackground = UiHelper::getRandomLoginBackground();
$pageTitle = 'Password recovery';
$pageEyebrow = 'Capability status';
$pageHeading = 'Password recovery.';
$pageDescription = 'This starter does not pretend an email integration exists when it has not been configured.';
$storyEyebrow = 'Honest capability boundaries';
$storyHeading = 'No dead-end forms.';
$storyDescription = 'Optional product integrations are clearly gated so teams can implement them against their own delivery and identity requirements.';

require __DIR__ . '/includes/auth-page-start.php';
?>
<div class="capability-card" role="status">
    <span class="capability-card__status">Not configured</span>
    <strong>Email password recovery is disabled.</strong>
    <p>Contact your workspace administrator for a secure password reset. Enable this route only after a reset-token service and email provider are configured.</p>
</div>
<a class="button button--full" href="/sign-in.php">Return to sign in</a>
<?php require __DIR__ . '/includes/auth-page-end.php'; ?>
