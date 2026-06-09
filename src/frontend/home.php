<?php
$pageTitle = 'Dream';
$bodyPage = 'home';
$headLinks = ['css/home.css'];

ob_start();
?>
  <section class="hero">
      <h1>Donate Blood, Save Lives</h1>
      <p>Your small help can save someone's life</p>
      <div class="buttons">
        <a class="btn" href="/project_club/src/frontend/register.php">Become Donor</a>
        <a class="btn outline" href="/project_club/src/frontend/request.php">Request Blood</a>
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
<?php
$content = ob_get_clean();
require __DIR__ . '/before_login_master.php';
?>
