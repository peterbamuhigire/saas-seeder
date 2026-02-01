<?php
/**
 * Franchise Admin Dashboard
 *
 * This is the main dashboard for franchise owners and staff.
 * Examples:
 * - School SaaS: School principal's dashboard
 * - Restaurant SaaS: Restaurant manager's dashboard
 * - Medical SaaS: Clinic manager's dashboard
 */

require_once __DIR__ . '/../src/config/auth.php';

// Require authentication
requireAuth();

// Only franchise admins (owner/staff) should access this
// Super admins can access but may need to select a franchise context
$userType = getSession('user_type', '');

if ($userType !== 'super_admin' && $userType !== 'owner' && $userType !== 'staff') {
    // End users should use memberpanel
    header('Location: ./memberpanel/');
    exit();
}

// Set page configuration
$pageTitle = 'Franchise Dashboard';
$panel = 'admin'; // Use admin menu for franchise owners/staff

// Get franchise information
$franchiseName = getSession('franchise_name', 'Your Franchise');
$userName = getSession('full_name', 'User');
$userRole = getSession('role_name', 'Administrator');
?>
<!doctype html>
<html lang="en">
<head>
   <?php include __DIR__ . "/includes/head.php"; ?>
</head>
<body>
    <script src="/assets/tabler/js/tabler.min.js"></script>

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
                                <?php echo htmlspecialchars($franchiseName); ?>
                            </div>
                            <h2 class="page-title">
                                Dashboard
                            </h2>
                        </div>
                        <div class="col-auto ms-auto d-print-none">
                            <div class="btn-list">
                                <!-- Add your action buttons here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page body -->
            <div class="page-body">
                <div class="container-xl">

                    <!-- Welcome Card -->
                    <div class="row row-deck row-cards mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3>Welcome, <?php echo htmlspecialchars($userName); ?>!</h3>
                                    <p class="text-muted">You're logged in as <strong><?php echo htmlspecialchars($userRole); ?></strong></p>

                                    <?php if ($userType === 'super_admin'): ?>
                                    <div class="alert alert-info">
                                        <strong>Super Admin Mode:</strong> You're viewing the franchise admin interface.
                                        Return to <a href="./adminpanel/">System Admin Panel</a> to manage all franchises.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Row -->
                    <div class="row row-deck row-cards">
                        <div class="col-sm-6 col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="subheader">Total Users</div>
                                    </div>
                                    <div class="h1 mb-3">0</div>
                                    <div class="d-flex mb-2">
                                        <div>Start adding your users/members</div>
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
                                        <div>No activity yet</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="subheader">This Month</div>
                                    </div>
                                    <div class="h1 mb-3">0</div>
                                    <div class="d-flex mb-2">
                                        <div>Monthly summary</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="subheader">Status</div>
                                    </div>
                                    <div class="h1 mb-3">
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                    <div class="d-flex mb-2">
                                        <div>System running normally</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row row-deck row-cards mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Quick Actions</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <a href="#" class="btn btn-outline-primary w-100">
                                                Add New User
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="#" class="btn btn-outline-primary w-100">
                                                View Reports
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="#" class="btn btn-outline-primary w-100">
                                                Settings
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Getting Started -->
                    <div class="row row-deck row-cards mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Getting Started</h3>
                                </div>
                                <div class="card-body">
                                    <p>This is the <strong>Franchise Admin Dashboard</strong> (public/ root).</p>
                                    <ul>
                                        <li><strong>/public/</strong> - Franchise admin pages (you are here)</li>
                                        <li><strong>/memberpanel/</strong> - End user portal (students/customers)</li>
                                        <li><strong>/adminpanel/</strong> - Super admin system (multiple franchises)</li>
                                    </ul>
                                    <p class="mt-3">Start building your franchise management features here!</p>
                                    <p class="text-muted">See <code>docs/PANEL-STRUCTURE.md</code> for detailed documentation.</p>
                                </div>
                            </div>
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
