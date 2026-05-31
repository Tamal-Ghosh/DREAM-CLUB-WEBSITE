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
        <p>Add your best club photos here.</p>
      </div>

      <div class="gallery-grid">
        <div class="gallery-item">Photo 1</div>
        <div class="gallery-item">Photo 2</div>
        <div class="gallery-item">Photo 3</div>
        <div class="gallery-item">Photo 4</div>
        <div class="gallery-item">Photo 5</div>
        <div class="gallery-item">Photo 6</div>
      </div>
    </section>
<?php
$content = ob_get_clean();
require __DIR__ . '/before_login_master.php';
?>
