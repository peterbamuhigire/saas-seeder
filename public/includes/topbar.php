<?php
$panel = $panel ?? 'admin';
$menuFile = $panel === 'admin'
  ? __DIR__ . '/menus/admin.php'
  : __DIR__ . '/menus/member.php';

// App name — consistent across all pages (override in your project)
$appName = 'SaaS Seeder';

// Current page for active state detection
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<header class="navbar navbar-expand-md d-print-none">
  <a href="#main-body" class="visually-hidden-focusable position-absolute" style="z-index:9999">Skip to main content</a>
  <div class="container-xl">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3" href="/">
      <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?>
    </a>
    <div class="collapse navbar-collapse" id="navbar-menu">
      <div class="navbar-nav flex-row">
        <?php if (file_exists($menuFile)) { include $menuFile; } ?>
      </div>
    </div>
    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
    <div class="navbar-nav flex-row order-md-last">
      <div class="nav-item dropdown">
        <button type="button" class="nav-link d-flex lh-1 text-reset p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-label="Account menu">
          <span class="avatar avatar-sm bg-primary-lt">
            <?php echo strtoupper(substr(getSession('full_name') ?: getSession('username') ?: '?', 0, 1)); ?>
          </span>
          <div class="d-none d-xl-block ps-2">
            <div style="font-size:.85rem;font-weight:600;"><?php echo htmlspecialchars(getSession('full_name') ?: getSession('username') ?: 'User'); ?></div>
            <div class="mt-1 small text-secondary"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', getSession('user_type') ?: 'user'))); ?></div>
          </div>
        </button>
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
          <a href="/change-password.php" class="dropdown-item">Change password</a>
          <div class="dropdown-divider"></div>
          <a href="/logout.php" class="dropdown-item">Sign out</a>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</header>
