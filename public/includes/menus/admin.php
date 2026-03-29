<?php
// Admin menu — shown to franchise owners/staff and super admins
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<a class="nav-link<?php echo $currentPage === 'dashboard.php' ? ' active' : ''; ?>" href="/dashboard.php">Dashboard</a>
<?php if (function_exists('getSession') && getSession('user_type') === 'super_admin'): ?>
<a class="nav-link<?php echo strpos($_SERVER['REQUEST_URI'] ?? '', '/adminpanel/') !== false ? ' active' : ''; ?>" href="/adminpanel/">System Admin</a>
<?php endif; ?>
