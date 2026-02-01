<?php
require_once __DIR__ . '/../src/config/auth.php';
require_once __DIR__ . '/../src/config/database.php';

// If already logged in, redirect to index
if (isLoggedIn()) {
    header('Location: ./index.php');
    exit();
}
?>
<!doctype html>
<html lang="en">
  <head>
    <?php include("./includes/head.php"); ?>
    <title>Forgot Password - Maduuka</title>
    <style>
      @import url('https://rsms.me/inter/inter.css');
      :root {
      	--tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
      }
      body {
      	font-feature-settings: "cv03", "cv04", "cv11";
      }
    </style>
  </head>
  <body class="d-flex flex-column">
    <script src="./dist/js/demo-theme.min.js?1692870487"></script>
    <div class="page page-center">
      <div class="container container-tight py-4">
        <div class="text-center mb-4">
          <a href="." class="navbar-brand navbar-brand-autodark"><img src="./dist/img/icons/logo.png" height="36" alt=""></a>
        </div>
        <form class="card card-md" id="forgotPasswordForm" autocomplete="off">
          <div class="card-body">
            <h2 class="card-title text-center mb-4">Forgot password</h2>
            <p class="text-secondary mb-4">Enter your email address or username and your password will be reset and emailed to you.</p>
            <div class="mb-3">
              <label class="form-label">Email address or Username</label>
              <input type="text" class="form-control" id="identifier" placeholder="Enter email or username" required>
            </div>
            <div class="form-footer">
              <button type="submit" class="btn btn-primary w-100">
                <!-- Download SVG icon from http://tabler-icons.io/i/mail -->
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>
                Send me new password
              </button>
            </div>
          </div>
        </form>
        <div class="text-center text-secondary mt-3">
          Forget it, <a href="./sign-in.php">send me back</a> to the sign in screen.
        </div>
      </div>
    </div>
    <!-- Libs JS -->
    <!-- Tabler Core -->
    <script src="./dist/js/tabler.min.js?1692870487" defer></script>
    <script src="./dist/js/demo.min.js?1692870487" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const identifier = document.getElementById('identifier').value;
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = 'Sending...';
            
            try {
                const response = await fetch('api/auth/request-password-reset.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ identifier: identifier })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Check your email',
                        text: data.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = './sign-in.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'Something went wrong. Please try again later.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    </script>
  </body>
</html>
