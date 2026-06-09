<?php
require_once __DIR__ . '/../backend/session.php';
if (!isLoggedIn() || $_SESSION['role'] !== 'volunteer') {
  header('Location: /project_club/src/frontend/home.php');
  exit;
}
?>
<?php
$pageTitle = 'Volunteer Dashboard | Dream';
$bodyPage = 'volunteer';
$headLinks = ['css/dashboard.css'];
$wrapContentInMain = false;

ob_start();
?>
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

      <dialog id="donorPickerModal" class="donor-picker-modal">
        <div class="donor-picker-panel" role="dialog" aria-modal="true" aria-labelledby="donorPickerTitle">
          <div class="panel-header">
            <div>
              <h3 id="donorPickerTitle">Select Matching Donor</h3>
              <p>Choose a donor whose blood group matches the patient request.</p>
            </div>
          </div>

          <div class="donor-picker-summary">
            <strong id="donorPickerBloodGroup">Blood group: -</strong>
            <span id="donorPickerRequestMeta">No request selected</span>
          </div>

          <div id="donorPickerList" class="donor-picker-list"></div>

          <div class="donor-picker-actions">
            <button type="button" class="btn small user-action-btn" data-close-donor-picker>Close</button>
          </div>
        </div>
      </dialog>
    </section>
  </main>

  <script>
    (function () {
      const API = '/project_club/src/backend/request.php';
      const tbody = document.getElementById('requestTableBody');
      const donorPickerModal = document.getElementById('donorPickerModal');
      const donorPickerList = document.getElementById('donorPickerList');
      const donorPickerBloodGroup = document.getElementById('donorPickerBloodGroup');
      const donorPickerRequestMeta = document.getElementById('donorPickerRequestMeta');

      let activeDonorRequest = null;

      if (!tbody || !donorPickerModal || !donorPickerList || !donorPickerBloodGroup || !donorPickerRequestMeta) return;

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

        try {
          const data = await response.json();
          console.log('requestAction response', action, data);
          return data;
        } catch (err) {
          const text = await response.text();
          console.error('requestAction non-JSON response', response.status, text);
          return { success: false, message: text || `HTTP ${response.status}` };
        }
      };

      const closeDonorPicker = () => {
        activeDonorRequest = null;
        if (donorPickerModal.open) {
          donorPickerModal.close();
        }
      };

      const renderDonorPicker = (request, donors) => {
        donorPickerBloodGroup.textContent = `Blood group: ${request.blood_group || '-'}`;
        donorPickerRequestMeta.textContent = `Patient: ${request.patient_name || '—'} | Hospital: ${request.hospital || '—'} | Location: ${request.location || '—'}`;

        if (!donors.length) {
          donorPickerList.innerHTML = '<div class="donor-picker-empty">No matching donors found.</div>';
          return;
        }

        donorPickerList.innerHTML = donors.map((donor) => `
          <div class="donor-picker-item">
            <div class="donor-picker-donor">
              <strong>${donor.name || '—'}</strong>
              <span>Name: ${donor.name || '—'} | ID: ${donor.id} | Phone: ${donor.phone || '—'} | Blood group: ${donor.blood_group || '—'}</span>
            </div>
            <button class="btn small user-action-btn donor-select-btn" type="button" data-donor-id="${donor.id}">Select</button>
          </div>
        `).join('');

        donorPickerList.querySelectorAll('.donor-select-btn').forEach((button) => {
          button.addEventListener('click', async () => {
            if (!activeDonorRequest) return;

            try {
              const donorId = button.getAttribute('data-donor-id');
              const data = await requestAction('assign', activeDonorRequest.id, { donor_id: donorId });
              if (!data.success) {
                alert(data.message || 'Unable to assign request');
                return;
              }
              closeDonorPicker();
              await refresh();
            } catch (error) {
              alert('Unable to assign request');
            }
          });
        });
      };

      const openDonorPicker = async (request) => {
        activeDonorRequest = request;
        donorPickerBloodGroup.textContent = `Blood group: ${request.blood_group || '-'}`;
        donorPickerRequestMeta.textContent = `Loading matching donors for ${request.patient_name || 'this request'}...`;
        donorPickerList.innerHTML = '<div class="donor-picker-empty">Loading matching donors...</div>';

        if (!donorPickerModal.open) {
          donorPickerModal.showModal();
        }

        try {
          const response = await fetch(`${API}?action=matched-donors&request_id=${request.id}`, { credentials: 'include', cache: 'no-store' });
          const data = await response.json();
          if (!data.success) {
            donorPickerList.innerHTML = `<div class="donor-picker-empty">${data.message || 'Unable to load donors'}</div>`;
            return;
          }
          renderDonorPicker(data.request || request, data.donors || []);
        } catch (error) {
          donorPickerList.innerHTML = '<div class="donor-picker-empty">Unable to load donors.</div>';
        }
      };

      donorPickerModal.querySelectorAll('[data-close-donor-picker]').forEach((element) => {
        element.addEventListener('click', closeDonorPicker);
      });

      donorPickerModal.addEventListener('click', (event) => {
        if (event.target === donorPickerModal) {
          closeDonorPicker();
        }
      });

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
          const canAssign = !['Completed', 'Failed', 'Donor Assigned'].includes(status);
          const actionCell = status === 'Completed'
            ? '<span class="table-badge success">Completed</span>'
            : status === 'Failed'
              ? '<span class="table-badge pending">Failed</span>'
              : `
                <button class="btn small complete-btn" type="button">Complete</button>
                <button class="btn small failed-btn" type="button">Failed</button>
              `;
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
            <td class="table-actions">${actionCell}</td>
          `;

          row.querySelector('.assign-btn')?.addEventListener('click', async () => {
            await openDonorPicker(request);
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
<?php
$content = ob_get_clean();
require __DIR__ . '/after_login_master.php';
?>
