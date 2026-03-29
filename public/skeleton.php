<?php
require_once __DIR__ . '/../src/config/auth.php';

// Check authentication
requireAuth();

// Optional: Check permissions if needed
// requirePermissionGlobal('PERMISSION_CODE');

// Set page configuration
$pageTitle = 'Skeleton Page';
$panel = 'admin'; // Change to 'member' for member panel pages
?>
<!doctype html>
<html lang="en">
<head>
   <?php include __DIR__ . "/includes/head.php"; ?>
</head>
<body>
  <div class="page">
    <!-- Navbar -->
    <div class="sticky-top">
      <?php include __DIR__ . "/includes/topbar.php"; ?>
    </div>

    <div class="page-wrapper">
      <!-- Page header -->
      <div class="page-header d-print-none">
        <div class="container-xl">
          <div class="row g-2 align-items-center">
            <div class="col">
              <div class="page-pretitle">
                SaaS Seeder Template
              </div>
              <h2 class="page-title">
                Skeleton Page
              </h2>
            </div>
            <!-- Page title actions -->
            <div class="col-auto ms-auto d-print-none">
              <div class="btn-list">
                <!-- Add your action buttons here -->
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Page body -->
      <div class="page-body" id="main-body">
        <div class="container-xl">
          <div class="card">
            <div class="card-body" id="main-content">
              Put your content here. Clone this file to create new pages.
            </div>
          </div>
        </div>
      </div>
      <footer class="footer footer-transparent d-print-none">
        <?php include __DIR__ . '/includes/footer.php'; ?>
      </footer>
    </div>
  </div>
  <?php include __DIR__ . "/includes/foot.php"; ?>
</body>
</html>
