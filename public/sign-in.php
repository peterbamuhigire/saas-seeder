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
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>Sign in - SaaS Seeder Template</title>
  <!-- BEGIN GLOBAL MANDATORY STYLES -->
  <link href="./assets/tabler/css/tabler.min.css" rel="stylesheet" />
  <!-- END GLOBAL MANDATORY STYLES -->
  <!-- BEGIN PLUGINS STYLES -->
  <link href="./assets/tabler/css/tabler-flags.min.css" rel="stylesheet" />
  <link href="./assets/tabler/css/tabler-payments.min.css" rel="stylesheet" />
  <link href="./assets/tabler/css/tabler-vendors.min.css" rel="stylesheet" />
  <!-- END PLUGINS STYLES -->
  <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
  <style>
    @import url("https://rsms.me/inter/inter.css");

    /* Login page background */
    body {
      background-image: url('<?php echo htmlspecialchars($loginBackground); ?>');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }

    /* Add darker overlay to make sign-in card stand out */
    .page-center::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.65);
      z-index: -1;
    }

    .page-center {
      position: relative;
    }
  </style>
</head>

<body>
  <script src="./assets/tabler/js/tabler.min.js"></script>
  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <?php if (isset($loginSuccess) && $loginSuccess === true): ?>
    <!-- Login Success - Show SweetAlert and Redirect -->
    <div class="page page-center">
      <div class="container container-tight py-4">
        <div class="text-center mb-4">
          <a href="." aria-label="SaaS Seeder" class="navbar-brand navbar-brand-autodark">
            <h1 class="text-primary">SaaS Seeder</h1>
          </a>
        </div>
        <div class="card card-md">
          <div class="card-body text-center">
            <div class="spinner-border text-primary mb-3" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <h3>Login Successful!</h3>
            <p class="text-muted">Redirecting to dashboard...</p>
          </div>
        </div>
      </div>
    </div>

    <script>
      // Show SweetAlert immediately on page load
      Swal.fire({
        icon: 'success',
        title: 'Welcome Back!',
        html: '<p>Login successful!</p><p>Welcome, <strong><?php echo htmlspecialchars($userName ?? 'User'); ?></strong>!</p>',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        }
      }).then(() => {
        window.location.href = './index.php';
      });
    </script>

  <?php else: ?>
    <!-- Login Form -->
    <div class="page page-center">
      <div class="container container-tight py-4">

        <div class="card card-md">
          <div class="card-body">
            <div class="text-center mb-4">
              <a href="." aria-label="SaaS Seeder" class="navbar-brand navbar-brand-autodark">
                <h1 class="text-primary">SaaS Seeder</h1>
              </a>
            </div>
            <h2 class="h2 text-center mb-4">Login to your account</h2>

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger alert-dismissible" role="alert">
                <div class="d-flex">
                  <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                      viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                      stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                      <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                      <path d="M12 8v4"></path>
                      <path d="M12 16h.01"></path>
                    </svg>
                  </div>
                  <div><?php echo htmlspecialchars($error); ?></div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
              </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
              <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                  <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                      viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                      stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                      <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                      <path d="M9 12l2 2l4 -4"></path>
                    </svg>
                  </div>
                  <div><?php echo htmlspecialchars($success); ?></div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
              </div>
            <?php endif; ?>

            <form action="./sign-in.php" method="POST" autocomplete="off">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

              <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" name="username" class="form-control" placeholder="Enter your username"
                  autocomplete="off" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" />
              </div>

              <div class="mb-2">
                <label class="form-label">
                  Password
                  <span class="form-label-description">
                    <a href="./forgot-password.php">I forgot password</a>
                  </span>
                </label>
                <div class="input-group input-group-flat">
                  <input type="password" name="password" id="password" class="form-control" placeholder="Your password"
                    autocomplete="off" required />
                  <span class="input-group-text">
                    <a href="#" class="link-secondary" title="Show password" id="toggle-password">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon">
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                      </svg>
                    </a>
                  </span>
                </div>
              </div>

              <div class="mb-2">
                <label class="form-check">
                  <input type="checkbox" name="remember" class="form-check-input" />
                  <span class="form-check-label">Remember me on this device</span>
                </label>
              </div>

              <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">Sign in</button>
              </div>
            </form>
          </div>


          <div class="text-center text-muted mt-3">
            <small>Copyright (C) 2026-<?php echo date('Y'); ?> <a href="https://chwezi.co.za" target="_blank">Chwezi Core
                Systems</a></small>
          </div>
        </div>
      </div>
    </div>

    <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
    <script src="./assets/tabler/js/tabler.min.js" defer></script>
    <!-- END GLOBAL MANDATORY SCRIPTS -->

    <!-- Password toggle script -->
    <script>
      document.getElementById('toggle-password')?.addEventListener('click', function (e) {
        e.preventDefault();
        const passwordField = document.getElementById('password');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
      });
    </script>

  <?php endif; ?>
</body>

</html>