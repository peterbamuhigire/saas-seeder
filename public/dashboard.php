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
$pageTitle = 'Dashboard';
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

                    <!-- Getting Started -->
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Getting Started</h3>
                                </div>
                                <div class="card-body">
                                    <div class="empty">
                                        <p class="empty-title">Your franchise dashboard is ready</p>
                                        <p class="empty-subtitle text-secondary">
                                            This is the <strong>Franchise Admin Dashboard</strong>. Start building your
                                            management features by cloning <code>skeleton.php</code> and adding pages here.
                                        </p>
                                        <div class="empty-action">
                                            <a href="./skeleton.php" class="btn btn-primary">View page template</a>
                                        </div>
                                    </div>
                                    <hr>
                                    <p class="mt-3"><strong>Three-tier architecture:</strong></p>
                                    <ul class="mb-0">
                                        <li><strong>/public/</strong> — Franchise admin pages (you are here)</li>
                                        <li><strong>/memberpanel/</strong> — End user portal (students/customers)</li>
                                        <li><strong>/adminpanel/</strong> — Super admin system (multiple franchises)</li>
                                    </ul>
                                    <p class="text-muted mt-2 mb-0">See <code>docs/PANEL-STRUCTURE.md</code> for detailed documentation.</p>
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
