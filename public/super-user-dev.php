<?php
/**
 * Super User Development Tool
 *
 * This page is for development purposes only.
 * It allows creating super admin users with properly hashed passwords.
 *
 * SECURITY WARNING: Remove or restrict access to this file in production!
 */

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth\Helpers\{PasswordHelper, CSRFHelper};
use App\Config\Database;

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Validate required environment variables
$dotenv->required([
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'COOKIE_ENCRYPTION_KEY',
    'APP_ENV'
])->notEmpty();

// Initialize CSRF helper
$csrfHelper = new CSRFHelper();
$csrfToken = $csrfHelper->generateToken();

// Initialize error and success messages
$error = '';
$success = '';
$createSuccess = false;
$createdUsername = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        $csrfHelper->validateToken($_POST['csrf_token'] ?? '');

        // Validate input
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        // Basic validation
        if (empty($username) || empty($email) || empty($firstName) || empty($lastName) || empty($password)) {
            throw new \Exception("All fields are required");
        }

        if ($password !== $confirmPassword) {
            throw new \Exception("Passwords do not match");
        }

        // Validate password strength
        $passwordHelper = new PasswordHelper();
        $passwordErrors = $passwordHelper->validatePasswordStrength($password);
        if (!empty($passwordErrors)) {
            throw new \Exception("Password validation failed: " . implode(', ', $passwordErrors));
        }

        // Hash password using the same method as login
        $hashedPassword = $passwordHelper->hashPassword($password);

        // Get database connection
        $db = (new Database())->getConnection();

        // Check if username already exists
        $checkStmt = $db->prepare("SELECT id FROM tbl_users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        if ($checkStmt->fetch()) {
            throw new \Exception("Username or email already exists");
        }

        // Insert super admin user
        $insertStmt = $db->prepare("
            INSERT INTO tbl_users
            (franchise_id, username, user_type, email, password_hash, first_name, last_name, status, force_password_change, created_at)
            VALUES
            (NULL, ?, 'super_admin', ?, ?, ?, ?, 'active', 0, NOW())
        ");

        $result = $insertStmt->execute([
            $username,
            $email,
            $hashedPassword,
            $firstName,
            $lastName
        ]);

        if ($result) {
            // Success! Set flag for SweetAlert
            $createSuccess = true;
            $createdUsername = $username;
        } else {
            throw new \Exception("Failed to create user");
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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Create Super Admin - SaaS Seeder Template [DEV]</title>
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
      .dev-warning {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 4px;
        padding: 12px;
        margin-bottom: 20px;
      }
    </style>
  </head>
  <body>
    <script src="./assets/tabler/js/tabler.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if ($createSuccess === true): ?>
    <!-- Success - Show SweetAlert and Redirect -->
    <div class="page page-center">
      <div class="container container-tight py-4">
        <div class="text-center mb-4">
          <a href="." aria-label="SaaS Seeder" class="navbar-brand navbar-brand-autodark">
            <h1 class="text-primary">SaaS Seeder</h1>
          </a>
          <div class="text-muted mt-2">Super Admin Development Tool</div>
        </div>
        <div class="card card-md">
          <div class="card-body text-center">
            <div class="spinner-border text-success mb-3" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <h3>Super Admin Created Successfully!</h3>
            <p class="text-muted">Redirecting to login page...</p>
          </div>
        </div>
      </div>
    </div>

    <script>
      Swal.fire({
        icon: 'success',
        title: 'Account Created!',
        html: '<p>Super admin user <strong><?php echo htmlspecialchars($createdUsername); ?></strong> has been created successfully!</p><p>You can now login with your credentials.</p>',
        timer: 3000,
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
    <!-- Create User Form -->
    <div class="page page-center">
      <div class="container container-tight py-4">
        <div class="text-center mb-4">
          <a href="." aria-label="SaaS Seeder" class="navbar-brand navbar-brand-autodark">
            <h1 class="text-primary">SaaS Seeder</h1>
          </a>
          <div class="text-muted mt-2">Super Admin Development Tool</div>
        </div>

        <div class="card card-md">
          <div class="card-body">
            <div class="dev-warning">
              <strong>⚠️ Development Tool Only</strong>
              <p class="mb-0 mt-1">This page is for creating super admin users during development. Remove or restrict access in production!</p>
            </div>

            <h2 class="h2 text-center mb-4">Create Super Admin User</h2>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
              <div class="d-flex">
                <div>
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
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
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                    <path d="M9 12l2 2l4 -4"></path>
                  </svg>
                </div>
                <div><?php echo $success; ?></div>
              </div>
              <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            <?php endif; ?>

            <form action="./super-user-dev.php" method="POST" autocomplete="off">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label required">First Name</label>
                  <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" />
                </div>
                <div class="col-md-6">
                  <label class="form-label required">Last Name</label>
                  <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" />
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label required">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" autocomplete="off" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" />
                <small class="form-hint">This will be used to login</small>
              </div>

              <div class="mb-3">
                <label class="form-label required">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email address" autocomplete="off" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
              </div>

              <div class="mb-3">
                <label class="form-label required">Password</label>
                <div class="input-group input-group-flat">
                  <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" autocomplete="off" required />
                  <span class="input-group-text">
                    <a href="#" class="link-secondary" title="Show password" id="toggle-password">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                      </svg>
                    </a>
                  </span>
                </div>
                <small class="form-hint">Min 8 characters with uppercase, lowercase, number and special character</small>
              </div>

              <div class="mb-3">
                <label class="form-label required">Confirm Password</label>
                <div class="input-group input-group-flat">
                  <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm password" autocomplete="off" required />
                  <span class="input-group-text">
                    <a href="#" class="link-secondary" title="Show password" id="toggle-confirm-password">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                      </svg>
                    </a>
                  </span>
                </div>
              </div>

              <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">Create Super Admin</button>
              </div>
            </form>
          </div>
        </div>

        <div class="text-center text-secondary mt-3">
          Already have an account? <a href="./sign-in.php">Sign in</a>
        </div>

        <div class="text-center text-muted mt-3">
          <small>
            Password will be hashed using Argon2ID with salt and pepper
          </small>
        </div>
      </div>
    </div>

    <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
    <script src="./assets/tabler/js/tabler.min.js" defer></script>
    <!-- END GLOBAL MANDATORY SCRIPTS -->

    <!-- Password toggle scripts -->
    <script>
      // Toggle password visibility
      document.getElementById('toggle-password')?.addEventListener('click', function(e) {
        e.preventDefault();
        const passwordField = document.getElementById('password');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
      });

      // Toggle confirm password visibility
      document.getElementById('toggle-confirm-password')?.addEventListener('click', function(e) {
        e.preventDefault();
        const confirmPasswordField = document.getElementById('confirm_password');
        const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordField.setAttribute('type', type);
      });

      // Password match validation
      const password = document.getElementById('password');
      const confirmPassword = document.getElementById('confirm_password');

      confirmPassword?.addEventListener('input', function() {
        if (password.value !== confirmPassword.value) {
          confirmPassword.setCustomValidity('Passwords do not match');
        } else {
          confirmPassword.setCustomValidity('');
        }
      });

      password?.addEventListener('input', function() {
        if (confirmPassword.value && password.value !== confirmPassword.value) {
          confirmPassword.setCustomValidity('Passwords do not match');
        } else {
          confirmPassword.setCustomValidity('');
        }
      });
    </script>

    <?php endif; ?>
  </body>
</html>
