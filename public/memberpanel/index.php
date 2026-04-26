<?php
require_once __DIR__ . '/../../src/config/auth.php';

// Require authentication
requireAuth();

$panel = 'member';
$pageTitle = 'My Dashboard';
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
          <h2 class="page-title">My Dashboard</h2>
        </div>
      </div>
      <div class="page-body">
        <div class="container-xl">

          <div class="card">
            <div class="card-body">
              <div class="empty">
                <p class="empty-title">Welcome to your dashboard</p>
                <p class="empty-subtitle text-secondary">
                  This is the <strong>Member Portal</strong> where end users (students, customers, patients, etc.)
                  can access their data and self-service features. Build your member-facing pages here.
                </p>
                <div class="empty-action">
                  <a href="/change-password.php" class="btn btn-primary">Update your password</a>
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
