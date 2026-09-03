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
    <title>Services - {{ $compagnie->nom_compagnie }}</title>
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
        .svc-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; }
        .svc-card { background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow); padding: 32px 26px; transition: transform .25s ease, box-shadow .25s ease; }
        .svc-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); }
        .svc-icon {
            width: 58px; height: 58px; border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: white; margin-bottom: 20px;
        }
        .svc-card h3 { font-size: 1.05rem; margin-bottom: 10px; }
        .svc-card p { font-size: .87rem; color: var(--gray); line-height: 1.65; margin-bottom: 14px; }
        .svc-card a { font-size: .82rem; font-weight: 700; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .svc-card a:hover { color: var(--secondary); }
        @media (max-width: 992px) { .svc-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .svc-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

@include('site.partials.nav', ['compagnie' => $compagnie])

<section class="page-header">
    <span class="deco-blob blob-1"></span>
    <span class="deco-blob blob-2"></span>
    <div class="container">
        <h1 data-aos="fade-up">Nos services</h1>
        <p data-aos="fade-up" data-aos-delay="100">Tout ce que {{ $compagnie->nom_compagnie }} met à votre disposition pour voyager et expédier en toute simplicité.</p>
    </div>
</section>

<section>
    <div class="container">
        <div class="svc-grid">
            <div class="svc-card" data-aos="fade-up" data-aos-delay="100">
                <div class="svc-icon" style="background: var(--primary);"><i class="fas fa-ticket"></i></div>
                <h3>Billetterie en ligne</h3>
                <p>Recherchez un trajet, choisissez votre horaire et réservez votre billet en quelques clics, sans passer par la gare.</p>
                <a href="{{ route('site.compagnie.trajets', $compagnie) }}">Réserver un billet <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="svc-card" data-aos="fade-up" data-aos-delay="150">
                <div class="svc-icon" style="background: var(--secondary);"><i class="fas fa-box"></i></div>
                <h3>Envoi & suivi de colis</h3>
                <p>Déposez votre colis en gare et suivez son parcours en temps réel jusqu'à sa livraison grâce à son code de suivi.</p>
                <a href="{{ route('site.suivi-colis') }}">Suivre un colis <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="svc-card" data-aos="fade-up" data-aos-delay="200">
                <div class="svc-icon" style="background: var(--accent);"><i class="fas fa-bus"></i></div>
                <h3>Location de car</h3>
                <p>Un événement, un groupe à transporter ? Contactez-nous pour louer un car adapté à votre trajet et vos dates.</p>
                <a href="{{ route('site.contact') }}">Nous contacter <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="svc-card" data-aos="fade-up" data-aos-delay="100">
                <div class="svc-icon" style="background: var(--success);"><i class="fas fa-credit-card"></i></div>
                <h3>Paiement en ligne</h3>
                <p>Réglez votre billet directement par Orange Money, en toute sécurité, avec confirmation instantanée par email.</p>
            </div>
            <div class="svc-card" data-aos="fade-up" data-aos-delay="150">
                <div class="svc-icon" style="background: var(--primary);"><i class="fas fa-route"></i></div>
                <h3>Départs quotidiens</h3>
                <p>Plusieurs départs chaque jour vers nos destinations desservies, avec des horaires réguliers et fiables.</p>
                <a href="{{ route('site.compagnie.trajets', $compagnie) }}">Voir les destinations <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="svc-card" data-aos="fade-up" data-aos-delay="200">
                <div class="svc-icon" style="background: var(--secondary);"><i class="fas fa-headset"></i></div>
                <h3>Support client</h3>
                <p>Une équipe disponible pour répondre à vos questions, par téléphone, WhatsApp ou via notre formulaire de contact.</p>
                <a href="{{ route('site.contact') }}">Nous écrire <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

@include('site.partials.footer', ['compagnie' => $compagnie])

<script src="{{ asset('assets_site/js/aos.js') }}"></script>
<script>AOS.init({ duration: 600, once: true, offset: 50 });</script>
</body>
</html>
