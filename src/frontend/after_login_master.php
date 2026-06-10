<?php
require_once __DIR__ . '/../backend/session.php';

$pageTitle = $pageTitle ?? 'Dream';
$bodyPage = $bodyPage ?? '';
$headLinks = $headLinks ?? [];
$bodyScripts = $bodyScripts ?? [];
$wrapContentInMain = $wrapContentInMain ?? true;

if (!is_array($headLinks)) $headLinks = [$headLinks];
if (!is_array($bodyScripts)) $bodyScripts = [$bodyScripts];

$role = $_SESSION['role'] ?? 'public';
$dashboardHref = '/project_club/src/frontend/home.php';
switch ($role) {
  case 'donor': $dashboardHref = '/project_club/src/frontend/donor_dashboard.php'; break;
  case 'patient': $dashboardHref = '/project_club/src/frontend/patient_dashboard.php'; break;
  case 'admin': $dashboardHref = '/project_club/src/frontend/admin_dashboard.php'; break;
  case 'volunteer': $dashboardHref = '/project_club/src/frontend/volunteer_dashboard.php'; break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/site-shell.css">
  <?php foreach ($headLinks as $h): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($h, ENT_QUOTES) ?>">
  <?php endforeach; ?>
</head>
<body<?php if ($bodyPage) echo ' data-page="'.htmlspecialchars($bodyPage, ENT_QUOTES).'"'; ?> class="has-site-shell">
  <header class="site-header">
    <div class="site-nav-overlay" id="siteNavOverlay"></div>
    <div class="site-shell-inner">
      <a class="site-brand" href="/project_club/src/frontend/home.php" aria-label="Dream home">
        <div class="site-brand-logos" aria-hidden="true">
          <img class="site-brand-logo" src="../assets/logo.jpg" alt="">
          <img class="site-brand-logo contain" src="../assets/logoKuet.png" alt="">
        </div>
        <div class="site-brand-copy">
          <strong>Dream</strong>
          <span>Blood donation support network</span>
        </div>
      </a>
      <button class="menu-toggle-btn" id="menuToggleBtn" aria-label="Toggle navigation" aria-expanded="false">
        &#8942;
      </button>
      <nav class="site-nav" aria-label="Primary navigation">
        <button class="menu-close-btn" id="menuCloseBtn" aria-label="Close menu">&times;</button>
        <a href="/project_club/src/frontend/home.php" data-page="home">Home</a>
        <a href="/project_club/src/frontend/about.php" data-page="about">About</a>
        <a href="/project_club/src/frontend/our_team.php" data-page="team">Our Team</a>
        <a href="/project_club/src/frontend/contact.php" data-page="contact">Contact</a>
        <a href="<?= htmlspecialchars($dashboardHref, ENT_QUOTES) ?>" class="dashboard-link" data-page="<?= htmlspecialchars($role, ENT_QUOTES) ?>">Dashboard</a>
        <a href="/project_club/src/frontend/profile.php" class="profile-link" data-page="profile">Profile</a>
        <a href="/project_club/src/backend/logout.php" id="logoutBtn">Sign out</a>
      </nav>
    </div>
  </header>
  <?php if ($wrapContentInMain): ?>
    <main>
      <?= $content ?? '' ?>
    </main>
  <?php else: ?>
    <?= $content ?? '' ?>
  <?php endif; ?>

  <footer class="site-footer">
    <div class="site-shell-inner">
      <div class="site-brand-copy">
        <strong>Dream</strong>
        <span>© 2026 Dream. Stay connected, stay ready.</span>
      </div>
    </div>
  </footer>

  <script src="js/site-shell.js" defer></script>
  <?php foreach ($bodyScripts as $s): ?>
    <script src="<?= htmlspecialchars($s, ENT_QUOTES) ?>" defer></script>
  <?php endforeach; ?>
</body>
</html>
