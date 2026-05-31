<?php
$pageTitle = 'Register | Dream';
$bodyPage = 'register';
$headLinks = ['css/register.css'];

ob_start();
?>
  <section class="register-shell">
    <section class="form-wrap" aria-label="Register form section">
      <form class="form-inner" action="/project_club/src/backend/register.php" method="post" autocomplete="on">
        <h2>Create Account</h2>
        <p class="subtitle">Fill in your details to get started.</p>

        <div class="field-grid">
          <div class="field">
            <label for="firstName">First Name</label>
            <input id="firstName" name="firstName" type="text" placeholder="John" required>
          </div>
          <div class="field">
            <label for="lastName">Last Name</label>
            <input id="lastName" name="lastName" type="text" placeholder="Doe" required>
          </div>
        </div>

        <div class="field">
          <label for="email">Email Address</label>
          <input id="email" name="email" type="email" placeholder="you@example.com" required>
        </div>

        <div class="field">
          <label for="role">Register As</label>
          <select id="role" name="role" required>
            <option value="" disabled>Select role</option>
            <option value="patient">Patient</option>
            <option value="donor" selected>Donor</option>
          </select>
        </div>

        <div class="field-grid">
          <div class="field">
            <label for="bloodGroup">Blood Group</label>
            <select id="bloodGroup" name="bloodGroup" required>
              <option value="" selected disabled>Select group</option>
              <option value="A+">A+</option>
              <option value="A-">A-</option>
              <option value="B+">B+</option>
              <option value="B-">B-</option>
              <option value="AB+">AB+</option>
              <option value="AB-">AB-</option>
              <option value="O+">O+</option>
              <option value="O-">O-</option>
            </select>
          </div>
          <div class="field">
            <label for="phone">Phone Number</label>
            <input id="phone" name="phone" type="tel" placeholder="0123456789" required>
          </div>
        </div>

        <div class="field-grid">
          <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Minimum 6 characters" required minlength="6">
          </div>
          <div class="field">
            <label for="confirmPassword">Confirm Password</label>
            <input id="confirmPassword" name="confirmPassword" type="password" placeholder="Repeat password" required minlength="6">
          </div>
        </div>

        <label class="check" for="terms">
          <input id="terms" name="terms" type="checkbox" required>
          <span>I agree to the Terms and Privacy Policy</span>
        </label>

        <button class="btn" type="submit">Create Account</button>

        <p class="hint">Already have an account? <a class="link" href="/project_club/src/frontend/login.php">Sign in</a></p>
      </form>
    </section>
  </section>
  <script>
    (function () {
      const params = new URLSearchParams(window.location.search);
      if (params.get('next') !== 'admin') {
        return;
      }

      const form = document.querySelector('.form-inner');
      if (form) {
        form.action = '/project_club/src/backend/register.php?next=admin';
      }
    })();
  </script>
<?php
$content = ob_get_clean();
require __DIR__ . '/before_login_master.php';
?>
