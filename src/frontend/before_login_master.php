<?php
// Guest/master layout: set $pageTitle, $bodyPage, $headLinks (array), $bodyScripts (array), $content
$pageTitle = $pageTitle ?? 'Dream';
$bodyPage = $bodyPage ?? '';
$headLinks = $headLinks ?? [];
$bodyScripts = $bodyScripts ?? [];

if (!is_array($headLinks)) $headLinks = [$headLinks];
if (!is_array($bodyScripts)) $bodyScripts = [$bodyScripts];
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
        <span></span>
        <span></span>
        <span></span>
      </button>
      <nav class="site-nav" aria-label="Primary navigation">
        <button class="menu-close-btn" id="menuCloseBtn" aria-label="Close menu">&times;</button>
        <a href="/project_club/src/frontend/home.php" data-page="home">Home</a>
        <a href="/project_club/src/frontend/about.php" data-page="about">About</a>
        <a href="/project_club/src/frontend/our_team.php" data-page="team">Our Team</a>
        <a href="/project_club/src/frontend/contact.php" data-page="contact">Contact</a>
        <a href="/project_club/src/frontend/login.php" data-page="login">Login</a>
      </nav>
    </div>
  </header>
  <main>
    <?= $content ?? '' ?>
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
  <?php foreach ($bodyScripts as $s): ?>
    <script src="<?= htmlspecialchars($s, ENT_QUOTES) ?>" defer></script>
  <?php endforeach; ?>
</body>
</html>
