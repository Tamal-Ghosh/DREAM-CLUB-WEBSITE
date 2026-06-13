<?php
require_once __DIR__ . '/../backend/session.php';
$isLoggedIn = isLoggedIn() ? 'true' : 'false';
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';

$pageTitle = 'Dream';
$bodyPage = 'home';
$headLinks = ['css/home.css'];

ob_start();
?>
  <section class="hero">
      <h1>Donate Blood, Save Lives</h1>
      <p>Your small help can save someone's life</p>
      <div class="buttons">
        <a id="becomeDonorBtn" class="btn" href="/project_club/src/frontend/register.php">Become Donor</a>
        <a id="requestBloodBtn" class="btn outline" href="/project_club/src/frontend/patient_dashboard.php">Request Blood</a>
      </div>
    </section>

    <section class="gallery" aria-label="Club photo gallery">
      <div class="gallery-header">
        <h2>Club Photo Gallery</h2>
      </div>

      <div class="gallery-grid">
        <div class="gallery-item"><img src="../assets/img1.jpg" alt="Photo 1"></div>
        <div class="gallery-item"><img src="../assets/img2.jpg" alt="Photo 2"></div>
        <div class="gallery-item"><img src="../assets/img3.jpg" alt="Photo 3"></div>
        <div class="gallery-item"><img src="../assets/img4.jpg" alt="Photo 4"></div>
        <div class="gallery-item"><img src="../assets/img5.jpg" alt="Photo 5"></div>
        <div class="gallery-item"><img src="../assets/img6.jpg" alt="Photo 6"></div>
      </div>
    </section>

    <script>
      (function () {
        const loggedIn = <?= $isLoggedIn ?>;
        const userRole = '<?= htmlspecialchars($userRole, ENT_QUOTES) ?>';

        // Become Donor button logic
        document.getElementById('becomeDonorBtn')?.addEventListener('click', function (e) {
          if (loggedIn) {
            e.preventDefault();
            alert('You are already signed in.');
          }
        });

        // Request Blood button logic: Go to request page only if logged in as patient
        document.getElementById('requestBloodBtn')?.addEventListener('click', function (e) {
          const isAllowed = loggedIn && userRole === 'patient';
          if (!isAllowed) {
            e.preventDefault();
            window.location.href = '/project_club/src/frontend/login.php';
          }
        });
      })();
    </script>
<?php
$content = ob_get_clean();
require __DIR__ . '/before_login_master.php';
?>
