<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth\Services\{AuthService, TokenService, PermissionService};
use App\Auth\Helpers\{CookieHelper, CSRFHelper, PasswordHelper};
use App\Auth\DTO\LoginDTO;
use App\Config\Database;
use App\Helpers\UiHelper;

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Validate required environment variables
$dotenv->required([
  'COOKIE_DOMAIN',
  'COOKIE_ENCRYPTION_KEY',
  'APP_ENV'
])->notEmpty();

// Initialize CSRF helper
$csrfHelper = new CSRFHelper();
$csrfToken = $csrfHelper->generateToken();

// Get random login background
$loginBackground = UiHelper::getRandomLoginBackground();

// Initialize error and success messages
$error = '';
$success = '';
$loginSuccess = false;

// Redirect if already logged in (but not on POST - we're processing login)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  requireGuest();
}

// Check for URL parameters
if (isset($_GET['msg'])) {
  switch ($_GET['msg']) {
    case 'session_expired':
      $error = 'Your session has expired. Please login again.';
      break;
    case 'logout':
      $success = 'You have been successfully logged out.';
      break;
  }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    // Validate CSRF token
    $csrfHelper->validateToken($_POST['csrf_token'] ?? '');

    // Validate input
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $rememberMe = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
      throw new \Exception("Please provide both username and password");
    }

    // Initialize services
    $db = (new Database())->getConnection();
    $authService = new AuthService(
      $db,
      new TokenService($db),
      new PermissionService($db),
      new PasswordHelper(),
      new CookieHelper()
    );

    // Create login DTO and authenticate
    $loginDTO = new LoginDTO(
      $username,
      $password,
      $_SERVER['REMOTE_ADDR'],
      $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    );

    $result = $authService->authenticate($loginDTO);

    if ($result->getStatus() === 'SUCCESS') {
      // AuthService::authenticate() already wrote all session variables.
      // We only need to regenerate the session ID here for security.
      regenerateSession();

      // Handle remember me cookie (AuthService doesn't set cookies)
      if ($rememberMe) {
        try {
          $cookieHelper = new CookieHelper();
          $session = $authService->createUserSession($result->getUserId(), true);
          $cookieHelper->createSecureCookie('remember_token', $session->getToken(), 86400 * 30);
        } catch (\Exception $e) {
          error_log('Remember me failed: ' . $e->getMessage());
          // Non-fatal — user is still logged in
        }
      }

      // Check for forced password change
      if ((getSession('force_password_change') ?? 0) == 1) {
        header('Location: ./change-password.php');
        exit();
      }

      $userName = getSession('full_name') ?: $result->getUsername();
      $loginSuccess = true;
    } else {
      $errorMessages = [
        'USER_NOT_FOUND'    => 'No account found with this username or email.',
        'INVALID_PASSWORD'  => 'Invalid password provided.',
        'ACCOUNT_LOCKED'    => 'Account locked due to multiple failed login attempts.',
        'ACCOUNT_INACTIVE'  => 'Account is currently inactive.',
        'ACCOUNT_SUSPENDED' => 'Account has been suspended.',
        'SESSION_ERROR'     => 'Unable to create login session.',
        'DATABASE_ERROR'    => 'System error: Database connection failed.',
        'VALIDATION_ERROR'  => 'Invalid login credentials format.',
      ];
      $error = $errorMessages[$result->getStatus()] ?? 'Login failed. Please try again.';
    }
  } catch (\Exception $e) {
    $error = $e->getMessage();
    error_log('Login error: ' . $e->getMessage());
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Sign In — SaaS Seeder</title>
  <link href="./assets/tabler/css/tabler.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    @import url("https://rsms.me/inter/inter.css");
    *, *::before, *::after { box-sizing: border-box; }

    html, body { height: 100%; margin: 0; }

    .auth-split {
      display: flex;
      min-height: 100vh;
    }

    /* ── Left panel ── */
    .auth-left {
      flex: 0 0 55%;
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
      background: linear-gradient(
        160deg,
        rgba(6, 111, 209, 0.55) 0%,
        rgba(0, 0, 0, 0.72) 100%
      );
      pointer-events: none;
    }
    .auth-left > * { position: relative; z-index: 1; }

    .auth-left-logo {
      font-size: 1.6rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: -0.5px;
      text-decoration: none;
    }
    .auth-left-tagline {
      color: rgba(255,255,255,0.92);
    }
    .auth-left-tagline h2 {
      font-size: 2rem;
      font-weight: 700;
      line-height: 1.25;
      margin-bottom: .75rem;
    }
    .auth-left-tagline p {
      font-size: 1rem;
      opacity: .8;
      max-width: 380px;
    }
    .auth-left-footer {
      font-size: .8rem;
      color: rgba(255,255,255,.45);
    }

    /* ── Right panel ── */
    .auth-right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      padding: 2rem 1.5rem;
    }
    .auth-form-wrap {
      width: 100%;
      max-width: 400px;
    }
    .auth-form-wrap .brand-mobile {
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--tblr-primary);
      margin-bottom: 2rem;
      text-align: center;
    }
    .auth-form-wrap h2 {
      font-size: 1.6rem;
      font-weight: 700;
      margin-bottom: .3rem;
    }
    .auth-form-wrap .subtitle {
      color: #6b7280;
      margin-bottom: 1.75rem;
      font-size: .95rem;
    }

    .form-floating-label { position: relative; margin-bottom: 1rem; }
    .form-floating-label input {
      width: 100%;
      height: 52px;
      padding: 1.2rem .875rem .4rem;
      font-size: .95rem;
      border-radius: 8px;
      border: 1.5px solid #e5e7eb;
      transition: border-color .15s;
      outline: none;
    }
    .form-floating-label input:focus {
      border-color: var(--tblr-primary);
      box-shadow: 0 0 0 3px rgba(6,111,209,.1);
    }
    .form-floating-label label {
      position: absolute;
      top: 50%;
      left: .9rem;
      transform: translateY(-50%);
      font-size: .9rem;
      color: #9ca3af;
      transition: all .15s;
      pointer-events: none;
      background: transparent;
    }
    .form-floating-label input:focus ~ label,
    .form-floating-label input:not(:placeholder-shown) ~ label {
      top: .55rem;
      font-size: .7rem;
      color: var(--tblr-primary);
      transform: none;
    }
    .pw-wrap { position: relative; }
    .pw-wrap input { padding-right: 3rem; }
    .pw-toggle {
      position: absolute;
      right: .875rem;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #9ca3af;
      background: none;
      border: none;
      padding: 0;
      line-height: 1;
    }
    .pw-toggle:hover { color: #374151; }

    .btn-signin {
      height: 50px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 8px;
      letter-spacing: .01em;
    }

    @media (max-width: 991.98px) {
      .auth-left { display: none; }
      .auth-right { background: #f9fafb; }
    }
  </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($loginSuccess): ?>
<!-- ── Success state ── -->
<div class="auth-split" style="justify-content:center;align-items:center;background:#f9fafb;">
  <div class="text-center">
    <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;" role="status"></div>
    <h3 class="fw-semibold">Signing you in…</h3>
  </div>
</div>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Welcome back!',
    html: 'Hello, <strong><?php echo htmlspecialchars($userName ?? 'User'); ?></strong>. Taking you to your dashboard.',
    timer: 2200,
    timerProgressBar: true,
    showConfirmButton: false,
    allowOutsideClick: false,
  }).then(() => { window.location.href = './index.php'; });
</script>

<?php else: ?>
<!-- ── Login form ── -->
<div class="auth-split">

  <!-- Left panel -->
  <div class="auth-left">
    <a href="." class="auth-left-logo">SaaS Seeder</a>
    <div class="auth-left-tagline">
      <h2>Build your SaaS<br>faster than ever.</h2>
      <p>Multi-tenant architecture, role-based access control, and a clean three-tier panel system — ready out of the box.</p>
    </div>
    <div class="auth-left-footer">
      &copy; <?php echo date('Y'); ?> Chwezi Core Systems
    </div>
  </div>

  <!-- Right panel -->
  <div class="auth-right">
    <div class="auth-form-wrap">

      <!-- Mobile brand (hidden on desktop) -->
      <div class="brand-mobile d-lg-none">SaaS Seeder</div>

      <h2>Sign in</h2>
      <p class="subtitle">Enter your credentials to access your account.</p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" role="alert" style="border-radius:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
          <div><?php echo htmlspecialchars($error); ?></div>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3" role="alert" style="border-radius:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
          <div><?php echo htmlspecialchars($success); ?></div>
        </div>
      <?php endif; ?>

      <form action="./sign-in.php" method="POST" autocomplete="off" novalidate id="loginForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

        <div class="form-floating-label">
          <input
            type="text"
            name="username"
            id="username"
            placeholder=" "
            autocomplete="off"
            required
            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
          >
          <label for="username">Username or Email</label>
        </div>

        <div class="form-floating-label">
          <div class="pw-wrap">
            <input
              type="password"
              name="password"
              id="password"
              placeholder=" "
              autocomplete="off"
              required
            >
            <label for="password">Password</label>
            <button type="button" class="pw-toggle" id="togglePassword" aria-label="Toggle password visibility">
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
          <label class="form-check mb-0">
            <input type="checkbox" name="remember" class="form-check-input">
            <span class="form-check-label" style="font-size:.875rem;">Remember me</span>
          </label>
          <a href="./forgot-password.php" style="font-size:.875rem;" class="text-muted">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-signin" id="submitBtn">
          Sign In
        </button>
      </form>

      <p class="text-center text-muted mt-4" style="font-size:.78rem;">
        &copy; <?php echo date('Y'); ?> <a href="https://chwezi.co.za" target="_blank" class="text-muted">Chwezi Core Systems</a>
      </p>

    </div>
  </div>

</div>
<?php endif; ?>

<script src="./assets/tabler/js/tabler.min.js"></script>
<script>
  // Password visibility toggle
  const pwField   = document.getElementById('password');
  const toggleBtn = document.getElementById('togglePassword');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const isPassword = pwField.type === 'password';
      pwField.type = isPassword ? 'text' : 'password';
      toggleBtn.innerHTML = isPassword
        ? `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/></svg>`;
    });
  }

  // Submit button loading state
  const form      = document.getElementById('loginForm');
  const submitBtn = document.getElementById('submitBtn');
  if (form && submitBtn) {
    form.addEventListener('submit', () => {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Signing in…';
    });
  }
</script>
</body>
</html>