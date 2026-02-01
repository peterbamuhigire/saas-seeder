<?php
require_once __DIR__ . '/../src/config/auth.php';

// SystemMethods is not needed for this template - using simple approach
$selectedBackground = '';

// Get the reason for denial if provided
$reason = $_GET['reason'] ?? 'permission_denied';
$permission = $_GET['permission'] ?? '';
$function = $_GET['function'] ?? '';

// Define user-friendly messages based on reason
$messages = [
    'permission_denied' => [
        'title' => 'Access Denied',
        'description' => 'You do not have permission to access this function.',
        'icon' => 'shield-x'
    ],
    'encoding_period_not_set' => [
        'title' => 'Encoding Period Not Set',
        'description' => 'The encoding period has not been configured for your franchise. Please contact your administrator.',
        'icon' => 'calendar-x'
    ],
    'invalid_franchise' => [
        'title' => 'Invalid Franchise',
        'description' => 'The requested franchise does not exist or you do not have access to it.',
        'icon' => 'building-x'
    ],
    'forbidden' => [
        'title' => 'Forbidden',
        'description' => 'You are not authorized to perform this action.',
        'icon' => 'lock'
    ]
];

$message = $messages[$reason] ?? $messages['permission_denied'];

// Determine the dashboard URL based on user type
$dashboardUrl = './index.php';
if (isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'super_admin':
            $dashboardUrl = './adminpanel/';
            break;
        case 'distributor':
            $dashboardUrl = './distributorpanel/';
            break;
        default:
            $dashboardUrl = './index.php';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Access Denied - Maduuka</title>
    <!-- CSS files -->
    <link href="./dist/css/tabler.min.css?1692870487" rel="stylesheet"/>
    <link href="./dist/css/tabler-flags.min.css?1692870487" rel="stylesheet"/>
    <link href="./dist/css/tabler-payments.min.css?1692870487" rel="stylesheet"/>
    <link href="./dist/css/tabler-vendors.min.css?1692870487" rel="stylesheet"/>
    <link href="./dist/css/demo.min.css?1692870487" rel="stylesheet"/>
    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }

        .error-page {
            display: flex;
            flex-direction: column;
            background: var(--tblr-bg-surface-secondary);
        }

        .access-denied-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .access-denied-card {
            max-width: 600px;
            width: 100%;
            text-align: center;
            animation: fadeInUp 0.6s ease-out;
        }

        .access-denied-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s ease-in-out infinite;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        }

        .access-denied-icon svg {
            width: 64px;
            height: 64px;
            color: white;
        }

        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--tblr-primary);
            margin-bottom: 1rem;
            animation: fadeIn 0.8s ease-out 0.2s both;
        }

        .error-subtitle {
            font-size: 1.25rem;
            color: var(--tblr-body-color);
            margin-bottom: 2rem;
            line-height: 1.6;
            animation: fadeIn 0.8s ease-out 0.4s both;
        }

        .error-details {
            background: var(--tblr-bg-surface);
            border: 1px solid var(--tblr-border-color);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
            animation: fadeIn 0.8s ease-out 0.6s both;
        }

        .error-details-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .error-details-item:last-child {
            margin-bottom: 0;
        }

        .error-details-icon {
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
            color: var(--tblr-muted);
        }

        .btn-back {
            animation: fadeIn 0.8s ease-out 0.8s both;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-back:active {
            transform: translateY(0);
        }

        .help-text {
            margin-top: 2rem;
            font-size: 0.875rem;
            color: var(--tblr-muted);
            animation: fadeIn 0.8s ease-out 1s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
            }
            50% {
                box-shadow: 0 10px 60px rgba(102, 126, 234, 0.5);
            }
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 60%;
            left: 80%;
            animation-delay: 4s;
        }

        .shape:nth-child(3) {
            top: 80%;
            left: 20%;
            animation-delay: 8s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-50px) rotate(180deg);
            }
        }
    </style>
</head>
<body class="error-page">
    <!-- Floating background shapes -->
    <div class="floating-shapes">
        <svg class="shape" width="100" height="100" viewBox="0 0 100 100" fill="none">
            <circle cx="50" cy="50" r="50" fill="url(#gradient1)"/>
            <defs>
                <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>
        <svg class="shape" width="80" height="80" viewBox="0 0 80 80" fill="none">
            <rect width="80" height="80" rx="20" fill="url(#gradient2)"/>
            <defs>
                <linearGradient id="gradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#f093fb;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#f5576c;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>
        <svg class="shape" width="60" height="60" viewBox="0 0 60 60" fill="none">
            <polygon points="30,0 60,60 0,60" fill="url(#gradient3)"/>
            <defs>
                <linearGradient id="gradient3" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#4facfe;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#00f2fe;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>
    </div>

    <div class="access-denied-container">
        <div class="access-denied-card">
            <div class="access-denied-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-<?php echo $message['icon']; ?>" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <?php if ($message['icon'] === 'shield-x'): ?>
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M13.252 20.601c-.408 .155 -.826 .288 -1.252 .399a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3a12 12 0 0 0 8.5 3a12 12 0 0 1 .117 6.34" />
                        <path d="M22 22l-5 -5" />
                        <path d="M17 22l5 -5" />
                    <?php elseif ($message['icon'] === 'calendar-x'): ?>
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M13 21h-7a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6.5" />
                        <path d="M16 3v4" />
                        <path d="M8 3v4" />
                        <path d="M4 11h16" />
                        <path d="M22 22l-5 -5" />
                        <path d="M17 22l5 -5" />
                    <?php elseif ($message['icon'] === 'building-x'): ?>
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 21h9" />
                        <path d="M9 8h1" />
                        <path d="M9 12h1" />
                        <path d="M9 16h1" />
                        <path d="M14 8h1" />
                        <path d="M14 12h1" />
                        <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v7" />
                        <path d="M22 22l-5 -5" />
                        <path d="M17 22l5 -5" />
                    <?php else: ?>
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" />
                        <path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" />
                        <path d="M8 11v-4a4 4 0 1 1 8 0v4" />
                    <?php endif; ?>
                </svg>
            </div>

            <h1 class="error-title"><?php echo htmlspecialchars($message['title']); ?></h1>
            <p class="error-subtitle"><?php echo htmlspecialchars($message['description']); ?></p>

            <?php if ($permission || $function): ?>
            <div class="error-details">
                <h3 class="mb-3" style="font-size: 1rem; color: var(--tblr-muted);">Details</h3>
                <?php if ($function): ?>
                <div class="error-details-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="error-details-icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />
                        <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />
                        <path d="M16 5l3 3" />
                    </svg>
                    <div>
                        <strong>Function:</strong> <?php echo htmlspecialchars($function); ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($permission): ?>
                <div class="error-details-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="error-details-icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" />
                        <path d="M12 11m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                        <path d="M12 12l0 2.5" />
                    </svg>
                    <div>
                        <strong>Required Permission:</strong> <?php echo htmlspecialchars($permission); ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="error-details-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="error-details-icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                    </svg>
                    <div>
                        <strong>Your User Type:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $_SESSION['user_type'] ?? 'Unknown'))); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <a href="<?php echo htmlspecialchars($dashboardUrl); ?>" class="btn btn-primary btn-back btn-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-home me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                    <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                </svg>
                Return to Dashboard
            </a>

            <div class="help-text">
                <p class="mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-info-circle me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                        <path d="M12 9h.01" />
                        <path d="M11 12h1v4h1" />
                    </svg>
                    If you believe this is an error, please contact your system administrator.
                </p>
                <p class="text-muted">
                    They can grant you the necessary permissions to access this function.
                </p>
            </div>
        </div>
    </div>

    <!-- Core plugin JavaScript-->
    <script src="./dist/libs/apexcharts/dist/apexcharts.min.js?1692870487" defer></script>
    <script src="./dist/libs/jsvectormap/dist/js/jsvectormap.min.js?1692870487" defer></script>
    <script src="./dist/libs/jsvectormap/dist/maps/world.js?1692870487" defer></script>
    <script src="./dist/libs/jsvectormap/dist/maps/world-merc.js?1692870487" defer></script>
    <!-- Tabler Core -->
    <script src="./dist/js/tabler.min.js?1692870487" defer></script>
    <script src="./dist/js/demo.min.js?1692870487" defer></script>
</body>
</html>
