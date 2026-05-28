<?php
require_once __DIR__ . '/../backend/session.php';

if (!isLoggedIn() || !in_array($_SESSION['role'] ?? '', ['patient', 'volunteer'], true)) {
  header('Location: ../frontend/home.html');
  exit;
}

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
  <link rel="stylesheet" href="css/request.css">
  <link rel="stylesheet" href="css/site-shell.css">
</head>
<body data-page="patient" class="has-site-shell">
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
        <a href="patient_dashboard.php" class="dashboard-link" data-page="patient">Dashboard</a>
        <a href="profile.php" class="profile-link" data-page="profile">Profile</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="request-hero">
      <h1>Patient Dashboard</h1>
      <p>
        Submit a blood request, then track its status below. This page stays connected to the backend so your latest requests
        refresh automatically after login.
      </p>
    </section>

    <div class="request-grid">
      <section class="request-card">
        <h2>New Request</h2>
        <form class="request-form-grid">
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

    <section id="my-requests" class="request-card" style="margin-top:20px;">
      <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;">
        <div>
          <h2>My Requests</h2>
          <p style="margin-top:6px;color:#7d524c;">Latest requests refresh automatically while you stay on the page.</p>
        </div>
      </div>

      <div class="request-card-list" aria-live="polite" style="margin-top:16px;"></div>
      <p class="empty-state" style="display:none; color:#7d524c; margin-top:12px;">No requests found yet.</p>
    </section>

    <dialog id="editRequestDialog" class="edit-user-modal request-edit-dialog">
      <form id="editRequestForm" class="edit-user-modal-panel request-edit-dialog-panel" method="dialog">
        <div class="panel-header" style="margin-bottom:18px;">
          <div>
            <h2>Edit Request</h2>
            <p style="margin-top:6px;color:#7d524c;">You can only edit requests while the status is Pending.</p>
          </div>
          <div class="table-badge pending" id="editRequestStatus">Pending</div>
        </div>

        <input type="hidden" id="editRequestId" name="request_id">

        <div class="edit-user-grid request-form-grid">
          <div class="request-field">
            <label for="editPatientName">Patient Name</label>
            <input id="editPatientName" name="patientName" type="text" required>
          </div>
          <div class="request-field">
            <label for="editBloodGroup">Blood Group</label>
            <select id="editBloodGroup" name="bloodGroup" required>
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
            <label for="editHospital">Hospital</label>
            <input id="editHospital" name="hospital" type="text" required>
          </div>
          <div class="request-field">
            <label for="editLocation">Location</label>
            <input id="editLocation" name="location" type="text">
          </div>
          <div class="request-field">
            <label for="editContactNumber">Contact Number</label>
            <input id="editContactNumber" name="contactNumber" type="tel" required>
          </div>
          <div class="request-field">
            <label for="editUnitsNeeded">Units Needed</label>
            <input id="editUnitsNeeded" name="unitsNeeded" type="number" min="1" step="1" required>
          </div>
          <div class="request-field">
            <label for="editUrgencyLevel">Urgency Level</label>
            <select id="editUrgencyLevel" name="urgencyLevel" required>
              <option value="Normal">Normal</option>
              <option value="Urgent">Urgent</option>
              <option value="Critical">Critical</option>
            </select>
          </div>
          <div class="request-field">
            <label for="editDetails">Additional Details</label>
            <textarea id="editDetails" name="details"></textarea>
          </div>
        </div>

        <div class="edit-user-actions request-actions" style="margin-top:18px; justify-content:flex-end;">
          <button type="button" class="request-btn" id="cancelEditRequestBtn" style="background:#9b7a6f;">Cancel</button>
          <button type="submit" class="request-btn">Save Changes</button>
        </div>
      </form>
    </dialog>
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
      const form = document.querySelector('form.request-form-grid');
      const listWrap = document.querySelector('.request-card-list');
      const emptyEl = document.querySelector('.empty-state');
      const patientNameInput = document.getElementById('patientName');
      const initialPatientName = patientNameInput ? patientNameInput.value : '';
      const editDialog = document.getElementById('editRequestDialog');
      const editForm = document.getElementById('editRequestForm');
      const editRequestId = document.getElementById('editRequestId');
      const editRequestStatus = document.getElementById('editRequestStatus');
      const editPatientName = document.getElementById('editPatientName');
      const editBloodGroup = document.getElementById('editBloodGroup');
      const editHospital = document.getElementById('editHospital');
      const editLocation = document.getElementById('editLocation');
      const editContactNumber = document.getElementById('editContactNumber');
      const editUnitsNeeded = document.getElementById('editUnitsNeeded');
      const editUrgencyLevel = document.getElementById('editUrgencyLevel');
      const editDetails = document.getElementById('editDetails');
      const cancelEditRequestBtn = document.getElementById('cancelEditRequestBtn');
      let currentEditRequest = null;

      const formatDate = (value) => {
        if (!value) return '—';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? '—' : date.toLocaleDateString();
      };

      const render = (items) => {
        if (!listWrap || !emptyEl) return;

        listWrap.innerHTML = '';
        if (!items.length) {
          emptyEl.style.display = '';
          return;
        }

        emptyEl.style.display = 'none';
        items.forEach((it) => {
          const status = it.status || 'Pending';
          const statusClass = (status === 'Completed' || status === 'Donor Assigned') ? 'success' : 'pending';
          const donorName = it.donor_name || '—';
          const donorPhone = it.donor_phone || '—';
          const createdAt = it.created_at || it.created || null;
          const canEdit = status === 'Pending';

          // Only show donor info to the patient when the request has been donor-assigned or completed
          const showDonorToPatient = status === 'Donor Assigned' || status === 'Completed';
          const donorBlock = showDonorToPatient ? `
            <div class="record-meta">
              <div class="donor-info" style="margin-top:8px;">
                <strong class="donor-name">Donor: ${donorName}</strong>
                <div class="donor-phone">Phone: ${donorPhone}</div>
              </div>
            </div>
          ` : '';

          const article = document.createElement('article');
          article.className = 'request-record';
          article.setAttribute('data-request', it.id);
          article.innerHTML = `
            <div class="request-record-top">
              <div>
                <span class="request-record-id">Request ID: ${it.id}</span>
                <div class="record-meta" style="margin-top:8px;">
                  <strong>${it.patient_name || ''}</strong>
                  <span>Hospital: ${it.hospital || '—'}</span>
                  <span>Location: ${it.location || '—'}</span>
                  <span>Units: ${it.units_required || '—'}</span>
                </div>
              </div>
              <div style="text-align:right;">
                <div style="font-size:0.95rem; margin-bottom:6px; font-weight:700;">${it.blood_group || '—'}</div>
                <div style="font-size:0.85rem; color:#666; margin-bottom:6px;">${formatDate(createdAt)}</div>
                <div><span class="table-badge ${statusClass}">${status}</span></div>
                <div class="request-actions" style="justify-content:flex-end; margin-top:10px;">
                  <button type="button" class="request-btn edit-request-btn" data-request-id="${it.id}" ${canEdit ? '' : 'disabled'} style="${canEdit ? '' : 'opacity:0.55; cursor:not-allowed;'}">Edit</button>
                </div>
              </div>
            </div>
            ${donorBlock}
          `;

          if (canEdit) {
            const editBtn = article.querySelector('.edit-request-btn');
            if (editBtn) {
              editBtn.addEventListener('click', () => openEditDialog(it));
            }
          }

          listWrap.appendChild(article);
        });
      };

      const openEditDialog = (request) => {
        currentEditRequest = request;
        if (!editDialog || !editForm) return;

        if (editRequestId) editRequestId.value = request.id || '';
        if (editRequestStatus) {
          editRequestStatus.textContent = request.status || 'Pending';
          editRequestStatus.classList.remove('success');
          editRequestStatus.classList.add('pending');
        }
        if (editPatientName) editPatientName.value = request.patient_name || '';
        if (editBloodGroup) editBloodGroup.value = request.blood_group || 'A+';
        if (editHospital) editHospital.value = request.hospital || '';
        if (editLocation) editLocation.value = request.location || '';
        if (editContactNumber) editContactNumber.value = request.contact_number || '';
        if (editUnitsNeeded) editUnitsNeeded.value = request.units_required || 1;
        if (editUrgencyLevel) editUrgencyLevel.value = request.urgency || 'Normal';
        if (editDetails) editDetails.value = request.details || '';

        if (typeof editDialog.showModal === 'function') {
          editDialog.showModal();
        } else {
          editDialog.setAttribute('open', 'open');
        }
      };

      const closeEditDialog = () => {
        currentEditRequest = null;
        if (!editDialog) return;
        if (typeof editDialog.close === 'function') {
          editDialog.close();
        } else {
          editDialog.removeAttribute('open');
        }
      };

      const loadRequests = async () => {
        try {
          const response = await fetch(`${API}?action=patient-history`, {
            credentials: 'include',
            cache: 'no-store'
          });
          const data = await response.json();
          render(data.success ? (data.requests || []) : []);
        } catch (error) {
          render([]);
        }
      };

      if (form) {
        form.addEventListener('submit', async (event) => {
          event.preventDefault();
          const formData = new FormData(form);
          formData.append('action', 'create');

          try {
            const response = await fetch(API, {
              method: 'POST',
              credentials: 'include',
              body: formData
            });
            const data = await response.json();
            if (!data.success) {
              alert(data.message || 'Unable to submit request');
              return;
            }

            form.reset();
            if (patientNameInput) {
              patientNameInput.value = initialPatientName;
            }
            location.hash = '#my-requests';
            await loadRequests();
          } catch (error) {
            alert('Unable to submit request');
          }
        });
      }

      if (editForm) {
        editForm.addEventListener('submit', async (event) => {
          event.preventDefault();
          if (!currentEditRequest || (currentEditRequest.status || 'Pending') !== 'Pending') {
            alert('Only pending requests can be edited');
            closeEditDialog();
            return;
          }

          const formData = new FormData(editForm);
          formData.append('action', 'update');

          try {
            const response = await fetch(API, {
              method: 'POST',
              credentials: 'include',
              body: formData
            });
            const data = await response.json();
            if (!data.success) {
              alert(data.message || 'Unable to update request');
              return;
            }

            closeEditDialog();
            await loadRequests();
          } catch (error) {
            alert('Unable to update request');
          }
        });
      }

      if (cancelEditRequestBtn) {
        cancelEditRequestBtn.addEventListener('click', closeEditDialog);
      }

      if (editDialog) {
        editDialog.addEventListener('click', (event) => {
          if (event.target === editDialog) {
            closeEditDialog();
          }
        });
      }

      loadRequests();
      setInterval(loadRequests, 4000);
    })();
  </script>
</body>
</html>
