<?php
require_once __DIR__ . '/../../src/config/auth.php';

// Require authentication
requireAuth();

$panel = 'admin';
$pageTitle = 'System Admin';
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
    <main id="main-body" class="page-wrapper" tabindex="-1">
      <div class="page-header d-print-none">
        <div class="container-xl">
          <div class="row g-2 align-items-center">
            <div class="col">
              <div class="page-pretitle">Super Admin</div>
              <h2 class="page-title">System Dashboard</h2>
            </div>
            <div class="col-auto">
              <a href="../dashboard.php" class="btn btn-outline-primary">
                Go to Franchise Dashboard
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="page-body">
        <div class="container-xl">

          <!-- Getting Started empty state -->
          <div class="card">
            <div class="card-body">
              <div class="empty">
                <p class="empty-title">System admin panel is ready</p>
                <p class="empty-subtitle text-secondary">
                  This is the <strong>Super Admin Panel</strong> for managing all franchises, system users,
                  and global settings. Build your system management pages here.
                </p>
                <div class="empty-action">
                  <a href="../dashboard.php" class="btn btn-primary">Go to Franchise Dashboard</a>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      <footer class="footer footer-transparent d-print-none">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
      </footer>
    </main>
  </div>
  <?php include __DIR__ . '/../includes/foot.php'; ?>
</body>
</html>
