<?php
$pageTitle = 'Contact - Dream';
$bodyPage = 'contact';
$headLinks = ['css/contact.css'];

ob_start();
?>
  <section class="page-hero">
      <h1>Get In Touch</h1>
      <p>If you need blood support, want to volunteer, or want to connect with Dream, use the details below. We keep contact simple and direct so help is easy to reach.</p>
    </section>

    <div class="contact-grid">
      <section class="contact-card">
        <h2>Official Contact</h2>
        <div class="contact-list">
          <div class="contact-item">
            <strong>Contact Person</strong>
            <span>Md. Arif Hossain, Coordinator</span>
          </div>
          <div class="contact-item">
            <strong>Phone</strong>
            <span>+880 1712-345678</span>
          </div>
          <div class="contact-item">
            <strong>Email</strong>
            <span><a href="mailto:hello@dreamkuet.org">hello@dreamkuet.org</a></span>
          </div>
        </div>
      </section>

      <section class="contact-card contact-highlight">
        <h2>Dream at KUET</h2>
        <p class="contact-note">Dream is a blood donation club of Khulna University of Engineering and Technology. We work with students and volunteers to support urgent blood needs in a fast and organized way.</p>
        <div class="contact-badge-row">
          <span class="badge">KUET campus support</span>
          <span class="badge">Blood donor coordination</span>
          <span class="badge">Emergency response</span>
        </div>
        <div class="contact-list">
          <div class="contact-item">
            <strong>Office Address</strong>
            <span>Dream Club Room, KUET, Khulna, Bangladesh</span>
          </div>
          <div class="contact-item">
            <strong>Emergency Line</strong>
            <span>+880 1811-112233</span>
          </div>
          <div class="contact-item">
            <strong>Hours</strong>
            <span>Every day, 9:00 AM - 9:00 PM</span>
          </div>
        </div>
      </section>
    </div>
<?php
$content = ob_get_clean();
require __DIR__ . '/before_login_master.php';
?>
