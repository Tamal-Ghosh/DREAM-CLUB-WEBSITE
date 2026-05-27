<?php
require_once __DIR__ . '/../backend/session.php';
if (!isLoggedIn() || !in_array($_SESSION['role'], ['patient','volunteer'])) {
  header('Location: ../frontend/home.html');
  exit;
}
?>
    (function () {
      const API = '../backend/request.php';
      const form = document.querySelector('form.request-form-grid');
      const listWrap = document.querySelector('.request-card-list');
      const emptyEl = document.querySelector('.empty-state');

      const render = (items) => {
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
          const created = it.created_at ? new Date(it.created_at) : new Date();

          const article = document.createElement('article');
          article.className = 'request-record';
          article.setAttribute('data-request', it.id);
          article.innerHTML = `
            <div class="request-card-header" style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
              <div>
                <span class="request-record-id">Request ID: ${it.id}</span>
                <div style="margin-top:8px;font-weight:600">${it.patient_name || ''}</div>
                <div style="margin-top:4px;color:#7b5f58;">Hospital: ${it.hospital || '—'}</div>
              </div>
              <div style="text-align:right;">
                <div style="font-size:0.95rem; margin-bottom:6px; font-weight:700;">${it.blood_group || '—'}</div>
                <div style="font-size:0.85rem; color:#666; margin-bottom:6px;">${created.toLocaleDateString()}</div>
                <div><span class="table-badge ${statusClass}">${status}</span></div>
              </div>
            </div>
            <div class="record-meta" style="margin-top:12px;">
              <div class="donor-info" style="margin-top:8px;">
                <strong class="donor-name">Donor: ${donorName}</strong>
                <div class="donor-phone">Phone: ${donorPhone}</div>
              </div>
            </div>
          `;
          listWrap.appendChild(article);
        });
      };

      const loadRequests = async () => {
        try {
          const response = await fetch(`${API}?action=patient-history`, {
            credentials: 'include',
            cache: 'no-store'
          });
          const data = await response.json();
          if (data.success) {
            render(data.requests || []);
          } else {
            render([]);
          }
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
            location.hash = '#my-requests';
            await loadRequests();
          } catch (error) {
            alert('Unable to submit request');
          }
        });
      }

      loadRequests();
      setInterval(loadRequests, 4000);
    })();
                </div>
              </div>
              <div class="meta-top-right" style="text-align:right;">
                <div style="font-size:0.95rem; margin-bottom:6px;">
                  <strong>${it.bloodGroup || '—'}</strong>
                </div>
                <div style="font-size:0.85rem; color:#666; margin-bottom:6px;">${new Date(it.created).toLocaleDateString()}</div>
                <div><span class="table-badge ${statusClass}">${status}</span></div>
              </div>
            </div>
            <div class="record-meta" style="margin-top:12px;">
              <div>Hospital: ${it.hospital || '—'}</div>
              <div class="donor-info" style="margin-top:8px;">
                <strong class="donor-name">Donor: ${donorName}</strong>
                <div class="donor-phone">Phone: ${donorPhone}</div>
              </div>
            </div>
          `;
          listWrap.appendChild(article);
        });
      };

      const updateFromAssigned = () => {
        const assigned = getAssignedMap();
        document.querySelectorAll('.request-record').forEach((article) => {
          const id = article.getAttribute('data-request');
          if (!id) return;
          const info = assigned[id];
          if (!info) return;
          const badge = article.querySelector('.table-badge');
          const donorNameEl = article.querySelector('.donor-name');
          const donorPhoneEl = article.querySelector('.donor-phone');
          if (badge && info.status) {
            badge.textContent = info.status;
            badge.classList.remove('pending', 'success');
            badge.classList.add((info.status === 'Completed' || info.status === 'Donor Assigned') ? 'success' : 'pending');
          }
          if (donorNameEl && info.donorName) donorNameEl.textContent = 'Donor: ' + info.donorName;
          if (donorPhoneEl && info.donorPhone) donorPhoneEl.textContent = 'Phone: ' + info.donorPhone;
        });
      };

      if (form) {
        form.addEventListener('submit', (e) => {
          e.preventDefault();
          const data = {
            id: 'PR-' + Date.now().toString().slice(-6),
            patientName: form.patientName.value || '',
            contactNumber: form.contactNumber.value || '',
            bloodGroup: form.bloodGroup.value || '',
            units: form.unitsNeeded.value || '',
            hospital: form.hospital.value || '',
            location: form.location.value || '',
            urgency: form.urgencyLevel.value || '',
            details: form.details ? form.details.value : '',
            status: 'Pending',
            created: Date.now()
          };
          const items = load();
          items.unshift(data);
          save(items);
          render();
          // scroll to requests
          location.hash = '#my-requests';
          form.reset();
        });
      }

      // initial render
      render();

      // respond to assignedDonors changes from other tabs (real-time-ish)
      window.addEventListener('storage', (event) => {
        if (event.key === 'assignedDonors') {
          updateFromAssigned();
        }
        if (event.key === STORAGE_KEY) {
          render();
        }
      });

      // polling fallback for same-tab updates (in case other pages write without triggering storage)
      let lastAssigned = localStorage.getItem('assignedDonors');
      let lastRequests = localStorage.getItem(STORAGE_KEY);
      setInterval(() => {
        const curAssigned = localStorage.getItem('assignedDonors');
        const curRequests = localStorage.getItem(STORAGE_KEY);
        if (curAssigned !== lastAssigned) {
          lastAssigned = curAssigned;
          updateFromAssigned();
        }
        if (curRequests !== lastRequests) {
          lastRequests = curRequests;
          render();
        }
      }, 1500);
    })();
  </script>
</body>
</html>
