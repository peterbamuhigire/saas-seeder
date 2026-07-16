<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/config/autoloader.php';

use App\Http\Security\EnvironmentGuard;

$appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'development';
EnvironmentGuard::denyUnlessLocalDevelopment($appEnv, $_SERVER['REMOTE_ADDR'] ?? '');

require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/includes/security-headers.php';

$checks = [
    'Session active' => session_status() === PHP_SESSION_ACTIVE,
    'Authenticated' => isLoggedIn(),
    'User identifier present' => hasSession('user_id'),
    'Franchise identifier present' => hasSession('franchise_id'),
    'Last activity present' => hasSession('last_activity'),
    'HttpOnly cookie' => ini_get('session.cookie_httponly') === '1',
    'SameSite cookie configured' => ini_get('session.cookie_samesite') !== '',
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Local session diagnostics — SaaS Seeder</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body class="diagnostic-page">
    <main class="diagnostic-card">
        <p class="eyebrow">Local development only</p>
        <h1>Session diagnostics</h1>
        <p class="lede">This page reports configuration state only. It never displays session identifiers, values, or server metadata.</p>
        <dl class="diagnostic-list">
            <?php foreach ($checks as $label => $passed): ?>
                <div>
                    <dt><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></dt>
                    <dd class="<?= $passed ? 'status-pass' : 'status-warn' ?>"><?= $passed ? 'Pass' : 'Review' ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
        <a class="text-link" href="/sign-in.php">Return to sign in</a>
    </main>
</body>
</html>
