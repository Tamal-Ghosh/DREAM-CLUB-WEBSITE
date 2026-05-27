<?php
require_once __DIR__ . '/../backend/session.php';
if (!isLoggedIn() || $_SESSION['role'] !== 'volunteer') {
  header('Location: ../frontend/home.html');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Volunteer Dashboard | Dream</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/site-shell.css">
</head>
<body data-page="volunteer">
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
      <div class="hero-grid volunteer-hero-grid">
        <article class="hero-card">
          <div class="eyebrow">Volunteer control</div>
          <h2>Coordinate responses and help match donors to requests.</h2>
          <p>
            Volunteers can view requests, contact donors, and update statuses.
          </p>
          <div class="hero-actions">
            <a class="btn" href="#requests">View requests</a>
          </div>
        </article>
      </div>

      <div class="dashboard-grid full-width" style="margin-top:24px;">
        <section class="table-card" id="requests">
          <div class="panel-header">
            <div>
              <h3>Blood Request Management</h3>
              <p>Assign donors, then use Complete or Failed only after the request reaches Donor Assigned.</p>
            </div>
          </div>

          <div class="history-table-wrap">
            <table class="history-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Patient Name</th>
                  <th>Patient Number</th>
                  <th>Blood</th>
                  <th>Units</th>
                  <th>Hospital</th>
                  <th>Location</th>
                  <th>Status</th>
                  <th>Donor Name</th>
                  <th>Donor Phone</th>
                  <th>Assign Donor</th>
                  <th>Completed?</th>
                </tr>
              </thead>
              <tbody id="requestTableBody"></tbody>
            </table>
          </div>
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
  <script>
    (function () {
      const API = '../backend/request.php';
      const tbody = document.getElementById('requestTableBody');

      if (!tbody) return;

      const requestAction = async (action, requestId, extraData = {}) => {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('request_id', requestId);
        Object.entries(extraData).forEach(([key, value]) => formData.append(key, value));

        const response = await fetch(API, {
          method: 'POST',
          credentials: 'include',
          body: formData
        });
        return response.json();
      };

      const render = (requests) => {
        tbody.innerHTML = '';
        if (!requests.length) {
          const row = document.createElement('tr');
          row.innerHTML = '<td colspan="12" style="text-align:center; padding:18px;">No requests found</td>';
          tbody.appendChild(row);
          return;
        }

        requests.forEach((request) => {
          const status = request.status || 'Pending';
          const canComplete = status === 'Donor Assigned';
          const canAssign = !['Completed', 'Failed', 'Donor Assigned'].includes(status);
          const row = document.createElement('tr');
          row.dataset.requestId = request.id;
          row.innerHTML = `
            <td>${request.id}</td>
            <td>${request.patient_name || '—'}</td>
            <td>${request.contact_number || '—'}</td>
            <td>${request.blood_group || '—'}</td>
            <td>${request.units_required || '—'}</td>
            <td>${request.hospital || '—'}</td>
            <td>${request.location || '—'}</td>
            <td><span class="table-badge ${(status === 'Completed' || status === 'Donor Assigned') ? 'success' : 'pending'}">${status}</span></td>
            <td class="donor-name">${request.donor_name || '—'}</td>
            <td class="donor-phone">${request.donor_phone || '—'}</td>
            <td><button class="btn small assign-btn" type="button" ${canAssign ? '' : 'disabled'}>Assign Donor</button></td>
            <td class="table-actions">
              <button class="btn small complete-btn" type="button" ${canComplete ? '' : 'disabled'}>Complete</button>
              <button class="btn small failed-btn" type="button" ${canComplete ? '' : 'disabled'}>Failed</button>
            </td>
          `;

          row.querySelector('.assign-btn')?.addEventListener('click', async () => {
            const donorId = window.prompt('Enter donor user id');
            if (!donorId) return;
            const donorName = window.prompt('Enter donor name');
            if (!donorName) return;
            const donorPhone = window.prompt('Enter donor phone number') || '';

            try {
              const data = await requestAction('assign', request.id, {
                donor_id: donorId,
                donor_name: donorName,
                donor_phone: donorPhone
              });
              if (!data.success) {
                alert(data.message || 'Unable to assign request');
                return;
              }
              await refresh();
            } catch (error) {
              alert('Unable to assign request');
            }
          });

          row.querySelector('.complete-btn')?.addEventListener('click', async () => {
            try {
              const data = await requestAction('complete', request.id);
              if (!data.success) {
                alert(data.message || 'Unable to complete request');
                return;
              }
              await refresh();
            } catch (error) {
              alert('Unable to complete request');
            }
          });

          row.querySelector('.failed-btn')?.addEventListener('click', async () => {
            try {
              const data = await requestAction('failed', request.id);
              if (!data.success) {
                alert(data.message || 'Unable to mark request failed');
                return;
              }
              await refresh();
            } catch (error) {
              alert('Unable to mark request failed');
            }
          });

          tbody.appendChild(row);
        });
      };

      const refresh = async () => {
        try {
          const response = await fetch(`${API}?action=management-requests`, { credentials: 'include', cache: 'no-store' });
          const data = await response.json();
          render(data.success ? (data.requests || []) : []);
        } catch (error) {
          render([]);
        }
      };

      refresh();
      setInterval(refresh, 4000);
    })();
  </script>
</body>
</html>
