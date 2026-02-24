<?php
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Helpers\UiHelper;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

if (isLoggedIn()) {
    header('Location: ./index.php');
    exit();
}

$loginBackground = UiHelper::getRandomLoginBackground();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Forgot Password — SaaS Seeder</title>
  <link href="./assets/tabler/css/tabler.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    @import url("https://rsms.me/inter/inter.css");
    html, body { height: 100%; margin: 0; }
    .auth-split { display: flex; min-height: 100vh; }
    .auth-left {
      flex: 0 0 55%;
      background-image: url('<?php echo htmlspecialchars($loginBackground); ?>');
      background-size: cover; background-position: center; position: relative;
      display: flex; flex-direction: column; justify-content: space-between; padding: 3rem;
    }
    .auth-left::after {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(160deg, rgba(6,111,209,.55) 0%, rgba(0,0,0,.72) 100%);
    }
    .auth-left > * { position: relative; z-index: 1; }
    .auth-left-logo { font-size: 1.6rem; font-weight: 800; color: #fff; text-decoration: none; }
    .auth-left-footer { font-size: .8rem; color: rgba(255,255,255,.45); }
    .auth-right { flex: 1; display: flex; align-items: center; justify-content: center; background: #fff; padding: 2rem 1.5rem; }
    .auth-form-wrap { width: 100%; max-width: 400px; }
    .form-control { border-radius: 8px; border: 1.5px solid #e5e7eb; height: 50px; }
    .form-control:focus { border-color: var(--tblr-primary); box-shadow: 0 0 0 3px rgba(6,111,209,.1); }
    .btn-submit { height: 50px; font-size: 1rem; font-weight: 600; border-radius: 8px; }
    @media (max-width: 991.98px) { .auth-left { display: none; } .auth-right { background: #f9fafb; } }
  </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="auth-split">
  <!-- Left panel -->
  <div class="auth-left d-none d-lg-flex flex-column">
    <a href="." class="auth-left-logo">SaaS Seeder</a>
    <div style="color:rgba(255,255,255,.85);">
      <h2 style="font-size:2rem;font-weight:700;margin-bottom:.75rem;">Forgot your<br>password?</h2>
      <p style="opacity:.8;max-width:360px;">Enter your email or username and we'll send you reset instructions.</p>
    </div>
    <small class="auth-left-footer">&copy; <?php echo date('Y'); ?> Chwezi Core Systems</small>
  </div>

  <!-- Right panel -->
  <div class="auth-right">
    <div class="auth-form-wrap">
      <div class="d-lg-none text-center mb-4">
        <span style="font-size:1.4rem;font-weight:800;color:var(--tblr-primary);">SaaS Seeder</span>
      </div>

      <h2 style="font-size:1.6rem;font-weight:700;margin-bottom:.3rem;">Reset Password</h2>
      <p style="color:#6b7280;font-size:.95rem;margin-bottom:1.5rem;">
        Enter your email or username and we'll send reset instructions to your inbox.
      </p>

      <div class="alert alert-warning d-flex gap-2 align-items-start mb-4" style="border-radius:8px;font-size:.875rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 mt-1"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <div>Email-based password reset is not yet configured for this installation. Please contact your system administrator to reset your password.</div>
      </div>

      <form id="forgotPasswordForm" autocomplete="off">
        <div class="mb-4">
          <label class="form-label fw-medium">Email or Username</label>
          <input type="text" class="form-control" id="identifier" placeholder="Enter your email or username">
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-submit">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Send Reset Instructions
        </button>
      </form>

      <div class="text-center mt-4">
        <a href="./sign-in.php" style="font-size:.875rem;" class="text-muted">&larr; Back to sign in</a>
      </div>
    </div>
  </div>
</div>

<script src="./assets/tabler/js/tabler.min.js"></script>
<script>
  document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    Swal.fire({
      icon: 'info',
      title: 'Not Yet Configured',
      text: 'Email-based password reset has not been set up for this installation. Please contact your administrator.',
      confirmButtonText: 'OK'
    });
  });
</script>
</body>
</html>
