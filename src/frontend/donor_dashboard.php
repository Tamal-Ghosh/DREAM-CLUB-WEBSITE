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
        <a href="donor_dashboard.php" class="dashboard-link" data-page="donor">Dashboard</a>
        <a href="profile.php" class="profile-link" data-page="profile">Profile</a>
      </nav>
    </div>
  </header>
  <main class="dashboard-shell">
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
              <span class="status-label">Last donation</span>
              <strong id="lastDonationValue"></strong>
            </div>
            <div>
              <span class="status-label">Completed donations</span>
              <strong id="completedDonationsValue">0</strong>
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

        <section class="side-card" id="requests">
          <h3>Assigned Requests</h3>
          <p>Requests assigned by admin or volunteer. Accept a request to update its status.</p>

          <div class="requests-list" aria-live="polite"></div>
          <p class="requests-empty" style="display:none; color:#7a5959;">No incoming requests</p>
        </section>

        <!-- Donation history panel remains available under the requests section -->
        <section class="side-card" id="history">
          <h3>Donation History</h3>
          <p>Recent donations and status updates. If there are no records you'll see a message.</p>

          <div class="history-list" aria-live="polite">
            <!-- history items will be injected here -->
          </div>
          <p class="history-empty" style="display:none; color:#7a5959;">No previous history</p>
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
      const requestList = document.querySelector('.requests-list');
      const requestsEmptyEl = document.querySelector('.requests-empty');
      const listWrap = document.querySelector('.history-list');
      const emptyEl = document.querySelector('.history-empty');
      const lastDonationValue = document.getElementById('lastDonationValue');
      const completedDonationsValue = document.getElementById('completedDonationsValue');

      const formatDate = (value) => {
        if (!value) return '—';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? '—' : date.toLocaleDateString();
      };

      const renderRequests = (requests) => {
        const entries = requests.filter((item) => {
          const status = item.status || 'Pending';
          return status === 'Donor Review' || status === 'Donor Assigned';
        });
        requestList.innerHTML = '';

        if (!entries.length) {
          requestsEmptyEl.style.display = '';
          return;
        }

        requestsEmptyEl.style.display = 'none';
        entries.forEach((info) => {
          const requestId = info.id;
          const status = info.status || 'Pending';
          const statusClass = (status === 'Donor Assigned' || status === 'Completed') ? 'success' : 'pending';
          const item = document.createElement('article');
          item.className = 'request-item';
          item.style.padding = '12px 10px';
          item.style.borderBottom = '1px solid rgba(0,0,0,0.06)';
          item.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
              <div>
                <div style="font-weight:700; font-size:1rem;">Request ${requestId}</div>
                <div style="margin-top:6px; color:#6b4c45;">${info.blood_group || '—'} · ${info.hospital || '—'}</div>
                <div style="margin-top:6px; color:#7b5f58; font-size:0.92rem;">Patient: ${info.patient_name || '—'}</div>
                <div style="margin-top:4px; color:#7b5f58; font-size:0.92rem;">Location: ${info.location || '—'}</div>
                <div style="margin-top:4px; color:#7b5f58; font-size:0.92rem;">Units required: ${info.units_required || '—'}</div>
                <div style="margin-top:4px; color:#7b5f58; font-size:0.92rem;">Contact: ${info.contact_number || '—'}</div>
              </div>
              <div style="text-align:right; min-width:120px;">
                <div><span class="table-badge ${statusClass}">${status}</span></div>
                <div style="display:flex;flex-direction:column;gap:8px;margin-top:10px;">
                  <button class="btn small accept-request-btn" type="button" data-request-id="${requestId}">Accept</button>
                  <button class="btn small secondary reject-request-btn" type="button" data-request-id="${requestId}">Reject</button>
                </div>
              </div>
            </div>
          `;

          const acceptBtn = item.querySelector('.accept-request-btn');
          if (acceptBtn) {
            const accepted = status === 'Donor Assigned' || status === 'Completed';
            if (accepted) {
              acceptBtn.textContent = 'Accepted';
              acceptBtn.disabled = true;
            } else {
              acceptBtn.addEventListener('click', () => {
                acceptRequest(requestId);
              });
            }
          }

          const rejectBtn = item.querySelector('.reject-request-btn');
          if (rejectBtn) {
            rejectBtn.addEventListener('click', () => {
              if (!confirm('Are you sure you want to reject this assignment? This will remove the donor and return the request to Pending.')) return;
              rejectRequest(requestId);
            });
          }

          requestList.appendChild(item);
        });
      };

      const renderHistory = (requests) => {
        const items = requests.filter((item) => (item.status || 'Pending') === 'Completed');
        const completedCount = requests.filter((item) => (item.status || 'Pending') === 'Completed').length;
        if (completedDonationsValue) {
          completedDonationsValue.textContent = String(completedCount);
        }

        if (lastDonationValue) {
          const lastCompleted = requests
            .filter((item) => (item.status || 'Pending') === 'Completed' && item.created_at)
            .sort((a, b) => new Date((b.updated_at || b.created_at)) - new Date((a.updated_at || a.created_at)))[0];
          lastDonationValue.textContent = lastCompleted ? formatDate(lastCompleted.updated_at || lastCompleted.created_at) : '';
        }

        listWrap.innerHTML = '';
        if (!items.length) {
          emptyEl.style.display = '';
          return;
        }
        emptyEl.style.display = 'none';
        items.forEach((it) => {
          const el = document.createElement('div');
          el.className = 'history-item';
          el.style.padding = '12px 10px';
          el.style.borderBottom = '1px solid rgba(0,0,0,0.04)';
          el.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
              <div>
                <div style="font-weight:700">${it.patient_name || '—'}</div>
                <div style="font-size:0.9rem;color:#666">${it.blood_group || '—'} · ${it.hospital || ''}</div>
                <div style="margin-top:4px;color:#666;font-size:0.9rem;">Location: ${it.location || '—'}</div>
                <div style="margin-top:4px;color:#666;font-size:0.9rem;">Units required: ${it.units_required || '—'}</div>
                <div style="margin-top:4px;color:#666;font-size:0.9rem;">Contact: ${it.contact_number || '—'}</div>
              </div>
              <div style="text-align:right">
                <div style="font-size:0.9rem;color:#666">${formatDate(it.updated_at || it.created_at)}</div>
                <div style="margin-top:6px"><span class="table-badge success">${it.status}</span></div>
              </div>
            </div>
            <div style="margin-top:8px;color:#444">Donor: ${it.donor_name || '—'} · ${it.donor_phone || '—'}</div>
          `;
          listWrap.appendChild(el);
        });
      };

      const acceptRequest = async (requestId) => {
        try {
          const formData = new FormData();
          formData.append('action', 'accept');
          formData.append('request_id', requestId);

          const response = await fetch(API, {
            method: 'POST',
            credentials: 'include',
            body: formData
          });
          const data = await response.json();
          if (!data.success) {
            alert(data.message || 'Unable to accept request');
            return;
          }
          await refresh();
        } catch (error) {
          alert('Unable to accept request');
        }
      };

      const rejectRequest = async (requestId) => {
        try {
          const formData = new FormData();
          formData.append('action', 'reject');
          formData.append('request_id', requestId);

          const response = await fetch(API, {
            method: 'POST',
            credentials: 'include',
            body: formData
          });
          const data = await response.json();
          if (!data.success) {
            alert(data.message || 'Unable to reject request');
            return;
          }
          await refresh();
        } catch (error) {
          alert('Unable to reject request');
        }
      };

      const refresh = async () => {
        try {
          const response = await fetch(`${API}?action=assigned-requests`, {
            credentials: 'include',
            cache: 'no-store'
          });
          const data = await response.json();
          if (!data.success) {
            renderRequests([]);
            renderHistory([]);
            return;
          }
          const requests = data.requests || [];
          renderRequests(requests);
          renderHistory(requests);
        } catch (error) {
          renderRequests([]);
          renderHistory([]);
        }
      };

      refresh();
      setInterval(refresh, 4000);

    })();
  </script>
</body>
</html>
