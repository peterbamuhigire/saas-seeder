<?php
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth\Helpers\{PasswordHelper, CSRFHelper};
use App\Config\Database;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

requireAuth();

$csrfHelper = new CSRFHelper();
$csrfToken  = $csrfHelper->generateToken();
$loginBackground = \App\Helpers\UiHelper::getRandomLoginBackground();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $csrfHelper->validateToken($_POST['csrf_token'] ?? '');

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword      = $_POST['new_password'] ?? '';
        $confirmPassword  = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            throw new \Exception('All fields are required.');
        }

        if ($newPassword !== $confirmPassword) {
            throw new \Exception('New passwords do not match.');
        }

        $passwordHelper = new PasswordHelper();

        $errors = $passwordHelper->validatePasswordStrength($newPassword);
        if (!empty($errors)) {
            throw new \Exception(implode(' ', $errors));
        }

        $db     = (new Database())->getConnection();
        $userId = (int) getSession('user_id');

        // Verify current password
        $stmt = $db->prepare("SELECT password_hash FROM tbl_users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row || !$passwordHelper->verifyPassword($currentPassword, $row['password_hash'])) {
            throw new \Exception('Current password is incorrect.');
        }

        // Hash new password via PasswordHelper (single source of truth)
        $newHash = $passwordHelper->hashPassword($newPassword);

        $stmt = $db->prepare(
            "UPDATE tbl_users SET password_hash = ?, force_password_change = 0, updated_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$newHash, $userId]);

        // Clear the force_password_change session flag
        setSession('force_password_change', 0);

        $success = 'Password changed successfully.';

        // Redirect to dashboard after a moment
        header('refresh:2;url=./index.php');

    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Change Password';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?php echo htmlspecialchars($pageTitle); ?> - SaaS Seeder</title>
  <link href="./assets/tabler/css/tabler.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    @import url("https://rsms.me/inter/inter.css");
    .auth-split { min-height: 100vh; display: flex; }
    .auth-left {
      flex: 0 0 45%;
      background-image: url('<?php echo htmlspecialchars($loginBackground); ?>');
      background-size: cover;
      background-position: center;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 3rem;
    }
    .auth-left::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.4) 100%);
    }
    .auth-left > * { position: relative; z-index: 1; }
    .auth-left-logo { font-size: 1.6rem; font-weight: 800; color: #fff; text-decoration: none; }
    .auth-left-tagline { color: rgba(255,255,255,0.92); }
    .auth-left-tagline h3 { font-size: 1.8rem; font-weight: 700; margin-bottom: .5rem; }
    .auth-left-tagline p { font-size: .95rem; opacity: .75; max-width: 340px; }
    .auth-left-footer { font-size: .8rem; color: rgba(255,255,255,.45); }
    .auth-right { flex: 1; display: flex; align-items: center; justify-content: center; background: #fff; padding: 2rem 1.5rem; }
    .auth-form-wrap { width: 100%; max-width: 420px; }
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
    <div class="auth-left-tagline">
      <h3>Secure Your Account</h3>
      <p>Choose a strong password with uppercase, lowercase, numbers, and special characters.</p>
    </div>
    <small class="auth-left-footer">&copy; <?php echo date('Y'); ?> Chwezi Core Systems</small>
  </div>

  <!-- Right panel -->
  <div class="auth-right">
    <div class="auth-form-wrap">
      <div class="d-lg-none text-center mb-4">
        <span style="font-size:1.4rem;font-weight:800;color:var(--tblr-primary);">SaaS Seeder</span>
      </div>

      <h2 class="mb-1 fw-bold">Change Password</h2>
      <p class="text-muted mb-4">
        <?php if ((getSession('force_password_change') ?? 0) == 1): ?>
          You must change your password before continuing.
        <?php else: ?>
          Update your account password below.
        <?php endif; ?>
      </p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="border-radius:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3" style="border-radius:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
          <?php echo htmlspecialchars($success); ?> Redirecting...
        </div>
      <?php endif; ?>

      <form method="POST" action="./change-password.php" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

        <div class="mb-3">
          <label class="form-label fw-medium">Current Password</label>
          <div class="input-group input-group-flat">
            <input type="password" name="current_password" id="current_password" class="form-control" required>
            <span class="input-group-text">
              <button type="button" class="btn btn-link text-muted p-0 toggle-pw" data-target="current_password">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
              </button>
            </span>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-medium">New Password</label>
          <div class="input-group input-group-flat">
            <input type="password" name="new_password" id="new_password" class="form-control" required>
            <span class="input-group-text">
              <button type="button" class="btn btn-link text-muted p-0 toggle-pw" data-target="new_password">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
              </button>
            </span>
          </div>
          <div id="pw-strength" class="mt-2" style="display:none;">
            <div class="progress" style="height:4px;">
              <div id="pw-strength-bar" class="progress-bar" role="progressbar" style="width:0%"></div>
            </div>
            <small id="pw-strength-text" class="text-muted"></small>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium">Confirm New Password</label>
          <div class="input-group input-group-flat">
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            <span class="input-group-text">
              <button type="button" class="btn btn-link text-muted p-0 toggle-pw" data-target="confirm_password">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
              </button>
            </span>
          </div>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-submit">Change Password</button>
        </div>
      </form>

      <?php if ((getSession('force_password_change') ?? 0) != 1): ?>
        <div class="text-center mt-3">
          <a href="./index.php" class="text-muted" style="font-size:.875rem;">Cancel</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="./assets/tabler/js/tabler.min.js"></script>
<script>
  // Password visibility toggles
  document.querySelectorAll('.toggle-pw').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const field = document.getElementById(btn.dataset.target);
      field.type = field.type === 'password' ? 'text' : 'password';
    });
  });

  // Password strength meter
  const newPw = document.getElementById('new_password');
  const strengthBar = document.getElementById('pw-strength-bar');
  const strengthText = document.getElementById('pw-strength-text');
  const strengthWrap = document.getElementById('pw-strength');

  newPw.addEventListener('input', function() {
    const val = this.value;
    if (!val) { strengthWrap.style.display = 'none'; return; }
    strengthWrap.style.display = 'block';

    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[!@#$%^&*()\-_=+{};:,<.>]/.test(val)) score++;

    const levels = [
      { pct: 20, cls: 'bg-danger',  label: 'Very weak' },
      { pct: 40, cls: 'bg-warning', label: 'Weak' },
      { pct: 60, cls: 'bg-info',    label: 'Fair' },
      { pct: 80, cls: 'bg-primary', label: 'Strong' },
      { pct: 100, cls: 'bg-success', label: 'Very strong' },
    ];
    const lvl = levels[score - 1] || levels[0];
    strengthBar.style.width = lvl.pct + '%';
    strengthBar.className = 'progress-bar ' + lvl.cls;
    strengthText.textContent = lvl.label;
  });

  // Confirm password match
  document.getElementById('confirm_password').addEventListener('input', function() {
    this.setCustomValidity(this.value && this.value !== newPw.value ? 'Passwords do not match' : '');
  });
</script>
</body>
</html>
