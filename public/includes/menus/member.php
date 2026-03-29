<?php
// Member menu — shown to end users (students, customers, patients, etc.)
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<a class="nav-link<?php echo strpos($_SERVER['REQUEST_URI'] ?? '', '/memberpanel/') !== false ? ' active' : ''; ?>" href="/memberpanel/">My Dashboard</a>
