
  <footer class="text-center py-3 border-top" style="font-size: 13px;">
    <div class="container-fluid">
        <p class="text-muted mb-1">
            &copy; {{ date('Y') }} Computer Service BARRY.
        </p>
        <small class="text-muted">
            v1.0.0
        </small>
    </div>
  </footer>

  <div id="sidebarOverlay" class="sidebar-overlay"></div>

  <!-- Bootstrap 5.3 bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!--plugins-->
  <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/easyPieChart/jquery.easypiechart.js') }}"></script>
  <script src="{{ asset('assets/plugins/peity/jquery.peity.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
  <script src="{{ asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
  <script src="{{ asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
  <!--app-->
  <script src="{{ asset('assets/js/table-datatable.js') }}"></script>
  <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
  <script src="{{ asset('assets/js/form-select2.js') }}"></script>
  <script src="{{ asset('assets/js/js_gare.js') }}"></script>
  <script src="{{ asset('assets/plugins/js/bs-stepper.min.js') }}"></script>

  <script src="{{ asset('mon_js/swt_alert.js') }}"></script>

  <script>
     if (document.querySelector(".best-product")) new PerfectScrollbar(".best-product");
     if (document.querySelector(".top-sellers-list")) new PerfectScrollbar(".top-sellers-list");

    $(document).ready(function() {
        $('#example1').DataTable();
    });
  </script>

  <script>
    // Un menu "..." (dropdown-menu) ouvert dans un tableau .table-responsive était rogné
    // par le défilement horizontal du tableau. On désactive le défilement le temps que le
    // menu est ouvert, pour qu'il s'affiche en entier.
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.table-responsive').forEach(function (wrapper) {
        wrapper.addEventListener('show.bs.dropdown', function () {
          wrapper.style.overflow = 'visible';
        });
        wrapper.addEventListener('hide.bs.dropdown', function () {
          wrapper.style.overflow = '';
        });
      });
    });

    document.addEventListener('show.bs.dropdown', function (event) {
      var row = event.target.closest('tr');
      if (row) {
        row.style.position = 'relative';
        row.style.zIndex = 1045;
      }
    });
    document.addEventListener('hide.bs.dropdown', function (event) {
      var row = event.target.closest('tr');
      if (row) {
        row.style.zIndex = '';
        row.style.position = '';
      }
    });
  </script>

  <style>
    .tg-action-busy {
      pointer-events: none;
      opacity: .55;
    }
    .tg-action-busy::after {
      content: "";
      display: inline-block;
      width: .75em;
      height: .75em;
      margin-left: .4em;
      border: 2px solid currentColor;
      border-right-color: transparent;
      border-radius: 50%;
      vertical-align: -0.15em;
      animation: tg-spin .6s linear infinite;
    }
    @keyframes tg-spin {
      to { transform: rotate(360deg); }
    }
  </style>

  <script>
    (function () {
      document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented) return;

        if (form.dataset.tgSubmitting === '1') {
          event.preventDefault();
          return;
        }

        var btn = event.submitter
          || form.querySelector('button[type="submit"], input[type="submit"]');
        if (!btn || btn.disabled) return;

        form.dataset.tgSubmitting = '1';

        btn.classList.add('tg-action-busy');
        if (btn.tagName === 'BUTTON') {
          btn.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>');
        }
      }, false);

      document.addEventListener('click', function (event) {
        var link = event.target.closest('.delete-button, .supprimer-car-button');
        if (!link) return;

        if (link.dataset.tgBusy === '1') {
          event.preventDefault();
          event.stopImmediatePropagation();
          return;
        }

        link.dataset.tgBusy = '1';
        link.classList.add('tg-action-busy');
        window.setTimeout(function () {
          link.dataset.tgBusy = '0';
          link.classList.remove('tg-action-busy');
        }, 1500);
      }, true);
    })();
  </script>

  {{-- Mode sombre / sidebar repliable / sélecteur de couleur — copie fidèle du JS de
       DojoManager (resources/views/layouts/app.blade.php). --}}
  <script>
    const darkModeToggle = document.getElementById('darkModeToggle');
    const html = document.documentElement;

    function loadDarkMode() {
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        if (isDarkMode) {
            html.classList.add('dark-mode');
            if (darkModeToggle) darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
    }

    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            html.classList.toggle('dark-mode');
            const isDarkMode = html.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkMode);
            darkModeToggle.innerHTML = isDarkMode
                ? '<i class="fas fa-sun"></i>'
                : '<i class="fas fa-moon"></i>';
        });
    }

    document.addEventListener('DOMContentLoaded', loadDarkMode);

    // Sidebar toggle: slide-in on mobile, collapse to icons on desktop
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.querySelector('.main-content');

    function setSidebarCollapsed(collapsed) {
        if (collapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed-offset');
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('collapsed-offset');
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const persisted = localStorage.getItem('sidebarCollapsed') === 'true';
        if (window.innerWidth > 768 && persisted) {
            setSidebarCollapsed(true);
            if (sidebarCollapseBtn) {
                sidebarCollapseBtn.querySelector('i')?.classList.remove('fa-chevron-left');
                sidebarCollapseBtn.querySelector('i')?.classList.add('fa-chevron-right');
            }
        }
    });

    const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            }
        });
    }

    if (sidebarCollapseBtn && sidebar) {
        sidebarCollapseBtn.addEventListener('click', function() {
            const collapsed = sidebar.classList.toggle('collapsed');
            if (collapsed) {
                mainContent.classList.add('collapsed-offset');
                sidebarCollapseBtn.querySelector('i').classList.remove('fa-chevron-left');
                sidebarCollapseBtn.querySelector('i').classList.add('fa-chevron-right');
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                mainContent.classList.remove('collapsed-offset');
                sidebarCollapseBtn.querySelector('i').classList.remove('fa-chevron-right');
                sidebarCollapseBtn.querySelector('i').classList.add('fa-chevron-left');
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('selectedTheme', theme);
        location.reload();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('selectedTheme') || 'default';
        if (savedTheme !== 'default') {
            document.documentElement.setAttribute('data-theme', savedTheme);
        }
    });
  </script>
