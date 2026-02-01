<?php
require_once __DIR__ . '/../../src/config/auth.php';

// Require authentication
requireAuth();

$panel = 'admin';
$pageTitle = 'Admin Panel';
?>
<!doctype html>
<html lang="en">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
  <div class="page">
    <div class="sticky-top">
      <?php include __DIR__ . '/../includes/topbar.php'; ?>
    </div>
    <div class="page-wrapper">
      <div class="page-header d-print-none">
        <div class="container-xl">
          <h2 class="page-title">Admin Dashboard</h2>
        </div>
      </div>
      <div class="page-body">
        <div class="container-xl">
          <div class="card">
            <div class="card-body">Admin panel placeholder.</div>
          </div>
        </div>
      </div>
      <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
  </div>
  <?php include __DIR__ . '/../includes/foot.php'; ?>
</body>
</html>
