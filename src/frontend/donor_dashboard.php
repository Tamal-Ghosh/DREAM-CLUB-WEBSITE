<?php
require_once __DIR__ . '/../backend/session.php';
if (!isLoggedIn() || !in_array($_SESSION['role'], ['donor','volunteer'])) {
  header('Location: ../frontend/home.html');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Donor Dashboard | Dream</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/site-shell.css">
</head>
<body data-page="donor">
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
    <!-- Top bar with logo and quick availability control -->

    <nav class="nav" aria-label="Dashboard sections">
      <a class="active" href="#overview">Overview</a>
      <a href="#requests">Requests</a>
      <a href="#schedule">Schedule</a>
      <a href="#history">History</a>
    </nav>

    <section class="dashboard-content" id="overview">
      <!-- First thing the donor sees -->
      <div class="hero-grid">
        <article class="hero-card">
          <div class="eyebrow">Hero status</div>
          <div class="status-line">
            <div>
              <span class="status-label">Blood group</span>
              <strong class="blood-group">A+</strong>
            </div>
            <div>
              <span class="status-label">Availability</span>
              <strong class="availability-badge available">Available</strong>
            </div>
            <div>
              <span class="status-label">Last donation</span>
              <strong>12 Apr 2026</strong>
            </div>
          </div>
          <h2>Your blood can help save lives today.</h2>
          <p>
            Stay updated with nearby urgent requests, upcoming donation dates, and your completed donation record.
          </p>
          <div class="hero-actions">
            <a class="btn" href="#requests">Donate now</a>
            <a class="btn secondary" href="#history">View history</a>
          </div>
        </article>

        <aside class="side-card">
          <h3>Donor actions</h3>
          <p>Fast access to your most important tasks.</p>
          <ul>
            <li>Update availability before responding to requests</li>
            <li>Check your local area for nearby urgent needs</li>
            <li>Keep contact and location details current</li>
          </ul>
        </aside>
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
