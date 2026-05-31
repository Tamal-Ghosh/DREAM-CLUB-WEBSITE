(() => {
  const loadSession = async () => {
    try {
      const response = await fetch('/project_club/src/backend/session.php', {
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
      profile: ['donor', 'patient', 'volunteer', 'admin'],
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

    // Ensure profile + sign out links when logged in, remove Login link
    const nav = document.querySelector('.site-nav');
    if (nav) {
      const loginLink = nav.querySelector('a[data-page="login"]');
      if (isLoggedIn) {
        if (loginLink) loginLink.remove();

        const dashboardHref = (currentRole === 'donor') ? '/project_club/src/frontend/donor_dashboard.php' :
                  (currentRole === 'patient') ? '/project_club/src/frontend/patient_dashboard.php' :
                  (currentRole === 'admin') ? '/project_club/src/frontend/admin_dashboard.php' :
                  (currentRole === 'volunteer') ? '/project_club/src/frontend/volunteer_dashboard.php' : '/project_club/src/frontend/home.php';
        let dashboardLink = nav.querySelector('.dashboard-link');
        if (!dashboardLink) {
          dashboardLink = document.createElement('a');
          nav.appendChild(dashboardLink);
        }
        dashboardLink.href = dashboardHref;
        dashboardLink.className = 'dashboard-link';
        dashboardLink.dataset.page = currentRole;
        dashboardLink.textContent = 'Dashboard';

        let profileLink = nav.querySelector('a[data-page="profile"]');
        if (!profileLink) {
          profileLink = document.createElement('a');
          nav.appendChild(profileLink);
        }
        profileLink.href = '/project_club/src/frontend/profile.php';
        profileLink.className = 'profile-link';
        profileLink.dataset.page = 'profile';
        profileLink.textContent = 'Profile';

        // add sign out link if missing
        if (!nav.querySelector('#logoutBtn')) {
          const out = document.createElement('a');
          out.href = '/project_club/src/backend/logout.php';
          out.id = 'logoutBtn';
          out.textContent = 'Sign out';
          nav.appendChild(out);
        }
      } else {
        // not logged in: ensure login link is present
        if (!loginLink) {
          const a = document.createElement('a');
          a.href = '/project_club/src/frontend/login.php';
          a.dataset.page = 'login';
          a.textContent = 'Login';
          nav.appendChild(a);
        }
        // remove dashboard/profile/signout if present
        const dashboard = nav.querySelector('.dashboard-link'); if (dashboard) dashboard.remove();
        const pl = nav.querySelector('.profile-link'); if (pl) pl.remove();
        const out = nav.querySelector('#logoutBtn'); if (out) out.remove();
      }
    }

    const currentPage = document.body.dataset.page;
    // If a logged-in user lands on the login page, redirect them to their dashboard
    if (currentPage === 'login' && isLoggedIn) {
      const profileHref = (currentRole === 'donor') ? '/project_club/src/frontend/donor_dashboard.php' :
              (currentRole === 'patient') ? '/project_club/src/frontend/patient_dashboard.php' :
              (currentRole === 'admin') ? '/project_club/src/frontend/admin_dashboard.php' :
              (currentRole === 'volunteer') ? '/project_club/src/frontend/volunteer_dashboard.php' : '/project_club/src/frontend/home.php';
      window.location.href = profileHref;
      return;
    }
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
          await fetch('/project_club/src/backend/logout.php', { credentials: 'include' });
        } catch (error) {
          // Ignore logout network failures and force client redirect.
        }
        window.location.href = '/project_club/src/frontend/login.php';
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