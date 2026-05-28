(() => {
  const API = '../backend/profile.php';

  const initProfileSection = async () => {
    const form = document.getElementById('profileForm');
    if (!form) return;

    const messageEl = document.getElementById('profileMessage');
    const roleEl = document.getElementById('profileRole');
    const nameEl = document.getElementById('profileName');
    const emailEl = document.getElementById('profileEmail');
    const phoneEl = document.getElementById('profilePhone');
    const bloodGroupEl = document.getElementById('profileBloodGroup');
    const availabilityWrap = document.querySelector('[data-profile-only="donor"]');
    const availabilityEl = document.getElementById('profileAvailability');
    const oldPasswordEl = document.getElementById('profileOldPassword');
    const passwordEl = document.getElementById('profilePassword');
    const registeredEl = document.getElementById('profileRegistered');
    const readOnlyWrap = document.getElementById('profileReadOnly');
    const editBtn = document.getElementById('profileEditBtn');
    const cancelBtn = document.getElementById('profileCancelBtn');
    const nameView = document.getElementById('profileNameView');
    const emailView = document.getElementById('profileEmailView');
    const phoneView = document.getElementById('profilePhoneView');
    const bloodGroupView = document.getElementById('profileBloodGroupView');
    const availabilityView = document.getElementById('profileAvailabilityView');
    const availabilityViewWrap = document.getElementById('profileAvailabilityViewWrap');

    const setMessage = (text, isError = false) => {
      if (!messageEl) return;
      messageEl.textContent = text;
      messageEl.style.color = isError ? '#a03b33' : '#6b4c45';
    };

    const loadProfile = async () => {
      try {
        const response = await fetch(`${API}?action=me`, {
          credentials: 'include',
          cache: 'no-store'
        });
        const data = await response.json();
        if (!data.success) {
          setMessage(data.message || 'Unable to load profile', true);
          return;
        }

        const user = data.user || {};
        if (roleEl) roleEl.textContent = user.role ? String(user.role).charAt(0).toUpperCase() + String(user.role).slice(1) : '';
        if (nameEl) nameEl.value = user.name || '';
        if (emailEl) emailEl.value = user.email || '';
        if (phoneEl) phoneEl.value = user.phone || '';
        if (bloodGroupEl) bloodGroupEl.value = user.blood_group || '';
        if (availabilityEl) availabilityEl.value = user.availability_status || 'Available';
        if (registeredEl) {
          const created = user.created_at ? new Date(user.created_at) : null;
          registeredEl.textContent = created && !Number.isNaN(created.getTime())
            ? created.toLocaleDateString()
            : '—';
        }
        // populate read-only view
        if (nameView) nameView.textContent = user.name || '—';
        if (emailView) emailView.textContent = user.email || '—';
        if (phoneView) phoneView.textContent = user.phone || '—';
        if (bloodGroupView) bloodGroupView.textContent = user.blood_group || '—';
        if (availabilityView) availabilityView.textContent = user.availability_status || '—';
        if (availabilityViewWrap) {
          availabilityViewWrap.style.display = user.role === 'donor' ? '' : 'none';
        }

        // default to read-only view
        if (readOnlyWrap) readOnlyWrap.style.display = '';
        if (form) form.style.display = 'none';
        if (editBtn) editBtn.style.display = '';
        if (availabilityWrap) {
          availabilityWrap.style.display = user.role === 'donor' ? '' : 'none';
        }
        setMessage('Your profile is read-only. Click Edit to make changes.');
      } catch (error) {
        setMessage('Unable to load profile', true);
      }
    };

    const openEdit = () => {
      if (readOnlyWrap) readOnlyWrap.style.display = 'none';
      if (form) form.style.display = '';
      if (editBtn) editBtn.style.display = 'none';
      if (nameEl) nameEl.focus();
      setMessage('Make changes and click Save.');
    };

    const closeEdit = () => {
      if (form) form.style.display = 'none';
      if (readOnlyWrap) readOnlyWrap.style.display = '';
      if (editBtn) editBtn.style.display = '';
      if (oldPasswordEl) oldPasswordEl.value = '';
      if (passwordEl) passwordEl.value = '';
      setMessage('Your profile is read-only. Click Edit to make changes.');
    };

    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      const formData = new FormData();
      formData.append('action', 'update');
      formData.append('name', nameEl ? nameEl.value : '');
      formData.append('email', emailEl ? emailEl.value : '');
      formData.append('phone', phoneEl ? phoneEl.value : '');
      formData.append('blood_group', bloodGroupEl ? bloodGroupEl.value : '');
      formData.append('old_password', oldPasswordEl ? oldPasswordEl.value : '');
      formData.append('password', passwordEl ? passwordEl.value : '');
      if (availabilityEl) {
        formData.append('availability_status', availabilityEl.value);
      }

      try {
        const response = await fetch(API, {
          method: 'POST',
          credentials: 'include',
          body: formData
        });
        const data = await response.json();
        if (!data.success) {
          setMessage(data.message || 'Unable to update profile', true);
          return;
        }

        if (oldPasswordEl) oldPasswordEl.value = '';
        if (passwordEl) passwordEl.value = '';
        setMessage(data.message || 'Profile updated');
        // update read-only view and close editor
        if (nameView) nameView.textContent = nameEl ? nameEl.value : '';
        if (emailView) emailView.textContent = emailEl ? emailEl.value : '';
        if (phoneView) phoneView.textContent = phoneEl ? phoneEl.value : '';
        if (bloodGroupView) bloodGroupView.textContent = bloodGroupEl ? bloodGroupEl.value : '';
        if (availabilityView) availabilityView.textContent = availabilityEl ? availabilityEl.value : '';
        closeEdit();
      } catch (error) {
        setMessage('Unable to update profile', true);
      }
    });

    if (editBtn) editBtn.addEventListener('click', openEdit);
    if (cancelBtn) cancelBtn.addEventListener('click', closeEdit);

    await loadProfile();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProfileSection);
  } else {
    initProfileSection();
  }
})();
