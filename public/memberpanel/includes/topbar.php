<?php
$panel = $panel ?? 'admin';
$menuFile = $panel === 'admin'
  ? __DIR__ . '/menus/admin.php'
  : __DIR__ . '/menus/member.php';
?>
<header class="navbar navbar-expand-md d-print-none">
  <div class="container-xl">
    <a class="navbar-brand" href="/"><?php echo htmlspecialchars($pageTitle ?? 'SaaS Template', ENT_QUOTES, 'UTF-8'); ?></a>
    <div class="navbar-nav">
      <?php if (file_exists($menuFile)) { include $menuFile; } ?>
    </div>
  </div>
</header>
