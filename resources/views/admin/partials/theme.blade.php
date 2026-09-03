{{--
    Thème visuel de l'admin — copie fidèle du <style> de DojoManager_laravel
    (resources/views/layouts/app.blade.php), mêmes variables/règles CSS. Seule la
    palette par défaut a été adaptée à TransGest (marine/orange) ; la mécanique
    (mode sombre, sélecteur de couleur, sidebar repliable) est identique.
--}}
<style>
    :root {
        --primary-color: #0f3b5e;
        --secondary-color: #1d6fa5;
        --navbar-bg: #0f3b5e;
        --sidebar-bg: #0b2c48;
        --navbar-text: #f4f6f8;
        --sidebar-text: #f4f6f8;
        --main-bg: #eef2f8;
        --card-bg: #ffffff;
        --card-border: #d7dee9;
        --footer-bg: #f8f9fa;
        --footer-border: #dfe4ee;
        --footer-text: #6b7280;
        --body-text: #1f2937;

        /* Ces deux variables pilotent aussi TOUS les bandeaux/en-têtes "de marque" des
           pages de contenu (accueil, permissions, etc. — assets/css/transgest-theme.css) :
           en les faisant suivre le thème choisi ici, ces en-têtes changent avec lui au lieu
           de rester figés en marine. */
        --tg-navy: var(--primary-color);
        --tg-orange: var(--primary-color);
        --tg-orange-light: var(--secondary-color);
    }

    {{-- .tg-hero (bandeau "Bon après-midi…") dégrade sur 3 points d'arrêt dans
         transgest-theme.css, mais seul le 1er suit --tg-navy : les deux autres restent
         un marine/noir fixe (var(--tg-dark), #061019), ce qui fait que l'essentiel du
         bandeau ne changeait pas avec le thème. On reprend tout le dégradé ici. --}}
    .tg-hero {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
    }

    :root[data-theme="orange"] {
        --primary-color: #ea580c;
        --secondary-color: #f59e0b;
        --navbar-bg: #9a3412;
        --sidebar-bg: #7c2d12;
        --navbar-text: #ffffff;
        --sidebar-text: #ffffff;
    }

    :root[data-theme="green"] {
        --primary-color: #10b981;
        --secondary-color: #059669;
        --navbar-bg: #0f766e;
        --sidebar-bg: #0e6e63;
        --navbar-text: #ffffff;
        --sidebar-text: #ffffff;
    }

    :root[data-theme="red"] {
        --primary-color: #ef4444;
        --secondary-color: #dc2626;
        --navbar-bg: #b91c1c;
        --sidebar-bg: #991b1b;
        --navbar-text: #ffffff;
        --sidebar-text: #ffffff;
    }

    :root[data-theme="indigo"] {
        --primary-color: #4f46e5;
        --secondary-color: #4338ca;
        --navbar-bg: #4338ca;
        --sidebar-bg: #3730a3;
        --navbar-text: #ffffff;
        --sidebar-text: #ffffff;
    }

    html.dark-mode {
        --navbar-bg: #111827;
        --sidebar-bg: #0f172a;
        --main-bg: #0b1120;
        --card-bg: #111827;
        --card-border: #1f2937;
        --footer-bg: #0f172a;
        --footer-border: #1f2937;
        --footer-text: #cbd5e1;
        --navbar-text: #e2e8f0;
        --sidebar-text: #e2e8f0;
        --body-text: #e2e8f0;
        --surface-alt: #1a2332;
    }

    html.dark-mode .bg-light,
    html.dark-mode .bg-white,
    html.dark-mode .table-light {
        background-color: var(--surface-alt) !important;
        color: var(--body-text) !important;
    }
    html.dark-mode .bg-light.border,
    html.dark-mode .bg-white.border {
        border-color: var(--card-border) !important;
    }
    html.dark-mode .text-dark { color: var(--body-text) !important; }

    {{-- Composants "carte blanche" propres aux pages de contenu (accueil, filtres,
         onglets de paramètres…) — assets/css/transgest-theme.css les code en dur en blanc,
         indépendamment du mode sombre. --}}
    html.dark-mode .tg-panel,
    html.dark-mode .tg-stat-card,
    html.dark-mode .tg-filter-bar,
    html.dark-mode .permission-card,
    html.dark-mode .tg-timeline-card {
        background: var(--card-bg) !important;
        color: var(--body-text) !important;
        border-color: var(--card-border) !important;
    }
    html.dark-mode .tg-stat-card__value,
    html.dark-mode .tg-panel__title,
    html.dark-mode .tg-hero__title,
    html.dark-mode .tg-timeline-card .fw-semibold {
        color: var(--body-text) !important;
    }
    html.dark-mode .tg-panel__subtitle,
    html.dark-mode .tg-stat-card__label,
    html.dark-mode .tg-section-label {
        color: var(--footer-text) !important;
    }
    html.dark-mode .swal2-container .swal2-popup.swal2-toast {
        background: var(--card-bg) !important;
        color: var(--body-text) !important;
    }
    {{-- Onglets/sous-menu (ex. Paramètres généraux) : le lien inactif hérite du gris
         Bootstrap par défaut, illisible sur un fond de carte sombre. --}}
    html.dark-mode .card .nav-link:not(.active) {
        color: var(--body-text) !important;
    }
    html.dark-mode .card .nav-link:not(.active) i {
        color: var(--body-text) !important;
    }

    html.dark-mode .modal-content {
        background-color: var(--card-bg);
        color: var(--body-text);
        border-color: var(--card-border);
    }
    html.dark-mode .modal-header,
    html.dark-mode .modal-footer {
        border-color: var(--card-border);
    }
    html.dark-mode .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    html.dark-mode .swal2-popup {
        background: var(--card-bg) !important;
        color: var(--body-text) !important;
    }
    html.dark-mode .swal2-title,
    html.dark-mode .swal2-html-container {
        color: var(--body-text) !important;
    }

    * {
        transition: background-color 0.3s, color 0.3s;
    }

    html, body {
        height: 100%;
    }

    body {
        background-color: var(--main-bg);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        flex-direction: column;
    }

    html.dark-mode body {
        background-color: var(--main-bg);
        color: var(--body-text);
    }

    .navbar {
        background-color: var(--navbar-bg);
        color: var(--navbar-text);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        flex-shrink: 0;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .card-navbar { border-color: var(--navbar-bg) !important; }
    .card-header-navbar {
        background-color: var(--navbar-bg) !important;
        color: #fff !important;
        border-bottom: none;
    }
    .card-header-navbar h1, .card-header-navbar h2, .card-header-navbar h3,
    .card-header-navbar h4, .card-header-navbar h5, .card-header-navbar h6,
    .card-header-navbar .form-label,
    .card-header-navbar label { color: #fff !important; }
    .card-header-navbar .input-group-text {
        background-color: rgba(255,255,255,.15) !important;
        border-color: rgba(255,255,255,.25) !important;
        color: #fff !important;
    }
    .card-header-navbar .input-group-text .text-muted,
    .card-header-navbar .text-muted { color: rgba(255,255,255,.75) !important; }
    .card-header-navbar .form-control,
    .card-header-navbar .form-select { border-color: rgba(255,255,255,.25); }

    .table td form { margin: 0; }
    .table td .btn { vertical-align: middle; }
    .table td .btn-group,
    .table td .d-flex { vertical-align: middle; }
    .btn-group > form { display: contents; }

    .navbar-brand {
        font-weight: 700;
        font-size: 20px;
        color: var(--navbar-text) !important;
    }
    .navbar-brand i, .navbar-brand img { margin-right: 8px; }

    .navbar .dropdown-menu {
        background-color: var(--card-bg);
        border: 1px solid var(--card-border);
        color: var(--body-text);
        min-width: 200px;
    }
    .navbar .dropdown-item {
        color: var(--body-text);
        padding: 8px 12px;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .navbar .dropdown-item:hover,
    .navbar .dropdown-item:focus {
        background-color: rgba(0,0,0,0.06) !important;
        color: var(--body-text) !important;
    }
    html.dark-mode .navbar .dropdown-menu {
        background-color: var(--card-bg);
        border-color: var(--card-border);
    }
    html.dark-mode .navbar .dropdown-item {
        color: var(--body-text);
    }
    html.dark-mode .navbar .dropdown-item:hover,
    html.dark-mode .navbar .dropdown-item:focus {
        background-color: rgba(255,255,255,0.06) !important;
        color: var(--body-text) !important;
    }
    .navbar .dropdown-item i { margin-right: 8px; min-width: 20px; }

    .navbar-nav .nav-link {
        color: var(--navbar-text) !important;
        margin-left: 10px;
    }
    .navbar-nav .nav-link:hover { color: #e0e0e0; }

    #darkModeToggle, #themeSelector, .btn-theme-toggle {
        border-color: rgba(255, 255, 255, 0.5);
        color: var(--navbar-text);
        background-color: rgba(255,255,255,0.08);
    }
    #darkModeToggle:hover, #themeSelector:hover, .btn-theme-toggle:hover {
        background-color: rgba(0,0,0,0.06);
        border-color: rgba(0,0,0,0.12);
        color: var(--navbar-text);
    }
    html.dark-mode #darkModeToggle:hover, html.dark-mode #themeSelector:hover, html.dark-mode .btn-theme-toggle:hover {
        background-color: rgba(255,255,255,0.06);
        border-color: rgba(255,255,255,0.18);
        color: var(--navbar-text);
    }

    .notify-badge {
        position: absolute; top: -4px; right: -4px; background: #e53e3e; color: #fff;
        border-radius: 50%; font-size: 10px; min-width: 17px; height: 17px; display: flex;
        align-items: center; justify-content: center; font-weight: 700; padding: 0 3px;
    }

    .wrapper {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    .sidebar {
        background-color: var(--sidebar-bg);
        border-right: 1px solid #333;
        width: 250px;
        overflow-y: auto;
        padding: 20px 0;
        position: fixed;
        left: 0;
        top: 56px;
        height: calc(100vh - 56px - 80px);
        z-index: 1000;
    }

    .sidebar.collapsed { width: 70px; }
    .sidebar.collapsed .nav-link {
        justify-content: center;
        padding-left: 0 !important;
        padding-right: 0 !important;
        text-align: center;
        font-size: 0;
    }
    .sidebar.collapsed .nav-link .nav-text,
    .sidebar.collapsed .nav-link .ms-auto,
    .sidebar.collapsed .nav-link .badge,
    .sidebar.collapsed .nav-section-title { display: none !important; }
    .sidebar.collapsed .nav-link i { margin: 0; font-size: 20px; }

    .sidebar .sidebar-collapse-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        margin: 0 auto 15px;
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.06);
        color: var(--sidebar-text);
        cursor: pointer;
        transition: transform 0.3s ease, background-color 0.2s ease;
    }
    .sidebar .sidebar-collapse-btn:hover { background-color: rgba(255,255,255,0.12); }
    .sidebar .sidebar-collapse-btn i { transition: transform 0.3s ease; font-size: 16px; }
    .sidebar.collapsed .sidebar-collapse-btn i { transform: rotate(180deg); }

    .main-content.collapsed-offset {
        margin-left: 70px;
        width: calc(100% - 70px);
    }

    html.dark-mode .sidebar { background-color: #0d0d0d; border-right-color: #222; }

    .sidebar .nav-link {
        color: var(--sidebar-text);
        padding: 12px 20px;
        margin: 5px 0;
        border-left: 3px solid transparent;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        background-color: color-mix(in srgb, var(--secondary-color) 25%, transparent);
        border-left-color: var(--secondary-color);
        color: #fff;
    }
    html.dark-mode .sidebar .nav-link:hover,
    html.dark-mode .sidebar .nav-link.active {
        background-color: color-mix(in srgb, var(--secondary-color) 30%, transparent);
    }

    .sidebar .nav-section-title {
        color: rgba(255,255,255,.45);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 15px 20px 5px;
        margin-top: 10px;
    }
    html.dark-mode .sidebar .nav-section-title { color: #666; }

    {{-- Groupes repliables du sidebar (Billetterie, Colis, Programmation...) — sections
         par priorité/dépendance d'usage, écrans regroupés sous un même menu déroulant
         plutôt qu'un simple séparateur visuel comme dans l'ancienne disposition. --}}
    .sidebar .nav-link-group { font-weight: 600; cursor: pointer; }
    .sidebar .nav-caret { font-size: 11px; margin-left: auto; transition: transform 0.25s ease; opacity: 0.7; }
    .sidebar .nav-link-group.collapsed .nav-caret { transform: rotate(-90deg); }
    .sidebar .nav-link-group .badge { margin-left: auto; }
    .sidebar .nav-link-group.collapsed .badge { margin-left: 0; margin-right: 6px; }
    .sidebar .nav-subgroup {
        padding-left: 14px;
        border-left: 2px solid rgba(255,255,255,.08);
        margin: 0 0 6px 30px;
    }
    .sidebar .nav-subgroup .nav-link {
        padding: 9px 14px;
        margin: 2px 0;
        font-size: 13px;
        border-left: none;
    }
    .sidebar .nav-subgroup .nav-link:hover,
    .sidebar .nav-subgroup .nav-link.active {
        border-left: none;
        border-radius: 6px;
    }
    .sidebar.collapsed .nav-subgroup,
    .sidebar.collapsed .nav-caret { display: none !important; }

    .sidebar hr { border-color: #333; opacity: 0.5; margin: 10px 0; }
    html.dark-mode .sidebar hr { border-color: #222; }

    .main-content {
        margin-left: 250px;
        padding: 30px;
        width: calc(100% - 250px);
        flex: 1;
        overflow-y: auto;
    }

    @media (max-width: 768px) {
        .sidebar {
            display: none;
            position: fixed;
            left: -250px;
            width: 250px;
            z-index: 1040;
        }
        .sidebar.show { display: block; left: 0; }
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1030;
        }
        .sidebar-overlay.show { display: block; }
        .main-content { margin-left: 0; width: 100%; }
    }

    .card {
        border: 1px solid var(--card-border);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        margin-bottom: 20px;
        background-color: var(--card-bg);
        color: var(--body-text);
    }
    .card:hover { box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12); }

    .page-header { margin-bottom: 30px; }
    .breadcrumb { --bs-breadcrumb-divider-color: #6b7280; font-size: 14px; }
    .breadcrumb a { color: var(--primary-color); font-weight: 600; text-decoration: none; }
    .breadcrumb a:hover { color: var(--secondary-color); text-decoration: underline; }
    .breadcrumb-item.active { color: #6b7280; }
    .page-header h1 { font-size: 28px; font-weight: 700; color: #333; }
    html.dark-mode .breadcrumb-item.active,
    html.dark-mode .breadcrumb { color: #a8b0c0; }
    html.dark-mode .page-header h1 { color: #e0e0e0; }

    .alert { border: none; border-radius: 10px; }
    html.dark-mode .alert-success { background-color: #1e4620; color: #b8e6c1; }
    html.dark-mode .alert-danger { background-color: #4a1f1f; color: #f5a3a3; }
    html.dark-mode .alert-info { background-color: #1f3a4a; color: #a3d5f5; }
    html.dark-mode .alert-warning { background-color: #4a3a1f; color: #f5d4a3; }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
    }

    .table { color: var(--body-text); border-color: var(--card-border); }
    .table thead th { background-color: var(--card-bg); color: var(--body-text); border-color: var(--card-border); }
    .table tbody tr { border-color: var(--card-border); }
    .table tbody tr:hover { background-color: color-mix(in srgb, var(--secondary-color) 8%, transparent); }
    html.dark-mode .table {
        --bs-table-bg: var(--card-bg);
        --bs-table-color: var(--body-text);
        --bs-table-border-color: var(--card-border);
        --bs-table-striped-bg: rgba(255, 255, 255, .03);
        --bs-table-striped-color: var(--body-text);
        --bs-table-hover-bg: color-mix(in srgb, var(--secondary-color) 18%, transparent);
        --bs-table-hover-color: var(--body-text);
        background-color: var(--card-bg);
    }

    .form-control, .form-select {
        border: 1px solid var(--card-border);
        border-radius: 8px;
        background-color: var(--card-bg);
        color: var(--body-text);
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(29, 111, 165, 0.15);
    }
    html.dark-mode .text-muted { color: #b0b0b0 !important; }

    footer {
        background-color: var(--footer-bg);
        border-top: 1px solid var(--footer-border);
        color: var(--footer-text);
        padding: 20px;
        flex-shrink: 0;
        margin-top: auto;
    }
    footer a { color: var(--primary-color); text-decoration: none; }
    footer a:hover { color: var(--secondary-color); }
    footer p { margin: 0; font-size: 14px; }
</style>
