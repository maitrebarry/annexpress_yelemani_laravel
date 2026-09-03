<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <script>
        // Applique le mode sombre / la couleur de theme AVANT le premier rendu (pas de
        // flash de theme par defaut) - meme mecanique que resources/views/admin/partials/header.blade.php.
        (function () {
            try {
                if (localStorage.getItem('tgSiteDarkMode') === 'true') {
                    document.documentElement.classList.add('dark-mode');
                }
                var theme = localStorage.getItem('tgSiteTheme');
                if (theme && theme !== 'default') {
                    document.documentElement.setAttribute('data-theme', theme);
                }
            } catch (e) {}
        })();
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mon compte - {{ $compagnie->nom_compagnie }}</title>
    <link rel="icon" href="{{ asset('assets_site/img/favicon.svg') }}">
    <link href="{{ asset('assets_site/css/inter.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/site-common.css') }}" rel="stylesheet">
    <link rel="manifest" href="{{ route('site.manifest') }}">
    <meta name="theme-color" content="#0f3b5e">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw-site.js', { scope: '/' }).catch(function () {});
            });
        }
    </script>
    <script defer src="{{ asset('assets_site/js/site-transitions.js') }}"></script>
    <style>
        .account-box { max-width: 460px; margin: 0 auto; background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow); padding: 40px 36px; }
        .account-box-icon {
            width: 64px; height: 64px; border-radius: 50%; background: rgba(15,59,94,.08); color: var(--primary);
            display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin: 0 auto 20px;
        }
        .account-box h2 { text-align: center; font-size: 1.3rem; margin-bottom: 8px; }
        .account-box p.lead { text-align: center; color: var(--gray); font-size: .85rem; margin-bottom: 28px; }
        .account-box .form-group { margin-bottom: 16px; }
        .account-box label { display: block; font-size: .78rem; font-weight: 700; color: var(--dark); margin-bottom: 6px; }
        .account-box input { width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: var(--radius); font-size: .9rem; }
        .account-box input:focus { outline: none; border-color: var(--secondary); box-shadow: 0 0 0 4px rgba(220,38,38,.1); }
        .account-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: .82rem; padding: 12px 16px; border-radius: var(--radius); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .account-help { text-align: center; font-size: .78rem; color: var(--gray); margin-top: 22px; }
        .account-help a { color: var(--primary); font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>

@include('site.partials.nav', ['compagnie' => $compagnie])

<section class="page-header">
    <span class="deco-blob blob-1"></span>
    <span class="deco-blob blob-2"></span>
    <div class="container">
        <h1 data-aos="fade-up">Mon compte</h1>
        <p data-aos="fade-up" data-aos-delay="100">Retrouvez votre billet à tout moment, sans avoir besoin de créer un compte.</p>
    </div>
</section>

<section>
    <div class="container">
        <div class="account-box" data-aos="fade-up">
            <div class="account-box-icon"><i class="fas fa-ticket"></i></div>
            <h2>Retrouver mon billet</h2>
            <p class="lead">Indiquez le numéro de votre billet et le numéro de téléphone utilisé lors de la réservation.</p>

            @if ($erreur)
                <div class="account-error"><i class="fas fa-circle-exclamation"></i> {{ $erreur }}</div>
            @endif

            <form method="GET" action="{{ route('site.mon-compte') }}">
                <div class="form-group">
                    <label>Numéro de billet</label>
                    <input type="text" name="numero" placeholder="Ex : BIL-2026-000123" value="{{ old('numero', $numeroSaisi) }}" required>
                </div>
                <div class="form-group">
                    <label>Téléphone utilisé à la réservation</label>
                    <input type="tel" name="telephone" placeholder="Ex : 77 41 37 57" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Retrouver mon billet</button>
            </form>

            <p class="account-help">Besoin d'aide ? <a href="{{ route('site.contact') }}">Contactez-nous</a></p>
        </div>
    </div>
</section>

@include('site.partials.footer', ['compagnie' => $compagnie])

<script src="{{ asset('assets_site/js/aos.js') }}"></script>
<script>AOS.init({ duration: 600, once: true, offset: 50 });</script>
</body>
</html>
