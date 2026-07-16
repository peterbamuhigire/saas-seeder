<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/config/autoloader.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/includes/security-headers.php';

$reason = (string) ($_GET['reason'] ?? 'permission_denied');
$permission = trim((string) ($_GET['permission'] ?? ''));
$function = trim((string) ($_GET['function'] ?? ''));

$messages = [
    'permission_denied' => ['Access not granted', 'Your account does not have the permission required for this action.'],
    'encoding_period_not_set' => ['Configuration required', 'The encoding period has not been configured for this workspace.'],
    'invalid_franchise' => ['Workspace unavailable', 'The requested workspace does not exist or is outside your access boundary.'],
    'forbidden' => ['Action not authorized', 'Your current role does not authorize this action.'],
];
[$title, $description] = $messages[$reason] ?? $messages['permission_denied'];

$userType = (string) (getSession('user_type') ?? '');
$dashboardUrl = match ($userType) {
    'super_admin' => '/adminpanel/',
    'owner', 'staff' => '/dashboard.php',
    default => '/memberpanel/',
};

http_response_code(403);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#08131f">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> — SaaS Seeder</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body class="denied-page">
    <main class="denied-card">
        <p class="denied-card__code">HTTP 403 · ACCESS BOUNDARY</p>
        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="lede"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>

        <?php if ($permission !== '' || $function !== ''): ?>
            <dl class="denied-details">
                <?php if ($function !== ''): ?>
                    <div><dt>Requested function</dt><dd><?= htmlspecialchars($function, ENT_QUOTES, 'UTF-8') ?></dd></div>
                <?php endif; ?>
                <?php if ($permission !== ''): ?>
                    <div><dt>Required permission</dt><dd><?= htmlspecialchars($permission, ENT_QUOTES, 'UTF-8') ?></dd></div>
                <?php endif; ?>
            </dl>
        <?php endif; ?>

        <a class="button" href="<?= htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') ?>">Return to workspace</a>
        <p class="lede">If you need this access, ask a workspace administrator to review your role.</p>
    </main>
</body>
</html>
