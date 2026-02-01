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

          <!-- Welcome Card with Quick Access -->
          <div class="row row-deck row-cards mb-3">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col">
                      <h3 class="mb-2">Super Admin Panel</h3>
                      <p class="text-muted mb-0">Manage the entire SaaS system, franchises, and global settings</p>
                    </div>
                    <div class="col-auto">
                      <a href="../dashboard.php" class="btn btn-primary btn-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                          <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                          <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                          <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                        </svg>
                        Go to Franchise Dashboard
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Stats Cards -->
          <div class="row row-deck row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="subheader">Total Franchises</div>
                  </div>
                  <div class="h1 mb-3">0</div>
                  <div class="d-flex mb-2">
                    <div>Registered franchises</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-sm-6 col-lg-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="subheader">Total Users</div>
                  </div>
                  <div class="h1 mb-3">0</div>
                  <div class="d-flex mb-2">
                    <div>Across all franchises</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-sm-6 col-lg-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="subheader">Active Today</div>
                  </div>
                  <div class="h1 mb-3">0</div>
                  <div class="d-flex mb-2">
                    <div>Active sessions</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-sm-6 col-lg-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="subheader">System Status</div>
                  </div>
                  <div class="h1 mb-3">
                    <span class="badge bg-success">Online</span>
                  </div>
                  <div class="d-flex mb-2">
                    <div>All systems operational</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="row row-deck row-cards">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <div class="d-grid">
                        <a href="#" class="btn btn-outline-primary btn-lg">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M3 21l18 0" />
                            <path d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l2 4" />
                            <path d="M5 21l0 -10.15" />
                            <path d="M19 21l0 -10.15" />
                            <path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" />
                          </svg>
                          Manage Franchises
                        </a>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="d-grid">
                        <a href="#" class="btn btn-outline-primary btn-lg">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                          </svg>
                          System Users
                        </a>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="d-grid">
                        <a href="#" class="btn btn-outline-primary btn-lg">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                            <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                          </svg>
                          System Settings
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
  </div>
  <?php include __DIR__ . '/../includes/foot.php'; ?>
</body>
</html>
