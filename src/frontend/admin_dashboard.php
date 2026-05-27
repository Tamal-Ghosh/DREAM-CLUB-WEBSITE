<?php
require_once __DIR__ . '/../backend/session.php';
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
  header('Location: ../frontend/home.html');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Dream</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/site-shell.css">
</head>
<body data-page="admin">
  <header class="site-header">
    <div class="site-shell-inner">
      <a class="site-brand" href="home.html" aria-label="Dream home">
        <div class="site-brand-logos" aria-hidden="true">
          <img class="site-brand-logo" src="../assets/logo.jpg" alt="">
          <img class="site-brand-logo contain" src="../assets/logoKuet.png" alt="">
        </div>
        <div class="site-brand-copy">
          <strong>Dream</strong>
          <span>Blood donation support network</span>
        </div>
      </a>
      <nav class="site-nav" aria-label="Primary navigation">
        <a href="home.html" data-page="home">Home</a>
        <a href="about.html" data-page="about">About</a>
        <a href="our_team.html" data-page="team">Our Team</a>
        <a href="contact.html" data-page="contact">Contact</a>
        <a href="login.html" data-page="login">Login</a>
      </nav>
    </div>
  </header>
  <main class="dashboard-shell">
    <section class="dashboard-content" id="overview">
      <div class="hero-grid admin-hero-grid">
        <article class="hero-card">
          <div class="eyebrow">Superuser control</div>
          <h2>Manage the full blood donation system.</h2>
          <p>
            Monitor users and blood requests from one place.
          </p>
          <div class="hero-actions">
            <a class="btn" href="#users">View users</a>
          </div>
        </article>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="site-shell-inner">
      <div class="site-brand-copy">
        <strong>Dream</strong>
        <span>© 2026 Dream. Stay connected, stay ready.</span>
      </div>
    </div>
  </footer>

  <script src="js/site-shell.js" defer></script>
</body>
</html>
