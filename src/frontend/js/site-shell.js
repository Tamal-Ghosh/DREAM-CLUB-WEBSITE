(() => {
  const loadSession = async () => {
    try {
      const response = await fetch('../backend/session.php', {
        credentials: 'include',
        cache: 'no-store'
      });

      if (!response.ok) {
        return { role: 'public', name: '' };
      }

      const data = await response.json();
      return {
        role: data.role || 'public',
        name: data.name || ''
      };
    } catch (error) {
      return { role: 'public', name: '' };
    }
  };

  const refreshShell = async () => {
    const session = await loadSession();
    const currentRole = session.role || 'public';
    const isLoggedIn = currentRole !== 'public';

    const navConfig = {
      home: ['public', 'donor', 'patient', 'volunteer', 'admin'],
      about: ['public', 'donor', 'patient', 'volunteer', 'admin'],
      contact: ['public', 'donor', 'patient', 'volunteer', 'admin'],
      team: ['public', 'donor', 'patient', 'volunteer', 'admin'],
      request: ['public', 'patient', 'volunteer', 'admin'],
      register: ['public'],
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
      logoutBtn.addEventListener('click', async (event) => {
        event.preventDefault();
        try {
          await fetch('../backend/logout.php', { credentials: 'include' });
        } catch (error) {
          // Ignore logout network failures and force client redirect.
        }
        window.location.href = 'home.html';
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