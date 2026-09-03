<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'TransGest Admin')</title>
  <script>
    // Applique le mode sombre / la couleur de thème AVANT le premier rendu de la page :
    // sans ça, la page s'affiche d'abord avec le thème par défaut (flash blanc) puis
    // bascule après le chargement complet, une fois le script du bas exécuté.
    (function () {
      try {
        if (localStorage.getItem('darkMode') === 'true') {
          document.documentElement.classList.add('dark-mode');
        }
        var theme = localStorage.getItem('selectedTheme');
        if (theme && theme !== 'default') {
          document.documentElement.setAttribute('data-theme', theme);
        }
      } catch (e) {}
    })();
  </script>
  <meta name="theme-color" content="#0f3b5e">
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any" />
  <link rel="apple-touch-icon" href="{{ asset('images/logos/transgest_icon.png') }}" />
  <link rel="manifest" href="{{ asset('pwa/admin-manifest.json') }}">
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw-admin.js', { scope: '/admin/' }).catch(function () {});
      });
    }
  </script>

  <!-- Bootstrap 5.3 + Font Awesome 6.4 + SweetAlert2 (template DojoManager) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4/animate.min.css">

  <!-- Plugins fonctionnels utilisés par les pages (tableaux, sélecteurs, graphiques) -->
  <link href="{{ asset('assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/js/bs-stepper.css') }}" rel="stylesheet" />

  <!-- Widgets internes (cartes KPI, donut, hero d'accueil…) — conservés, restylés en dessous -->
  <link href="{{ asset('assets/css/transgest-theme.css') }}?v={{ @filemtime(public_path('assets/css/transgest-theme.css')) }}" rel="stylesheet" />

  @include('admin.partials.theme')
</head>
