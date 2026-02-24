<?php
/**
 * Super User Development Tool
 * SECURITY WARNING: Remove or restrict access to this file in production!
 */
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth\Helpers\{PasswordHelper, CSRFHelper};
use App\Config\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'COOKIE_ENCRYPTION_KEY', 'APP_ENV'])->notEmpty();

// Need session for CSRF
initSession();

$csrfHelper      = new CSRFHelper();
$csrfToken       = $csrfHelper->generateToken();
$error           = '';
$success         = '';
$createSuccess   = false;
$createdUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $csrfHelper->validateToken($_POST['csrf_token'] ?? '');

        $username        = trim($_POST['username'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $firstName       = trim($_POST['first_name'] ?? '');
        $lastName        = trim($_POST['last_name'] ?? '');
        $password        = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if (empty($username) || empty($email) || empty($firstName) || empty($lastName) || empty($password)) {
            throw new \Exception('All fields are required.');
        }
        if ($password !== $confirmPassword) {
            throw new \Exception('Passwords do not match.');
        }

        $passwordHelper = new PasswordHelper();
        $errors = $passwordHelper->validatePasswordStrength($password);
        if (!empty($errors)) {
            throw new \Exception(implode(' ', $errors));
        }

        $hashedPassword = $passwordHelper->hashPassword($password);
        $db = (new Database())->getConnection();

        $checkStmt = $db->prepare("SELECT id FROM tbl_users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        if ($checkStmt->fetch()) {
            throw new \Exception('Username or email already exists.');
        }

        $insertStmt = $db->prepare("
            INSERT INTO tbl_users
              (franchise_id, username, user_type, email, password_hash, first_name, last_name, status, force_password_change, created_at)
            VALUES
              (NULL, ?, 'super_admin', ?, ?, ?, ?, 'active', 0, NOW())
        ");

        if ($insertStmt->execute([$username, $email, $hashedPassword, $firstName, $lastName])) {
            $createSuccess   = true;
            $createdUsername = $username;
        } else {
            throw new \Exception('Failed to create user. Check database logs.');
        }

    } catch (\Exception $e) {
        $error = $e->getMessage();
        error_log('Super user creation error: ' . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Create Super Admin [DEV] — SaaS Seeder</title>
  <link href="./assets/tabler/css/tabler.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    @import url("https://rsms.me/inter/inter.css");
    html, body { height: 100%; margin: 0; }

    .auth-split { display: flex; min-height: 100vh; }

    /* Left panel — dark/branded */
    .auth-left {
      flex: 0 0 45%;
      background: linear-gradient(160deg, #1a1f36 0%, #0d1117 100%);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 3rem;
      position: relative;
      overflow: hidden;
    }
    .auth-left::before {
      content: '';
      position: absolute;
      width: 400px; height: 400px;
      background: rgba(6,111,209,.15);
      border-radius: 50%;
      top: -100px; right: -150px;
    }
    .auth-left > * { position: relative; z-index: 1; }
    .auth-left-logo { font-size: 1.5rem; font-weight: 800; color: #fff; text-decoration: none; }
    .auth-left-logo span { color: #066fd1; }
    .dev-badge {
      display: inline-block;
      padding: .25rem .6rem;
      background: rgba(255,193,7,.15);
      border: 1px solid rgba(255,193,7,.4);
      color: #ffc107;
      border-radius: 4px;
      font-size: .7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      margin-bottom: 1.5rem;
    }
    .auth-left-tagline { color: rgba(255,255,255,.85); }
    .auth-left-tagline h2 { font-size: 1.8rem; font-weight: 700; margin-bottom: .75rem; }
    .auth-left-tagline p { font-size: .9rem; opacity: .65; line-height: 1.6; }
    .security-notes { margin-top: 2rem; }
    .security-note {
      display: flex; align-items: flex-start; gap: .6rem;
      margin-bottom: .85rem; font-size: .8rem; color: rgba(255,255,255,.55);
    }
    .security-note svg { flex-shrink: 0; margin-top: 1px; color: #ffc107; }
    .auth-left-footer { font-size: .75rem; color: rgba(255,255,255,.3); }

    /* Right panel */
    .auth-right {
      flex: 1; display: flex; align-items: center; justify-content: center;
      background: #fff; padding: 2rem 1.5rem; overflow-y: auto;
    }
    .auth-form-wrap { width: 100%; max-width: 440px; }
    .auth-form-wrap h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: .3rem; }
    .auth-form-wrap .subtitle { color: #6b7280; font-size: .9rem; margin-bottom: 1.5rem; }

    .form-control { border-radius: 8px; border: 1.5px solid #e5e7eb; }
    .form-control:focus { border-color: var(--tblr-primary); box-shadow: 0 0 0 3px rgba(6,111,209,.1); }
    .btn-create { height: 50px; font-size: 1rem; font-weight: 600; border-radius: 8px; }

    .pw-strength-wrap .progress { height: 4px; border-radius: 2px; }
    .pw-strength-wrap small { font-size: .75rem; color: #6b7280; }

    @media (max-width: 991.98px) {
      .auth-left { display: none; }
      .auth-right { background: #f9fafb; }
    }
  </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($createSuccess): ?>
<div class="auth-split" style="justify-content:center;align-items:center;background:#f9fafb;">
  <div class="text-center">
    <div class="spinner-border text-success mb-3" style="width:3rem;height:3rem;" role="status"></div>
    <h3 class="fw-semibold">Super admin created!</h3>
    <p class="text-muted">Redirecting to sign in…</p>
  </div>
</div>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Account Created!',
    html: 'Super admin <strong><?php echo htmlspecialchars($createdUsername); ?></strong> is ready. You can now sign in.',
    timer: 3000,
    timerProgressBar: true,
    showConfirmButton: false,
  }).then(() => { window.location.href = './sign-in.php'; });
</script>

<?php else: ?>
<div class="auth-split">

  <!-- Left panel -->
  <div class="auth-left d-none d-lg-flex flex-column">
    <div>
      <a href="." class="auth-left-logo">SaaS<span>Seeder</span></a>
    </div>
    <div class="auth-left-tagline">
      <div class="dev-badge">Dev Tool</div>
      <h2>Create your<br>first super admin.</h2>
      <p>This tool creates super admin accounts with Argon2ID-hashed passwords. Remove or restrict this page before going to production.</p>
      <div class="security-notes">
        <div class="security-note">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Password hashed with Argon2ID + salt + pepper
        </div>
        <div class="security-note">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Same PasswordHelper used by AuthService at login
        </div>
        <div class="security-note">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          CSRF-protected form submission
        </div>
        <div class="security-note">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Remove this file before going to production
        </div>
      </div>
    </div>
    <div class="auth-left-footer">&copy; <?php echo date('Y'); ?> Chwezi Core Systems</div>
  </div>

  <!-- Right panel -->
  <div class="auth-right">
    <div class="auth-form-wrap">

      <div class="d-lg-none text-center mb-4">
        <span style="font-size:1.4rem;font-weight:800;color:var(--tblr-primary);">SaaS Seeder</span>
      </div>

      <h2>Create Super Admin</h2>
      <p class="subtitle">Fill in the details below to create the first super admin account.</p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="border-radius:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form action="./super-user-dev.php" method="POST" autocomplete="off" id="createForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label fw-medium">First Name <span class="text-danger">*</span></label>
            <input type="text" name="first_name" class="form-control" placeholder="John" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
          </div>
          <div class="col-6">
            <label class="form-label fw-medium">Last Name <span class="text-danger">*</span></label>
            <input type="text" name="last_name" class="form-control" placeholder="Doe" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-medium">Username <span class="text-danger">*</span></label>
          <input type="text" name="username" class="form-control" placeholder="admin" autocomplete="off" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
          <small class="text-muted">Used to sign in</small>
        </div>

        <div class="mb-3">
          <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" placeholder="admin@example.com" autocomplete="off" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="mb-3">
          <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
          <div class="input-group input-group-flat">
            <input type="password" name="password" id="password" class="form-control" placeholder="Min. 8 characters" autocomplete="off" required>
            <span class="input-group-text p-0 border-0 bg-transparent">
              <button type="button" class="btn btn-link text-muted px-3" id="togglePassword">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
              </button>
            </span>
          </div>
          <div class="pw-strength-wrap mt-2" id="pwStrengthWrap" style="display:none;">
            <div class="progress mb-1"><div id="pwStrengthBar" class="progress-bar" style="width:0%"></div></div>
            <small id="pwStrengthText"></small>
          </div>
          <small class="text-muted d-block mt-1">Uppercase, lowercase, number, and special character required</small>
        </div>

        <div class="mb-4">
          <label class="form-label fw-medium">Confirm Password <span class="text-danger">*</span></label>
          <div class="input-group input-group-flat">
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat password" autocomplete="off" required>
            <span class="input-group-text p-0 border-0 bg-transparent">
              <button type="button" class="btn btn-link text-muted px-3" id="toggleConfirm">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
              </button>
            </span>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-create" id="submitBtn">
          Create Super Admin Account
        </button>
      </form>

      <div class="text-center mt-3">
        <a href="./sign-in.php" class="text-muted" style="font-size:.875rem;">Already have an account? Sign in</a>
      </div>

    </div>
  </div>

</div>
<?php endif; ?>

<script src="./assets/tabler/js/tabler.min.js"></script>
<script>
  function makeToggle(btnId, fieldId) {
    const btn   = document.getElementById(btnId);
    const field = document.getElementById(fieldId);
    if (!btn || !field) return;
    btn.addEventListener('click', () => { field.type = field.type === 'password' ? 'text' : 'password'; });
  }
  makeToggle('togglePassword', 'password');
  makeToggle('toggleConfirm', 'confirm_password');

  const pwField        = document.getElementById('password');
  const pwStrengthBar  = document.getElementById('pwStrengthBar');
  const pwStrengthTxt  = document.getElementById('pwStrengthText');
  const pwStrengthWrap = document.getElementById('pwStrengthWrap');

  if (pwField) {
    pwField.addEventListener('input', function () {
      const v = this.value;
      if (!v) { pwStrengthWrap.style.display = 'none'; return; }
      pwStrengthWrap.style.display = 'block';
      let score = 0;
      if (v.length >= 8)                         score++;
      if (/[A-Z]/.test(v))                       score++;
      if (/[a-z]/.test(v))                       score++;
      if (/[0-9]/.test(v))                       score++;
      if (/[!@#$%^&*()\-_=+{};:,<.>]/.test(v))  score++;
      const levels = [
        { pct: 20,  cls: 'bg-danger',  label: 'Very weak'   },
        { pct: 40,  cls: 'bg-warning', label: 'Weak'        },
        { pct: 60,  cls: 'bg-info',    label: 'Fair'        },
        { pct: 80,  cls: 'bg-primary', label: 'Strong'      },
        { pct: 100, cls: 'bg-success', label: 'Very strong' },
      ];
      const lvl = levels[score - 1] || levels[0];
      pwStrengthBar.style.width = lvl.pct + '%';
      pwStrengthBar.className   = 'progress-bar ' + lvl.cls;
      pwStrengthTxt.textContent  = lvl.label;
    });
  }

  const cfmField = document.getElementById('confirm_password');
  if (cfmField && pwField) {
    cfmField.addEventListener('input', function () {
      this.setCustomValidity(this.value && this.value !== pwField.value ? 'Passwords do not match' : '');
    });
    pwField.addEventListener('input', function () {
      if (cfmField.value) cfmField.setCustomValidity(cfmField.value !== this.value ? 'Passwords do not match' : '');
    });
  }

  const createForm = document.getElementById('createForm');
  const submitBtn  = document.getElementById('submitBtn');
  if (createForm && submitBtn) {
    createForm.addEventListener('submit', () => {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Creating account…';
    });
  }
</script>
</body>
</html>
