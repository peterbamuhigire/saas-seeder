<?php
/**
 * Landing Page / Dashboard Router
 *
 * THREE-TIER PANEL STRUCTURE:
 * 1. /adminpanel/  - Super admin system (manage multiple franchises)
 * 2. /public/      - Franchise admin pages (manage franchise/school)
 * 3. /memberpanel/ - End user portal (students/customers/patients)
 *
 * Routing Logic:
 * - Not logged in → sign-in.php
 * - super_admin   → adminpanel/ (system management)
 * - owner/staff   → dashboard.php (franchise management)
 * - member/other  → memberpanel/ (end user portal)
 */

require_once __DIR__ . '/../src/config/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Not logged in - redirect to sign-in page
    header('Location: ./sign-in.php');
    exit();
}

// User is logged in - redirect based on user type
$userType = getSession('user_type', '');

if ($userType === 'super_admin') {
    // Super admin → System admin panel (manage all franchises)
    header('Location: ./adminpanel/');
} elseif ($userType === 'owner' || $userType === 'staff') {
    // Franchise admin → Franchise dashboard (root public/)
    // This is where school principals, restaurant managers work
    header('Location: ./dashboard.php');
} else {
    // End users (students, customers, patients) → Member panel
    header('Location: ./memberpanel/');
}
exit();
