<?php
$pageTitle = $pageTitle ?? 'SaaS Seeder';
require_once __DIR__ . '/security-headers.php';
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
<link rel="stylesheet" href="/assets/tabler/css/tabler.min.css">
<link rel="stylesheet" href="/assets/tabler/css/tabler-vendors.min.css">
<link rel="stylesheet" href="/assets/css/seeder-tokens.css">
<link rel="stylesheet" href="/assets/css/seeder-components.css">
<link rel="icon" href="/favicon.ico">
