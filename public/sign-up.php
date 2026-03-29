<?php
/**
 * Sign-Up Page — Placeholder
 *
 * Self-registration is project-specific. Each SaaS built from this
 * template must implement its own sign-up flow based on requirements.
 *
 * For API-based registration, see: api/v1/public/auth/register.php
 */
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
$pageTitle = 'Sign Up';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?php echo htmlspecialchars($pageTitle); ?> — SaaS Seeder</title>
  <link href="./assets/tabler/css/tabler.min.css" rel="stylesheet">
  <style>
    @import url("https://rsms.me/inter/inter.css");
    html, body { height: 100%; margin: 0; }
    .auth-split { display: flex; min-height: 100vh; }
    .auth-left {
      flex: 0 0 55%;
      <?php if ($loginBackground !== ''): ?>
      background-image: url('<?php echo htmlspecialchars($loginBackground); ?>');
      background-size: cover; background-position: center;
      <?php else: ?>
      background: linear-gradient(160deg, #1a1f36 0%, #0d1117 100%);
      <?php endif; ?>
      position: relative;
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
    .auth-form-wrap { width: 100%; max-width: 440px; }
    @media (max-width: 991.98px) { .auth-left { display: none; } .auth-right { background: #f9fafb; } }
  </style>
</head>
<body>
<div class="auth-split">
  <div class="auth-left d-none d-lg-flex flex-column">
    <a href="." class="auth-left-logo"><img src="/assets/images/branding/logo-light.png" alt="Logo" style="max-height:75px;width:auto"></a>
    <div style="color:rgba(255,255,255,.85);">
      <h2 style="font-size:2rem;font-weight:700;margin-bottom:.75rem;">Create an<br>account.</h2>
      <p style="opacity:.8;max-width:360px;">Multi-tenant SaaS template with role-based access control, ready out of the box.</p>
    </div>
    <small class="auth-left-footer">&copy; <?php echo date('Y'); ?> Chwezi Core Systems</small>
  </div>

  <div class="auth-right">
    <div class="auth-form-wrap">
      <div class="d-lg-none text-center mb-4">
        <span style="font-size:1.4rem;font-weight:800;color:var(--tblr-primary);">SaaS Seeder</span>
      </div>

      <h2 style="font-size:1.6rem;font-weight:700;margin-bottom:.3rem;">Sign Up</h2>
      <p style="color:#6b7280;font-size:.95rem;margin-bottom:1.5rem;">
        Create a new account to get started.
      </p>

      <div class="alert alert-info d-flex gap-2 align-items-start mb-4" role="alert" style="border-radius:8px;font-size:.875rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 mt-1"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        <div>
          <strong>Registration not yet configured.</strong><br>
          Self-registration is project-specific. Implement your sign-up flow in this file
          based on your project requirements. For API-based registration,
          see <code>api/v1/public/auth/register.php</code>.
        </div>
      </div>

      <div class="text-center mt-4">
        <a href="./sign-in.php" style="font-size:.875rem;" class="text-muted">&larr; Back to sign in</a>
      </div>
    </div>
  </div>
</div>

<script src="./assets/tabler/js/tabler.min.js" defer></script>
</body>
</html>
