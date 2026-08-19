
  <footer class="text-center py-3" style="border-top:1px solid #e9ecef; background:#fff; margin-top:auto;">
    <p class="mb-0 text-muted small">Copyright © 2026 Computer Service Barry. All rights reserved.</p>
  </footer>

  <!-- Bootstrap bundle JS -->
  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <!--plugins-->
  <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/easyPieChart/jquery.easypiechart.js') }}"></script>
  <script src="{{ asset('assets/plugins/peity/jquery.peity.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
  <script src="{{ asset('assets/js/pace.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
  <script src="{{ asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
  <!--app-->
  <script src="{{ asset('assets/js/app.js') }}"></script>
  <script src="{{ asset('assets/js/index.js') }}"></script>
  <script src="{{ asset('assets/js/table-datatable.js') }}"></script>
  <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
  <script src="{{ asset('assets/js/form-select2.js') }}"></script>
  <script src="{{ asset('assets/js/js_gare.js') }}"></script>

  <script src="{{ asset('assets/plugins/js/bs-stepper.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/js/main.js') }}"></script>

  <script src="{{ asset('mon_js/swt_alert.js') }}"></script>

  <script>
     new PerfectScrollbar(".best-product")
     new PerfectScrollbar(".top-sellers-list")

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
