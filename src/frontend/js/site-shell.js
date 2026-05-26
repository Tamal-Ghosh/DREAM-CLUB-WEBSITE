(() => {
  const refreshShell = () => {
    const currentRole = localStorage.getItem('userRole') || 'public';
    const isLoggedIn = currentRole !== 'public';

    const navConfig = {
      home: ['public', 'donor', 'patient', 'volunteer', 'admin'],
      about: ['public', 'donor', 'patient', 'volunteer', 'admin'],
      contact: ['public', 'donor', 'patient', 'volunteer', 'admin'],
      team: ['public', 'donor', 'patient', 'volunteer', 'admin'],
      donor: ['donor', 'volunteer'],
      patient: ['patient', 'volunteer'],
      volunteer: ['volunteer'],
      admin: ['admin'],
      login: ['public']
    };

    Object.entries(navConfig).forEach(([page, allowedRoles]) => {
      const link = document.querySelector(`.site-nav a[data-page="${page}"]`);
      if (link) {
        link.style.display = allowedRoles.includes(currentRole) ? '' : 'none';
      }
    });

    const userSection = document.querySelector('.site-nav-user');
    if (userSection) {
      userSection.style.display = isLoggedIn ? '' : 'none';
      if (isLoggedIn) {
        const userRole = document.querySelector('.user-role');
        if (userRole) {
          userRole.textContent = currentRole.charAt(0).toUpperCase() + currentRole.slice(1);
          userRole.title = `Logged in as ${currentRole}`;
        }
      }
    }

    const currentPage = document.body.dataset.page;
    document.querySelectorAll('.site-nav a[data-page]').forEach((link) => {
      if (currentPage && link.dataset.page === currentPage) {
        link.classList.add('active');
        link.setAttribute('aria-current', 'page');
      } else {
        link.classList.remove('active');
        link.removeAttribute('aria-current');
      }
    });

    document.body.classList.add('has-site-shell');

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn && !logoutBtn.dataset.shellBound) {
      logoutBtn.dataset.shellBound = 'true';
      logoutBtn.addEventListener('click', (event) => {
        event.preventDefault();
        localStorage.removeItem('userRole');
        localStorage.removeItem('userName');
        window.location.href = 'login.html';
      });
    }

    const userMenu = document.querySelector('.user-menu');
    if (userMenu && !userMenu.dataset.shellBound) {
      userMenu.dataset.shellBound = 'true';
      const trigger = userMenu.querySelector('.user-trigger');
      const dropdown = userMenu.querySelector('.user-dropdown');
      if (trigger && dropdown) {
        const closeMenu = () => {
          dropdown.hidden = true;
          trigger.setAttribute('aria-expanded', 'false');
        };

        trigger.addEventListener('click', (event) => {
          event.stopPropagation();
          const isOpen = trigger.getAttribute('aria-expanded') === 'true';
          dropdown.hidden = isOpen;
          trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        });

        document.addEventListener('click', closeMenu);
        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape') {
            closeMenu();
          }
        });
      }
    }
  };

  window.DreamShell = window.DreamShell || {};
  window.DreamShell.refresh = refreshShell;

  document.addEventListener('dream:pagechange', refreshShell);
  refreshShell();
})();