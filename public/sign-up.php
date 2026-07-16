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
$pageTitle = 'Account registration';
$pageEyebrow = 'Capability status';
$pageHeading = 'Account registration.';
$pageDescription = 'Workspace access is invitation-controlled in this installation.';
$storyEyebrow = 'Tenant-aware onboarding';
$storyHeading = 'Invite with intent.';
$storyDescription = 'Registration policy belongs to the product: approval, tenancy, billing, and verification should be designed together.';

require __DIR__ . '/includes/auth-page-start.php';
?>
<div class="capability-card" role="status">
    <span class="capability-card__status">Invitation only</span>
    <strong>Public self-registration is disabled.</strong>
    <p>Ask a workspace administrator for an invitation. Product teams can enable registration after defining tenant assignment, verification, and abuse controls.</p>
</div>
<a class="button button--full" href="/sign-in.php">Return to sign in</a>
<?php require __DIR__ . '/includes/auth-page-end.php'; ?>
