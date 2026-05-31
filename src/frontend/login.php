<?php
require_once __DIR__ . '/../backend/session.php';

// Prevent caching of the login page so back-navigation re-validates session
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Vary: Cookie');

// If already logged in, redirect to the appropriate dashboard
if (isLoggedIn()) {
  $role = $_SESSION['role'] ?? 'public';
  switch ($role) {
    case 'donor':
      $target = '/project_club/src/frontend/donor_dashboard.php';
      break;
    case 'patient':
      $target = '/project_club/src/frontend/patient_dashboard.php';
      break;
    case 'admin':
      $target = '/project_club/src/frontend/admin_dashboard.php';
      break;
    case 'volunteer':
      $target = '/project_club/src/frontend/volunteer_dashboard.php';
      break;
    default:
      $target = '/project_club/src/frontend/home.php';
  }
  header('Location: ' . $target);
  exit;
}
?>
<?php
$pageTitle = 'Login | Dream';
$bodyPage = 'login';
$headLinks = ['css/login.css'];

ob_start();
?>
  <section class="login-shell">
    <section class="form-wrap" aria-label="Login form section">
      <form class="form-inner" action="/project_club/src/backend/login.php" method="post" autocomplete="on">
        <h2>Sign In</h2>
        <p class="subtitle">Use your member account credentials.</p>

        <div class="field">
          <label for="email">Email Address</label>
          <input id="email" name="email" type="email" placeholder="you@example.com" required>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" placeholder="Enter your password" required minlength="6">
        </div>

        <div class="row">
          <label class="check" for="remember">
            <input id="remember" name="remember" type="checkbox">
            <span>Remember me</span>
          </label>
          <a class="link" href="#">Forgot password?</a>
        </div>

        <button class="btn" type="submit">Log In</button>

        <p class="hint">New here? <a class="link" href="/project_club/src/frontend/register.php">Create an account</a></p>
        <p class="safe">Secured connection</p>
      </form>
    </section>
  </section>
<?php
$content = ob_get_clean();
require __DIR__ . '/before_login_master.php';
?>
