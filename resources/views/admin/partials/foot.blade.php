
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
  {{-- Moteur de navigation sans rechargement complet (voir le fichier pour le détail) —
       chargé tôt pour que window.tgReady existe avant tout @section('scripts') qui l'utilise. --}}
  <script src="{{ asset('mon_js/admin-transitions.js') }}"></script>
  <style>
    /* Barre de progression + animation de transition — mêmes réglages que
       assets_site/css/site-common.css (site public), avec les couleurs du thème admin
       choisi (--primary-color/--secondary-color) à la place des couleurs figées du site. */
    .tg-progress-bar {
      position: fixed; top: 0; left: 0; height: 3px; width: 0%;
      background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
      z-index: 99999; opacity: 0; pointer-events: none;
      box-shadow: 0 0 8px rgba(0, 0, 0, .35);
    }
    body.tg-page-leaving { opacity: 0; transform: translateY(6px); }
    body { transition: opacity .16s ease, transform .16s ease; }
    @media (prefers-reduced-motion: reduce) {
      body { transition: none; }
      .tg-progress-bar { transition: none !important; }
    }
  </style>
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
     // Idempotent (PerfectScrollbar sur un élément déjà initialisé ne pose pas de
     // problème), et le tableau #example1 est désormais (ré)initialisé par
     // window.tgInitDataTables (voir mon_js/table-datatable.js) — plus jamais ici en dur,
     // pour ne pas diverger de ce qui se passe après une navigation douce.
     if (document.querySelector(".best-product")) new PerfectScrollbar(".best-product");
     if (document.querySelector(".top-sellers-list")) new PerfectScrollbar(".top-sellers-list");
  </script>

  <script>
    // Un menu "..." (dropdown-menu) ouvert dans un tableau .table-responsive était rogné
    // par le défilement horizontal du tableau. On désactive le défilement le temps que le
    // menu est ouvert, pour qu'il s'affiche en entier.
    //
    // Entièrement délégué sur `document` (les événements Bootstrap show/hide.bs.dropdown
    // remontent normalement) plutôt que lié wrapper par wrapper : ces wrappers vivent dans
    // <body>, régénéré à chaque navigation douce (voir mon_js/admin-transitions.js), donc
    // un binding direct par élément raterait tout wrapper apparu après la première
    // exécution. Garde anti-double-exécution : ce script inline est recréé et ré-exécuté à
    // CHAQUE navigation douce puisqu'il fait partie de <body> — sans cette garde, les
    // écouteurs posés sur `document` s'accumuleraient indéfiniment.
    if (! window.__tgDropdownOverflowFixInit) {
      window.__tgDropdownOverflowFixInit = true;

      document.addEventListener('show.bs.dropdown', function (event) {
        var wrapper = event.target.closest('.table-responsive');
        if (wrapper) wrapper.style.overflow = 'visible';

        var row = event.target.closest('tr');
        if (row) {
          row.style.position = 'relative';
          row.style.zIndex = 1045;
        }
      });
      document.addEventListener('hide.bs.dropdown', function (event) {
        var wrapper = event.target.closest('.table-responsive');
        if (wrapper) wrapper.style.overflow = '';

        var row = event.target.closest('tr');
        if (row) {
          row.style.zIndex = '';
          row.style.position = '';
        }
      });
    }
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
      // Garde anti-double-exécution CRITIQUE : ce script inline fait partie de <body>,
      // recréé et ré-exécuté à CHAQUE navigation douce (voir mon_js/admin-transitions.js).
      // Sans cette garde, une 2e exécution ajoute un 2e écouteur "submit" sur `document` ;
      // au clic suivant, le 1er écouteur pose tgSubmitting='1' SANS preventDefault(), puis
      // le 2e écouteur — déclenché dans la même passe, sur le même événement — voit
      // tgSubmitting déjà à '1' et appelle preventDefault() : le formulaire ne part alors
      // JAMAIS, silencieusement (seul le spinner de chargement s'affiche). Ces écouteurs
      // sont posés sur `document` (jamais remplacé par un swap de <body>) et délégués : les
      // poser une seule fois, pour toujours, est le comportement correct.
      if (window.__tgFootBusyHandlersInit) return;
      window.__tgFootBusyHandlersInit = true;

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
       DojoManager (resources/views/layouts/app.blade.php), adaptée pour la navigation
       sans rechargement complet (voir mon_js/admin-transitions.js) :
        - Enveloppé dans une IIFE : ce script inline fait partie de <body>, donc recréé et
          ré-exécuté à CHAQUE navigation douce. Sans cette IIFE, les `const`/`let` du
          niveau racine (partagé entre TOUTES les exécutions d'un script classique dans un
          même document) entreraient en collision dès la 2e exécution et lèveraient une
          erreur de syntaxe qui interromprait tout le bloc — plus aucun ré-attachement des
          boutons mode sombre/sidebar/thème après la première navigation.
        - Contrairement au correctif de foot.blade.php ci-dessus, PAS de garde
          anti-double-exécution ici : navbar/sidebar vivent dans <body> et sont
          régénérées à chaque swap, donc leurs boutons ont besoin d'un ré-attachement à
          CHAQUE navigation (une IIFE fraîche à chaque fois est exactement ce qu'il faut).
        - tgReady(...) à la place de DOMContentLoaded (ne se déclencherait plus jamais dès
          la 2e navigation, cf. mon_js/admin-transitions.js pour le détail).
        - setTheme() reste exposée sur window : appelée depuis les onclick="" inline du
          sélecteur de thème (admin/partials/navbar.blade.php), qui s'exécutent dans la
          portée globale, hors de portée d'une IIFE. --}}
  <script>
    (function () {
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

      tgReady(loadDarkMode);

      // Sidebar toggle: slide-in on mobile, collapse to icons on desktop
      const sidebarToggle = document.getElementById('sidebarToggle');
      const sidebar = document.getElementById('sidebar');
      const sidebarOverlay = document.getElementById('sidebarOverlay');
      const mainContent = document.querySelector('.main-content');
      const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');

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

      tgReady(function() {
          const persisted = localStorage.getItem('sidebarCollapsed') === 'true';
          if (window.innerWidth > 768 && persisted) {
              setSidebarCollapsed(true);
              if (sidebarCollapseBtn) {
                  sidebarCollapseBtn.querySelector('i')?.classList.remove('fa-chevron-left');
                  sidebarCollapseBtn.querySelector('i')?.classList.add('fa-chevron-right');
              }
          }
      });

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
      window.setTheme = setTheme;

      tgReady(function() {
          const savedTheme = localStorage.getItem('selectedTheme') || 'default';
          if (savedTheme !== 'default') {
              document.documentElement.setAttribute('data-theme', savedTheme);
          }
      });
    })();
  </script>
