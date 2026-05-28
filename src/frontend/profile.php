<?php
require_once __DIR__ . '/../backend/session.php';

if (!isLoggedIn()) {
  header('Location: ../frontend/home.html');
  exit;
}

$role = $_SESSION['role'] ?? 'public';
$dashboardHref = 'home.html';
switch ($role) {
  case 'donor':
    $dashboardHref = 'donor_dashboard.php';
    break;
  case 'patient':
    $dashboardHref = 'patient_dashboard.php';
    break;
  case 'admin':
    $dashboardHref = 'admin_dashboard.php';
    break;
  case 'volunteer':
    $dashboardHref = 'volunteer_dashboard.php';
    break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile | Dream</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/site-shell.css">
</head>
<body data-page="profile" class="has-site-shell">
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
        <a href="<?= htmlspecialchars($dashboardHref, ENT_QUOTES) ?>" class="dashboard-link" data-page="<?= htmlspecialchars($role, ENT_QUOTES) ?>">Dashboard</a>
        <a href="profile.php" class="profile-link" data-page="profile">Profile</a>
      </nav>
    </div>
  </header>

  <main class="dashboard-shell">
    <section class="dashboard-content" id="overview">
      <div class="hero-grid admin-hero-grid">
        <article class="hero-card">
          <h2>Your profile information</h2>

      <section class="table-card admin-wide profile-section" id="profile" style="margin-top:24px;">
        <div class="panel-header">
          <div>
            <h3>My Profile</h3>
            <p>Review your account details.</p>
            <p style="margin-top:6px;color:#7d524c;">Registered on: <strong id="profileRegistered">—</strong></p>
          </div>
          <div style="display:flex;align-items:center;gap:12px;">
            <button id="profileEditBtn" class="btn small" type="button">Edit</button>
            <div class="profile-role-tag" id="profileRole"><?= htmlspecialchars(ucfirst($role), ENT_QUOTES) ?></div>
          </div>
        </div>

        <div class="profile-readonly" id="profileReadOnly">
          <div class="detail-item">
            <div>
              <div class="status-label">Full name</div>
              <div class="value" id="profileNameView">—</div>
            </div>
          </div>
          <div class="detail-item">
            <div>
              <div class="status-label">Email</div>
              <div class="value" id="profileEmailView">—</div>
            </div>
          </div>

          <div class="detail-item">
            <div>
              <div class="status-label">Phone</div>
              <div class="value" id="profilePhoneView">—</div>
            </div>
          </div>
          <div class="detail-item">
            <div>
              <div class="status-label">Blood group</div>
              <div class="value" id="profileBloodGroupView">—</div>
            </div>
          </div>

          <div class="detail-item" id="profileAvailabilityViewWrap">
            <div>
              <div class="status-label">Availability</div>
              <div class="value" id="profileAvailabilityView">—</div>
            </div>
          </div>
        </div>

        <form id="profileForm" class="profile-form" style="display:none;">
          <div class="profile-grid">
            <div class="profile-field">
              <label for="profileName">Full name</label>
              <input id="profileName" type="text" placeholder="Full name" required>
            </div>
            <div class="profile-field">
              <label for="profileEmail">Email</label>
              <input id="profileEmail" type="email" placeholder="Email" required>
            </div>
            <div class="profile-field">
              <label for="profilePhone">Phone</label>
              <input id="profilePhone" type="text" placeholder="Phone number">
            </div>
            <div class="profile-field">
              <label for="profileBloodGroup">Blood group</label>
              <input id="profileBloodGroup" type="text" placeholder="Blood group">
            </div>
            <div class="profile-field" data-profile-only="donor">
              <label for="profileAvailability">Availability</label>
              <select id="profileAvailability">
                <option value="Available">Available</option>
                <option value="Unavailable">Unavailable</option>
              </select>
            </div>
            <div class="profile-field profile-wide">
              <label for="profileOldPassword">Current password</label>
              <input id="profileOldPassword" type="password" placeholder="Required only when changing password">
            </div>
            <div class="profile-field profile-wide">
              <label for="profilePassword">New password</label>
              <input id="profilePassword" type="password" placeholder="Leave blank to keep current password">
            </div>
          </div>

          <div class="profile-actions">
            <p class="profile-note" id="profileMessage">Loading profile...</p>
            <div style="display:flex;gap:10px;align-items:center;">
              <button class="btn small user-action-btn" type="submit">Save</button>
              <button class="btn small secondary" id="profileCancelBtn" type="button">Cancel</button>
            </div>
          </div>
        </form>
      </section>
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
  <script src="js/profile-section.js" defer></script>
</body>
</html>
