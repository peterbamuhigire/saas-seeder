# seed.ps1
# Run this from the Tabler template root (the directory that has dist/, static/, and the demo HTML files).

param(
  [switch]$SkipConfirm
)

$ErrorActionPreference = 'Stop'

if (-not $SkipConfirm) {
  $answer = Read-Host "This will restructure the template and delete demo HTML files. Continue? (yes/no)"
  if ($answer -ne 'yes') {
    Write-Host "Canceled."
    exit 1
  }
}

$dirs = @(
  "public",
  "public/assets",
  "public/assets/tabler",
  "public/assets/static",
  "public/assets/js",
  "public/assets/js/pages",
  "public/includes",
  "public/includes/menus",
  "public/_templates",
  "public/adminpanel",
  "public/memberpanel",
  "public/uploads",
  "api",
  "src",
  "src/config",
  "src/Services",
  "src/Modules"
)

foreach ($dir in $dirs) {
  New-Item -ItemType Directory -Force -Path $dir | Out-Null
}

# Move Tabler assets
if (Test-Path "dist") {
  Move-Item "dist\*" "public\assets\tabler\" -Force
  Remove-Item "dist" -Recurse -Force
}

# Optional: move static demo assets (images, icons) for reuse
if (Test-Path "static") {
  $staticTarget = "public\assets\static"
  if (Test-Path $staticTarget) {
    Copy-Item "static\*" $staticTarget -Recurse -Force
    Remove-Item "static" -Recurse -Force
  } else {
    Move-Item "static" "public\assets\" -Force
  }
}

# Keep only these HTML files and convert to PHP
$keep = @("index.html", "sign-in.html", "sign-up.html", "forgot-password.html")

Get-ChildItem -Path . -Filter "*.html" -File | ForEach-Object {
  if ($keep -notcontains $_.Name) {
    Remove-Item $_.FullName -Force
  }
}

foreach ($name in $keep) {
  if (Test-Path $name) {
    $newName = [IO.Path]::ChangeExtension($name, "php")
    Move-Item $name (Join-Path "public" $newName) -Force
  }
}

# Move common root assets
$rootAssets = @("favicon.ico", "favicon-dev.ico", "robots.txt", "sitemap.xml")
foreach ($file in $rootAssets) {
  if (Test-Path $file) {
    Move-Item $file "public\$file" -Force
  }
}

# Scaffold shared include files
$head = @'
<?php
$pageTitle = $pageTitle ?? 'SaaS Template';
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
<link rel="stylesheet" href="/assets/tabler/css/tabler.min.css">
<link rel="stylesheet" href="/assets/tabler/css/tabler-vendors.min.css">
<link rel="icon" href="/favicon.ico">
'@
Set-Content -Path "public/includes/head.php" -Value $head -Encoding ASCII

$topbar = @'
<?php
$panel = $panel ?? 'admin';
$menuFile = $panel === 'admin'
  ? __DIR__ . '/menus/admin.php'
  : __DIR__ . '/menus/member.php';
?>
<header class="navbar navbar-expand-md d-print-none">
  <div class="container-xl">
    <a class="navbar-brand" href="/"><?php echo htmlspecialchars($pageTitle ?? 'SaaS Template', ENT_QUOTES, 'UTF-8'); ?></a>
    <div class="navbar-nav">
      <?php if (file_exists($menuFile)) { include $menuFile; } ?>
    </div>
  </div>
</header>
'@
Set-Content -Path "public/includes/topbar.php" -Value $topbar -Encoding ASCII

$adminMenu = @'
<a class="nav-link" href="/adminpanel/index.php">Admin Dashboard</a>
<a class="nav-link" href="/sign-in.php">Sign In</a>
'@
Set-Content -Path "public/includes/menus/admin.php" -Value $adminMenu -Encoding ASCII

$memberMenu = @'
<a class="nav-link" href="/memberpanel/index.php">Member Dashboard</a>
<a class="nav-link" href="/sign-in.php">Sign In</a>
'@
Set-Content -Path "public/includes/menus/member.php" -Value $memberMenu -Encoding ASCII

$footer = @'
<footer class="footer footer-transparent d-print-none">
  <div class="container-xl">
    <div class="text-muted">Powered by SaaS Template</div>
  </div>
</footer>
'@
Set-Content -Path "public/includes/footer.php" -Value $footer -Encoding ASCII

$foot = @'
<script src="/assets/tabler/js/tabler.min.js"></script>
<script src="/assets/tabler/js/tabler-theme.min.js"></script>
'@
Set-Content -Path "public/includes/foot.php" -Value $foot -Encoding ASCII

# Scaffold basic panel index pages
$adminIndex = @'
<?php
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
          <div class="card">
            <div class="card-body">Admin panel placeholder.</div>
          </div>
        </div>
      </div>
      <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
  </div>
  <?php include __DIR__ . '/../includes/foot.php'; ?>
</body>
</html>
'@
Set-Content -Path "public/adminpanel/index.php" -Value $adminIndex -Encoding ASCII

$memberIndex = @'
<?php
$panel = 'member';
$pageTitle = 'Member Panel';
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
          <h2 class="page-title">Member Dashboard</h2>
        </div>
      </div>
      <div class="page-body">
        <div class="container-xl">
          <div class="card">
            <div class="card-body">Member panel placeholder.</div>
          </div>
        </div>
      </div>
      <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
  </div>
  <?php include __DIR__ . '/../includes/foot.php'; ?>
</body>
</html>
'@
Set-Content -Path "public/memberpanel/index.php" -Value $memberIndex -Encoding ASCII

# README
$readme = @'
# SaaS Template (Tabler)

This repository is a starter template for SaaS projects with Tabler UI.

## What this script did

- Moved Tabler assets into public/assets/tabler
- Removed demo HTML files (except index, sign-in, sign-up, forgot-password)
- Converted those pages to PHP and moved them into public
- Created shared includes and basic admin/member panel pages

## Manual steps

1) Add API routing
- Create public/.htaccess (or vhost rules) to route /api to api/index.php

Example rule:
RewriteEngine On
RewriteRule ^api/(.*)$ /api/index.php?path=$1 [QSA,L]

2) Build API front controller
- Create api/index.php that reads the path and dispatches to endpoints
- Keep API files under api/ (outside public)

3) Add auth
- Create src/config/auth.php and enforce session checks on panel pages
- Redirect to sign-in.php when not logged in

4) Create a seeder page template
- Add public/_templates/seeder-page.php and clone it for new pages

5) Update menus
- Edit public/includes/menus/admin.php and public/includes/menus/member.php

6) Uploads and images
- Use public/uploads for shared images (product images, avatars)

7) Clean unused assets
- Delete any unused libraries in public/assets/tabler/libs to keep the template lean

8) Replace branding
- Update page title, logos, and favicon in public/includes/head.php and public/assets

## Recommended structure

public/
  adminpanel/
  memberpanel/
  includes/
  assets/
  uploads/

api/
src/

## Notes

- Keep UI pages in public/
- Keep API outside public/
- Use shared includes for both panels
'@
Set-Content -Path "README.md" -Value $readme -Encoding ASCII

Write-Host "Done. Review README.md for manual steps."