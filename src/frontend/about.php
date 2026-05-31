<?php
$pageTitle = 'About - Dream';
$bodyPage = 'about';
$headLinks = ['css/about.css'];

ob_start();
?>
  <section class="page-hero">
      <h1>About Dream</h1>
      <p>Dream is a blood donation club of Khulna University of Engineering and Technology. We connect donors, patients, and volunteers with a simple, trustworthy system built around urgent help and community care.</p>
      <div class="pill-row">
        <span class="pill">KUET blood donation club</span>
        <span class="pill">Emergency support</span>
        <span class="pill">Volunteer network</span>
        <a class="pill pill-link" href="our_team.php">Meet Our Team</a>
      </div>
    </section>

    <div class="page-grid">
      <section class="about-card">
        <h2>Our Mission</h2>
        <p>Our mission is to make blood donation support faster, clearer, and more human. We help people find the right support without making the process confusing or slow.</p>
      </section>

      <section class="about-card">
        <h2>What We Do</h2>
        <p>We organize donor, patient, and volunteer activity in one place so urgent requests can move quickly from need to response to completion.</p>
      </section>

      <section class="about-card">
        <h2>Our Values</h2>
        <ul>
          <li>Respect for every donor and patient</li>
          <li>Fast and transparent communication</li>
          <li>Community support built around trust</li>
        </ul>
      </section>

      <section class="about-card about-spotlight">
        <div>
          <h2>Why Dream Exists</h2>
          <p>Blood need is often urgent. Dream was created to help KUET students and the wider community respond with more speed, better coordination, and a shared sense of responsibility.</p>
        </div>
        <div>
          <strong>Focus areas</strong>
          <ul>
            <li>Donor awareness</li>
            <li>Patient support</li>
            <li>Volunteer coordination</li>
          </ul>
        </div>
      </section>
    </div>
<?php
$content = ob_get_clean();
require __DIR__ . '/before_login_master.php';
?>
