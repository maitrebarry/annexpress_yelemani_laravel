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
    <title>{{ $actualite->titre }} - {{ $compagnie->nom_compagnie }}</title>
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
        .news-article { max-width: 780px; margin: 0 auto; }
        .news-article-image { width: 100%; height: 340px; object-fit: cover; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); margin-bottom: 32px; }
        .news-article-date { font-size: .78rem; font-weight: 700; color: var(--secondary); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; }
        .news-article h1 { font-size: 1.9rem; margin-bottom: 24px; line-height: 1.3; }
        .news-article-body { font-size: 1rem; color: #374151; line-height: 1.8; white-space: pre-line; }
        .news-back { display: inline-flex; align-items: center; gap: 8px; font-size: .85rem; font-weight: 700; color: var(--primary); text-decoration: none; margin-bottom: 24px; }
        .news-back:hover { color: var(--secondary); }
        .news-related { margin-top: 60px; padding-top: 40px; border-top: 1px solid #eef1f5; }
        .news-related h3 { font-size: 1.2rem; margin-bottom: 22px; }
        .news-related-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .news-related-card { text-decoration: none; color: var(--dark); display: block; }
        .news-related-card img { width: 100%; height: 110px; object-fit: cover; border-radius: var(--radius); margin-bottom: 10px; }
        .news-related-card .no-image { width: 100%; height: 110px; border-radius: var(--radius); margin-bottom: 10px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.5); }
        .news-related-card span { font-size: .85rem; font-weight: 700; line-height: 1.4; }
        @media (max-width: 768px) { .news-related-grid { grid-template-columns: 1fr; } .news-article h1 { font-size: 1.5rem; } }
    </style>
</head>
<body>

@include('site.partials.nav', ['compagnie' => $compagnie])

<section style="padding-top: 48px;">
    <div class="container">
        <div class="news-article" data-aos="fade-up">
            <a href="{{ route('site.actualites') }}" class="news-back"><i class="fas fa-arrow-left"></i> Toutes les actualités</a>

            @if ($actualite->image)
                <img src="{{ asset('images/actualites/'.$actualite->image) }}" alt="{{ $actualite->titre }}" class="news-article-image">
            @endif

            <span class="news-article-date"><i class="far fa-calendar me-1"></i>{{ $actualite->date_publication->translatedFormat('d F Y') }}</span>
            <h1>{{ $actualite->titre }}</h1>
            <div class="news-article-body">{{ $actualite->contenu }}</div>

            @if ($autres->isNotEmpty())
                <div class="news-related">
                    <h3>À lire aussi</h3>
                    <div class="news-related-grid">
                        @foreach ($autres as $a)
                            <a href="{{ route('site.actualites.show', $a) }}" class="news-related-card">
                                @if ($a->image)
                                    <img src="{{ asset('images/actualites/'.$a->image) }}" alt="{{ $a->titre }}">
                                @else
                                    <div class="no-image"><i class="fas fa-newspaper"></i></div>
                                @endif
                                <span>{{ $a->titre }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

@include('site.partials.footer', ['compagnie' => $compagnie])

<script src="{{ asset('assets_site/js/aos.js') }}"></script>
<script>AOS.init({ duration: 600, once: true, offset: 50 });</script>
</body>
</html>
