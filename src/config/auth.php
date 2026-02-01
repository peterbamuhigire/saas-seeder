<?php
// Auth configuration for SaaS Seeder Template
require_once dirname(__FILE__) . '/database.php';
require_once dirname(__FILE__) . '/autoloader.php';
require_once dirname(__FILE__) . '/session.php';

// Initialize session with secure settings
initSession();

function isLoggedIn(): bool {
    if (hasSession('user_id') && hasSession('last_activity')) {
        $timeout = time() - getSession('last_activity');

        if ($timeout < 1800) {
            setSession('last_activity', time());
            return true;
        }
        error_log("Session expired - clearing session");
        clearPrefixedSession();
        session_destroy();
    }
    return false;
}

function requireAuth() {
    if (!isLoggedIn()) {
        // Store intended destination
        setSession('redirect_after_login', $_SERVER['REQUEST_URI']);

        // Determine correct path to sign-in based on current location
        $scriptPath = $_SERVER['SCRIPT_NAME'];
        $redirectBase = '';

        if (strpos($scriptPath, '/adminpanel/') !== false || strpos($scriptPath, '/memberpanel/') !== false) {
            $redirectBase = '../';
        } else {
            $redirectBase = './';
        }

        header('Location: ' . $redirectBase . 'sign-in.php');
        exit();
    }
}

function requireGuest() {
    if (isLoggedIn()) {
        $userType = getSession('user_type', '');

        if ($userType === 'super_admin' || $userType === 'owner') {
            header('Location: ./adminpanel/');
        } else {
            header('Location: ./memberpanel/');
        }
        exit();
    }
}

function checkAuth() {
    // Check if user is logged in
    if (!hasSession('user_id')) {
        header('Location: ./sign-in.php');
        exit();
    }

    // Check if session is expired
    if (hasSession('last_activity') && (time() - getSession('last_activity') > 1800)) {
        clearPrefixedSession();
        session_destroy();
        header('Location: ./sign-in.php?msg=session_expired');
        exit();
    }

    setSession('last_activity', time());
}

function logout() {
    clearPrefixedSession();
    session_destroy();
    header('Location: ./sign-in.php');
    exit();
}

/**
 * Check whether the current user has a permission (UI gating)
 * Returns true if the user has permission, false otherwise
 *
 * @param string $permissionCode
 * @param int|null $franchiseId
 * @return bool
 */
function hasPermissionGlobal(string $permissionCode, ?int $franchiseId = null): bool {
    if (!isLoggedIn()) return false;
    if ($franchiseId === null) $franchiseId = (int)(getSession('franchise_id') ?? 0);
    $db = (new App\Config\Database())->getConnection();
    $permSvc = new App\Auth\PermissionService($db);
    try {
        return $permSvc->hasPermission((int)getSession('user_id'), (int)$franchiseId, $permissionCode);
    } catch (Exception $e) {
        error_log('Permission check error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Require the current user to have a permission and exit with 403 if not
 *
 * @param string $permissionCode
 * @param int|null $franchiseId
 * @return void
 */
function requirePermissionGlobal(string $permissionCode, ?int $franchiseId = null): void {
    if (!isLoggedIn()) {
        header('Location: ./sign-in.php');
        exit();
    }
    if ($franchiseId === null) $franchiseId = (int)(getSession('franchise_id') ?? 0);
    $db = (new App\Config\Database())->getConnection();
    $permSvc = new App\Auth\PermissionService($db);
    try {
        $permSvc->requirePermission((int)getSession('user_id'), (int)$franchiseId, $permissionCode);
    } catch (Exception $e) {
        // Check if this is an API/AJAX request
        $isApiRequest = (
            isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        ) || (
            isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
        ) || (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );

        if ($isApiRequest) {
            // Return JSON for API requests
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
        } else {
            // Redirect to access-denied page for browser requests
            $scriptPath = $_SERVER['SCRIPT_NAME'];
            $redirectBase = '';

            // Check if we're in a subdirectory
            if (strpos($scriptPath, '/adminpanel/') !== false ||
                strpos($scriptPath, '/memberpanel/') !== false ||
                strpos($scriptPath, '/api/') !== false) {
                $redirectBase = '../';
            } else {
                $redirectBase = './';
            }

            $redirectUrl = $redirectBase . 'access-denied.php?reason=forbidden&permission=' . urlencode($permissionCode);
            header('Location: ' . $redirectUrl);
        }
        exit();
    }
}

// Automatic Access Control Enforcement
// This block runs on every page load where auth.php is included
if (isLoggedIn()) {
    $currentScript = $_SERVER['PHP_SELF'];
    $userType = getSession('user_type') ?? '';

    // Normalize path separators
    $currentScript = str_replace('\\', '/', $currentScript);

    // Define restricted zones
    $isAdminPanel = strpos($currentScript, '/adminpanel/') !== false;
    $isMemberPanel = strpos($currentScript, '/memberpanel/') !== false;
    $isApi = strpos($currentScript, '/api/') !== false;

    // Exclude logout from enforcement to allow exit
    if (strpos($currentScript, 'logout.php') !== false) {
        return;
    }

    // Determine redirect base path
    $redirectBase = ($isAdminPanel || $isMemberPanel) ? '../' : './';

    // 1. Enforce Admin Panel Protection
    // Only Super Admin and Owner can access Admin Panel
    if ($isAdminPanel) {
        if ($userType !== 'super_admin' && $userType !== 'owner') {
            header("Location: {$redirectBase}memberpanel/");
            exit();
        }
    }

    // 2. Enforce Member Panel Protection
    // Staff and other user types can access member panel
    if ($isMemberPanel) {
        if ($userType === 'super_admin' || $userType === 'owner') {
            // Admins accessing member panel is allowed (they can see both)
        }
    }
}
