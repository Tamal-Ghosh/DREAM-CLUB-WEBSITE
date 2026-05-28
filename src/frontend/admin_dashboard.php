<?php
require_once __DIR__ . '/../backend/session.php';
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
  header('Location: ../frontend/home.html');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Dream</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/site-shell.css">
</head>
<body data-page="admin">
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
        <a href="admin_dashboard.php" class="dashboard-link" data-page="admin">Dashboard</a>
        <a href="profile.php" class="profile-link" data-page="profile">Profile</a>
      </nav>
    </div>
  </header>
  <main class="dashboard-shell">
    <section class="dashboard-content" id="overview">
      <div class="hero-grid admin-hero-grid">
        <article class="hero-card">
          <div class="eyebrow">Superuser control</div>
          <h2>Manage the full blood donation system.</h2>
          <p>
            Monitor users and blood requests from one place.
          </p>
          <div class="hero-actions">
            <a class="btn" href="#users">View users</a>
          </div>
        </article>
      </div>

      <div class="admin-grid full-width" style="margin-top:24px;">
        <section class="table-card admin-wide" id="requests">
          <div class="panel-header">
            <div>
              <h3>Blood Request Management</h3>
              <p>Assign donors, then mark a request complete or failed only after it is donor assigned.</p>
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

      <div class="admin-grid full-width" style="margin-top:24px;">
        <section class="table-card admin-wide" id="users">
          <div class="panel-header">
            <div>
              <h3>User Management</h3>
              <p>Search users, change roles, and toggle status between Active and Blocked.</p>
            </div>
            <div class="panel-actions" style="min-width:260px; flex: 1; display:flex; justify-content:flex-end; gap:10px; align-items:center; flex-wrap:wrap;">
              <a class="btn small user-action-btn" href="register.html?next=admin" style="width:auto; min-width:120px; max-width:180px; text-align:center; flex:0 0 auto;">Add User</a>
              <input id="userSearchInput" class="user-search" type="search" placeholder="Search users" aria-label="Search users" style="width:100%; max-width:320px;">
            </div>
          </div>

          <div class="history-table-wrap">
            <table class="history-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Number</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Change Status</th>
                  <th>Edit</th>
                </tr>
              </thead>
              <tbody id="userTableBody"></tbody>
            </table>
          </div>
        </section>
      </div>

      <dialog id="editUserModal" class="edit-user-modal">
        <div class="edit-user-modal-panel" role="dialog" aria-modal="true" aria-labelledby="editUserTitle">
          <div class="panel-header">
            <div>
              <h3 id="editUserTitle">Edit User</h3>
              <p>Update user information and settings.</p>
            </div>
          </div>

          <div class="edit-user-grid">
            <input id="editUserId" type="hidden">
            <div class="field-block">
              <label for="editUserName">Full name</label>
              <input id="editUserName" class="assign-input" type="text" placeholder="Full name">
            </div>
            <div class="field-block">
              <label for="editUserEmail">Email</label>
              <input id="editUserEmail" class="assign-input" type="email" placeholder="Email">
            </div>
            <div class="field-block">
              <label for="editUserPhone">Phone number</label>
              <input id="editUserPhone" class="assign-input" type="text" placeholder="Phone number">
            </div>
            <div class="field-block">
              <label for="editUserRole">Role</label>
              <select id="editUserRole" class="user-role-select">
                <option value="donor">donor</option>
                <option value="patient">patient</option>
                <option value="volunteer">volunteer</option>
                <option value="admin">admin</option>
              </select>
            </div>
            <div class="field-block">
              <label for="editUserBloodGroup">Blood group</label>
              <input id="editUserBloodGroup" class="assign-input" type="text" placeholder="Blood group">
            </div>
            <div class="field-block">
              <label for="editUserAvailability">Availability</label>
              <select id="editUserAvailability" class="user-role-select">
                <option value="Available">Available</option>
                <option value="Unavailable">Unavailable</option>
              </select>
            </div>
            <div class="field-block">
              <label for="editUserStatus">Status</label>
              <select id="editUserStatus" class="user-role-select">
                <option value="Active">Active</option>
                <option value="Blocked">Blocked</option>
              </select>
            </div>
          </div>

          <div class="edit-user-actions">
            <button type="button" class="btn small user-action-btn" data-close-edit-modal>Cancel</button>
            <button id="saveEditUserBtn" class="btn small user-action-btn" type="button">Save Changes</button>
          </div>
        </div>
      </dialog>

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
      const donorPickerModal = document.getElementById('donorPickerModal');
      const donorPickerList = document.getElementById('donorPickerList');
      const donorPickerBloodGroup = document.getElementById('donorPickerBloodGroup');
      const donorPickerRequestMeta = document.getElementById('donorPickerRequestMeta');

      let activeDonorRequest = null;

      if (!tbody || !donorPickerModal || !donorPickerList || !donorPickerBloodGroup || !donorPickerRequestMeta) {
        return;
      }

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
  <script>
    (function () {
      const API = '../backend/users.php';
      const tbody = document.getElementById('userTableBody');
      const searchInput = document.getElementById('userSearchInput');
      const editUserModal = document.getElementById('editUserModal');
      const editUserId = document.getElementById('editUserId');
      const editUserName = document.getElementById('editUserName');
      const editUserEmail = document.getElementById('editUserEmail');
      const editUserPhone = document.getElementById('editUserPhone');
      const editUserRole = document.getElementById('editUserRole');
      const editUserBloodGroup = document.getElementById('editUserBloodGroup');
      const editUserAvailability = document.getElementById('editUserAvailability');
      const editUserStatus = document.getElementById('editUserStatus');
      const saveEditUserBtn = document.getElementById('saveEditUserBtn');

      if (!tbody || !searchInput || !editUserModal || !editUserId || !editUserName || !editUserEmail || !editUserPhone || !editUserRole || !editUserBloodGroup || !editUserAvailability || !editUserStatus || !saveEditUserBtn) {
        return;
      }

      let users = [];

      const requestAction = async (action, payload = {}) => {
        const formData = new FormData();
        formData.append('action', action);
        Object.entries(payload).forEach(([key, value]) => formData.append(key, value));

        const response = await fetch(API, {
          method: 'POST',
          credentials: 'include',
          body: formData
        });
        return response.json();
      };

      const render = (items) => {
        tbody.innerHTML = '';
        if (!items.length) {
          const row = document.createElement('tr');
          row.innerHTML = '<td colspan="8" style="text-align:center; padding:18px;">No users found</td>';
          tbody.appendChild(row);
          return;
        }

        items.forEach((user) => {
          const row = document.createElement('tr');
          row.dataset.userId = user.id;
          row.dataset.searchText = `${user.name || ''} ${user.email || ''} ${user.role || ''} ${user.status || ''} ${user.phone || ''} ${user.blood_group || ''}`.toLowerCase();
          const statusLabel = user.status || 'Active';
          const nextStatus = statusLabel === 'Blocked' ? 'Active' : 'Block';
          const nextStatusValue = statusLabel === 'Blocked' ? 'Active' : 'Blocked';
          row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.name || '—'}</td>
            <td>${user.email || '—'}</td>
            <td>${user.phone || '—'}</td>
            <td>${user.role || '—'}</td>
            <td><span class="table-badge ${statusLabel === 'Active' ? 'success' : 'pending'}">${statusLabel}</span></td>
            <td><button class="btn small status-toggle-btn user-status-btn" type="button">${nextStatus}</button></td>
            <td><button class="btn small edit-user-btn user-action-btn" type="button">Edit</button></td>
          `;

          row.querySelector('.status-toggle-btn')?.addEventListener('click', async () => {
            try {
              const data = await requestAction('toggle-status', {
                user_id: user.id,
                status: nextStatusValue
              });
              if (!data.success) {
                alert(data.message || 'Unable to update status');
                return;
              }
              await refreshUsers();
            } catch (error) {
              alert('Unable to update status');
            }
          });

          row.querySelector('.edit-user-btn')?.addEventListener('click', () => {
            editUserId.value = String(user.id || '');
            editUserName.value = user.name || '';
            editUserEmail.value = user.email || '';
            editUserPhone.value = user.phone || '';
            editUserRole.value = user.role || 'patient';
            editUserBloodGroup.value = user.blood_group || '';
            editUserAvailability.value = user.availability_status || 'Available';
            editUserStatus.value = user.status || 'Active';
            if (!editUserModal.open) {
              editUserModal.showModal();
            }
          });

          tbody.appendChild(row);
        });
      };

      const closeEditModal = () => {
        if (editUserModal.open) {
          editUserModal.close();
        }
      };

      editUserModal.querySelectorAll('[data-close-edit-modal]').forEach((element) => {
        element.addEventListener('click', closeEditModal);
      });

      editUserModal.addEventListener('click', (event) => {
        if (event.target === editUserModal) {
          closeEditModal();
        }
      });

      saveEditUserBtn.addEventListener('click', async () => {
        try {
          const data = await requestAction('update-user', {
            user_id: editUserId.value,
            name: editUserName.value,
            email: editUserEmail.value,
            phone: editUserPhone.value,
            role: editUserRole.value,
            blood_group: editUserBloodGroup.value,
            availability_status: editUserAvailability.value,
            status: editUserStatus.value
          });

          if (!data.success) {
            alert(data.message || 'Unable to update user');
            return;
          }

          closeEditModal();
          await refreshUsers();
        } catch (error) {
          alert('Unable to update user');
        }
      });

      const applyFilter = () => {
        const query = searchInput.value.trim().toLowerCase();
        const filtered = !query ? users : users.filter((user) =>
          `${user.name || ''} ${user.email || ''} ${user.phone || ''} ${user.role || ''} ${user.status || ''}`.toLowerCase().includes(query)
        );
        render(filtered);
      };

      const refreshUsers = async () => {
        try {
          const response = await fetch(`${API}?action=list`, { credentials: 'include', cache: 'no-store' });
          const data = await response.json();
          users = data.success ? (data.users || []) : [];
          applyFilter();
        } catch (error) {
          users = [];
          applyFilter();
        }
      };

      searchInput.addEventListener('input', applyFilter);
      refreshUsers();
      setInterval(refreshUsers, 4000);
    })();
  </script>
</body>
</html>
