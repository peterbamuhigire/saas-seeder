<?php
/**
 * Landing Page
 *
 * THREE-TIER PANEL STRUCTURE:
 * 1. /adminpanel/  - Super admin system (manage multiple franchises)
 * 2. /public/      - Franchise admin pages (manage franchise/school)
 * 3. /memberpanel/ - End user portal (students/customers/patients)
 */

require_once __DIR__ . '/../src/config/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Not logged in - redirect to sign-in page
    header('Location: ./sign-in.php');
    exit();
}

// Get user information
$userType = getSession('user_type', '');
$userName = getSession('full_name', getSession('username', 'User'));
$franchiseName = getSession('franchise_name', 'Your Franchise');
$roleDescription = '';

// Set page configuration
$pageTitle = 'Welcome';
$panel = 'admin';

// Determine role description
switch ($userType) {
    case 'super_admin':
        $roleDescription = 'System Administrator';
        break;
    case 'owner':
        $roleDescription = 'Franchise Owner';
        break;
    case 'staff':
        $roleDescription = 'Staff Member';
        break;
    default:
        $roleDescription = 'Member';
}
?>
<!doctype html>
<html lang="en">
<head>
   <?php include __DIR__ . "/includes/head.php"; ?>
   <style>
   .landing-hero {
       min-height: 60vh;
       display: flex;
       align-items: center;
       justify-content: center;
       background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
       color: white;
       text-align: center;
       padding: 3rem 0;
   }
   .landing-card {
       background: white;
       border-radius: 1rem;
       padding: 2rem;
       box-shadow: 0 10px 40px rgba(0,0,0,0.1);
       color: #1f2937;
       max-width: 600px;
       margin: 0 auto;
   }
   .big-button {
       padding: 1.5rem 3rem;
       font-size: 1.25rem;
       border-radius: 0.75rem;
       font-weight: 600;
       box-shadow: 0 4px 12px rgba(0,0,0,0.15);
       transition: all 0.3s ease;
   }
   .big-button:hover {
       transform: translateY(-2px);
       box-shadow: 0 8px 20px rgba(0,0,0,0.2);
   }
   .role-badge {
       font-size: 0.875rem;
       padding: 0.5rem 1rem;
       border-radius: 2rem;
       background: #f3f4f6;
       color: #6b7280;
       display: inline-block;
       margin-bottom: 1rem;
   }
   .super-admin-badge {
       background: linear-gradient(135deg, #f59e0b 0%, #dc2626 100%);
       color: white;
   }
   </style>
</head>
<body>
    <script src="/assets/tabler/js/tabler.min.js"></script>

    <div class="page">
        <!-- Navbar -->
        <div class="sticky-top">
            <?php include __DIR__ . "/includes/topbar.php"; ?>
        </div>

        <!-- Hero Section -->
        <div class="landing-hero">
            <div class="container-xl">
                <div class="landing-card">
                    <span class="role-badge <?php echo $userType === 'super_admin' ? 'super-admin-badge' : ''; ?>">
                        <?php echo htmlspecialchars($roleDescription); ?>
                    </span>

                    <h1 class="display-4 mb-3">
                        Welcome, <?php echo htmlspecialchars($userName); ?>!
                    </h1>

                    <?php if ($userType === 'super_admin'): ?>
                        <p class="lead mb-4">
                            You have super admin access to the entire SaaS platform
                        </p>
                    <?php elseif ($userType === 'owner' || $userType === 'staff'): ?>
                        <p class="lead mb-4">
                            Managing: <strong><?php echo htmlspecialchars($franchiseName); ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="lead mb-4">
                            Welcome to your dashboard
                        </p>
                    <?php endif; ?>

                    <!-- Action Buttons Based on User Type -->
                    <div class="mt-4">
                        <?php if ($userType === 'super_admin'): ?>
                            <!-- Super Admin Buttons -->
                            <a href="./adminpanel/" class="btn btn-primary big-button mb-3 w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                </svg>
                                Super Admin Panel
                            </a>
                            <a href="./dashboard.php" class="btn btn-outline-primary big-button w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 21l18 0" />
                                    <path d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l2 4" />
                                    <path d="M5 21l0 -10.15" />
                                    <path d="M19 21l0 -10.15" />
                                    <path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" />
                                </svg>
                                Franchise Dashboard
                            </a>

                        <?php elseif ($userType === 'owner' || $userType === 'staff'): ?>
                            <!-- Franchise Admin Button -->
                            <a href="./dashboard.php" class="btn btn-primary big-button w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                                    <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                                </svg>
                                Go to Dashboard
                            </a>

                        <?php else: ?>
                            <!-- End User Button -->
                            <a href="./memberpanel/" class="btn btn-primary big-button w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                </svg>
                                My Dashboard
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4 text-muted">
                        <small>
                            <?php if ($userType === 'super_admin'): ?>
                                Manage the entire SaaS platform or view specific franchises
                            <?php elseif ($userType === 'owner' || $userType === 'staff'): ?>
                                Manage your franchise operations and users
                            <?php else: ?>
                                Access your personal dashboard and information
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="page-body">
            <div class="container-xl">
                <div class="row row-deck row-cards mt-4 mb-4">

                    <?php if ($userType === 'super_admin'): ?>
                    <!-- Super Admin Info Cards -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-muted mb-2">Super Admin Panel</div>
                                <div class="h3 mb-2">System Management</div>
                                <p class="text-muted">
                                    Manage all franchises, users, billing, and global settings
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-muted mb-2">Franchise Dashboard</div>
                                <div class="h3 mb-2">Franchise View</div>
                                <p class="text-muted">
                                    View and manage individual franchises as if you were the owner
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-muted mb-2">Full Access</div>
                                <div class="h3 mb-2">All Tiers</div>
                                <p class="text-muted">
                                    Access all three tiers: admin, franchise, and member panels
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php elseif ($userType === 'owner' || $userType === 'staff'): ?>
                    <!-- Franchise Admin Info -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-muted mb-2">Franchise Dashboard</div>
                                <div class="h3 mb-2">Manage Operations</div>
                                <p class="text-muted">
                                    Control all aspects of your franchise operations
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-muted mb-2">Your Franchise</div>
                                <div class="h3 mb-2"><?php echo htmlspecialchars($franchiseName); ?></div>
                                <p class="text-muted">
                                    Manage users, settings, and view reports
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php else: ?>
                    <!-- End User Info -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-muted mb-2">Member Portal</div>
                                <div class="h3 mb-2">Your Dashboard</div>
                                <p class="text-muted">
                                    Access your personal information and features
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <footer class="footer footer-transparent d-print-none">
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </footer>
    </div>

   <?php include __DIR__ . "/includes/foot.php"; ?>
</body>
</html>
