<?php
$pageTitle = 'Request Blood - Dream';
$bodyPage = 'request';
$headLinks = ['css/request.css'];

ob_start();
?>
  <section class="request-hero">
      <h1>Request Blood</h1>
      <p>Share the urgent details below so the Dream team can understand the need quickly and help coordinate support.</p>
    </section>

    <div class="request-grid">
      <section class="request-card">
        <h2>Request Form</h2>
        <form class="request-form" action="/project_club/src/backend/request.php" method="post">
          <div class="request-field">
            <label for="patientName">Patient Name</label>
            <input id="patientName" name="patientName" type="text" placeholder="Enter patient name" required>
          </div>
          <div class="request-field">
            <label for="bloodGroup">Blood Group</label>
            <select id="bloodGroup" name="bloodGroup" required>
              <option value="" selected disabled>Select blood group</option>
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
          <div class="request-field">
            <label for="hospital">Hospital</label>
            <input id="hospital" name="hospital" type="text" placeholder="Hospital name" required>
          </div>
          <div class="request-field">
            <label for="location">Location</label>
            <input id="location" name="location" type="text" placeholder="Area, city, or address">
          </div>
          <div class="request-field">
            <label for="contactNumber">Contact Number</label>
            <input id="contactNumber" name="contactNumber" type="tel" placeholder="01XXXXXXXXX" required>
          </div>
          <div class="request-field">
            <label for="unitsNeeded">Units Needed</label>
            <input id="unitsNeeded" name="unitsNeeded" type="number" min="1" step="1" placeholder="Enter number of units" required>
          </div>
          <div class="request-field">
            <label for="urgencyLevel">Urgency Level</label>
            <select id="urgencyLevel" name="urgencyLevel" required>
              <option value="" selected disabled>Select urgency</option>
              <option value="Normal">Normal</option>
              <option value="Urgent">Urgent</option>
              <option value="Critical">Critical</option>
            </select>
          </div>
          <div class="request-field">
            <label for="details">Additional Details</label>
            <textarea id="details" name="details" placeholder="Write urgency, unit needed, and any other details"></textarea>
          </div>
          <div class="request-actions">
            <button class="request-btn" type="submit">Submit Request</button>
          </div>
        </form>
      </section>

      <aside class="request-card">
        <h2>Before You Submit</h2>
        <div class="request-note-list">
          <div class="request-note">Keep the contact number active so donors or coordinators can reach you quickly.</div>
          <div class="request-note">Add the exact blood group and location to reduce delays.</div>
          <div class="request-note">If it is urgent, mention the deadline and hospital ward in the notes.</div>
        </div>
      </aside>
    </div>
<?php
$content = ob_get_clean();
require __DIR__ . '/before_login_master.php';
?>
