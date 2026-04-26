<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/config/auth.php';

requireAuth();

$pageTitle = 'Module unavailable';
$module = htmlspecialchars((string) ($_GET['module'] ?? 'MODULE'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
    <?php require __DIR__ . '/includes/head.php'; ?>
</head>
<body>
<div class="page">
    <?php require __DIR__ . '/includes/topbar.php'; ?>
    <main id="main-body" class="page-wrapper" tabindex="-1">
        <div class="page-body">
            <div class="container-xl">
                <div class="empty">
                    <div class="empty-icon">
                        <span class="avatar avatar-xl bg-warning-lt">!</span>
                    </div>
                    <p class="empty-title">Module unavailable</p>
                    <p class="empty-subtitle text-secondary">
                        The <?php echo $module; ?> module is not enabled for this tenant.
                    </p>
                    <div class="empty-action">
                        <a href="/dashboard.php" class="btn btn-primary">Back to dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</div>
<?php require __DIR__ . '/includes/foot.php'; ?>
</body>
</html>
