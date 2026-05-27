<?php
require_once __DIR__ . '/../backend/session.php';
if (!isLoggedIn() || !in_array($_SESSION['role'], ['patient','volunteer'])) {
  header('Location: ../frontend/home.html');
  exit;
}
?>
<?php
// Serve the same content as patient_dashboard.html
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Patient Dashboard | Dream</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/site-shell.css">
</head>
<body data-page="patient">
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
      <!-- Patient summary at the top -->

      <div class="hero-grid patient-hero-grid">
        <article class="hero-card">
          <div class="eyebrow">Patient support</div>

          <h2>Make a request and follow every update in one place.</h2>
          <p>
            Use the form below to send a blood request, then track the request status until a donor is found and blood is delivered.
          </p>
          <div class="hero-actions">
            <a class="btn" href="#create-request">Create request</a>
          </div>
        </article>
      </div>

      <div class="patient-grid">
        <section class="patient-card" id="create-request">
          <div class="panel-header">
            <div>
              <h3>Create Blood Request</h3>
              <p>Fill in the request details and submit it quickly.</p>
            </div>
          </div>

          <form class="request-form-grid" action="../backend/request.php" method="post">
            <div class="field full">
              <label for="patientName">Patient name</label>
              <input id="patientName" name="patientName" type="text" placeholder="Enter patient name" required>
            </div>

            <div class="field">
              <label for="contactNumber">Contact number</label>
              <input id="contactNumber" name="contactNumber" type="tel" placeholder="01XXXXXXXXX" required>
            </div>

            <div class="field">
              <label for="bloodGroup">Blood group needed</label>
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
              <label for="unitsNeeded">Units required</label>
              <input id="unitsNeeded" name="unitsNeeded" type="number" min="1" placeholder="e.g. 2" required>
            </div>

            <div class="field full">
              <label for="hospital">Hospital name</label>
              <input id="hospital" name="hospital" type="text" placeholder="Enter hospital name" required>
            </div>

            <div class="field full">
              <label for="location">Location</label>
              <input id="location" name="location" type="text" placeholder="District, thana, area">
            </div>

            <div class="field full">
              <label for="urgencyLevel">Urgency</label>
              <select id="urgencyLevel" name="urgencyLevel" required>
                <option value="" selected disabled>Select urgency</option>
                <option value="Normal">Normal</option>
                <option value="Urgent">Urgent</option>
                <option value="Critical">Critical</option>
              </select>
            </div>
          </form>
        </section>
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
