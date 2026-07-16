<?php

declare(strict_types=1);

if (!function_exists('authEscape')) {
    function authEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

$pageTitle = $pageTitle ?? 'Account';
$pageEyebrow = $pageEyebrow ?? 'Secure access';
$pageHeading = $pageHeading ?? 'Welcome';
$pageDescription = $pageDescription ?? '';
$storyEyebrow = $storyEyebrow ?? 'Production-ready foundation';
$storyHeading = $storyHeading ?? 'Secure access. Clear boundaries.';
$storyDescription = $storyDescription ?? 'Authentication, tenant-aware roles, and audit-ready controls in one readable starter.';
$loginBackground = $loginBackground ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#08131f">
    <title><?= authEscape($pageTitle) ?> — SaaS Seeder</title>
    <link rel="preload" href="/assets/fonts/bricolage-grotesque/bricolage-grotesque-variable.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
<a class="skip-link" href="#main-content">Skip to main content</a>
<div class="auth-shell">
    <aside class="auth-story" aria-label="About SaaS Seeder">
        <?php if ($loginBackground !== ''): ?>
            <img class="auth-story__image" src="<?= authEscape($loginBackground) ?>" alt="" aria-hidden="true">
        <?php endif; ?>
        <div class="auth-story__wash" aria-hidden="true"></div>
        <a class="brand" href="/" aria-label="SaaS Seeder home">
            <span class="brand__mark" aria-hidden="true">S</span>
            <span>SaaS Seeder</span>
        </a>
        <div class="auth-story__copy">
            <p class="eyebrow"><?= authEscape($storyEyebrow) ?></p>
            <h2><?= authEscape($storyHeading) ?></h2>
            <p class="auth-story__intro"><?= authEscape($storyDescription) ?></p>
            <ul class="proof-list" aria-label="Platform capabilities">
                <li>Argon2id credentials</li>
                <li>Tenant-aware RBAC</li>
                <li>Rotating refresh tokens</li>
            </ul>
        </div>
        <small class="auth-story__footer">© <?= date('Y') ?> Chwezi Core Systems</small>
    </aside>
    <main class="auth-main" id="main-content">
        <section class="auth-panel" aria-labelledby="page-heading">
            <a class="brand brand--mobile" href="/" aria-label="SaaS Seeder home">
                <span class="brand__mark" aria-hidden="true">S</span>
                <span>SaaS Seeder</span>
            </a>
            <header class="auth-header">
                <p class="eyebrow"><?= authEscape($pageEyebrow) ?></p>
                <h1 id="page-heading"><?= authEscape($pageHeading) ?></h1>
                <?php if ($pageDescription !== ''): ?>
                    <p class="auth-header__description"><?= authEscape($pageDescription) ?></p>
                <?php endif; ?>
            </header>
