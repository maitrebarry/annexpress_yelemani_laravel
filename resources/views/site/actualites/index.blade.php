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
    <title>Actualités - {{ $compagnie->nom_compagnie }}</title>
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
        .news-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
        .news-card { background: white; border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow); text-decoration: none; color: var(--dark); transition: transform .25s ease, box-shadow .25s ease; display: flex; flex-direction: column; }
        .news-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); color: var(--dark); }
        .news-card-image { height: 190px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); position: relative; overflow: hidden; }
        .news-card-image img { width: 100%; height: 100%; object-fit: cover; }
        .news-card-image .deco-pattern { opacity: .5; }
        .news-card-image i { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: rgba(255,255,255,.5); }
        .news-card-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
        .news-card-date { font-size: .72rem; font-weight: 700; color: var(--secondary); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
        .news-card-title { font-size: 1.05rem; font-weight: 700; margin-bottom: 10px; line-height: 1.35; }
        .news-card-excerpt { font-size: .85rem; color: var(--gray); line-height: 1.6; flex: 1; }
        .news-card-more { font-size: .82rem; font-weight: 700; color: var(--primary); margin-top: 14px; display: flex; align-items: center; gap: 6px; }
        .news-empty { text-align: center; padding: 60px 20px; color: var(--gray); }
        .news-empty i { font-size: 2.5rem; color: #dbe2ea; margin-bottom: 16px; display: block; }
        @media (max-width: 992px) { .news-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .news-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

@include('site.partials.nav', ['compagnie' => $compagnie])

<section class="page-header">
    <span class="deco-blob blob-1"></span>
    <span class="deco-blob blob-2"></span>
    <div class="container">
        <h1 data-aos="fade-up">Actualités</h1>
        <p data-aos="fade-up" data-aos-delay="100">Les dernières nouvelles de {{ $compagnie->nom_compagnie }} : nouveautés, promotions et informations pratiques.</p>
    </div>
</section>

<section>
    <div class="container">
        @if ($liste->isEmpty())
            <div class="news-empty" data-aos="fade-up">
                <i class="fas fa-newspaper"></i>
                Aucune actualité publiée pour le moment. Revenez bientôt !
            </div>
        @else
            <div class="news-grid">
                @foreach ($liste as $i => $a)
                    <a href="{{ route('site.actualites.show', $a) }}" class="news-card" data-aos="fade-up" data-aos-delay="{{ ($i % 3 + 1) * 100 }}">
                        <div class="news-card-image">
                            @if ($a->image)
                                <img src="{{ asset('images/actualites/'.$a->image) }}" alt="{{ $a->titre }}">
                            @else
                                <div class="deco-pattern"></div>
                                <i class="fas fa-newspaper"></i>
                            @endif
                        </div>
                        <div class="news-card-body">
                            <span class="news-card-date"><i class="far fa-calendar me-1"></i>{{ $a->date_publication->translatedFormat('d M Y') }}</span>
                            <h3 class="news-card-title">{{ $a->titre }}</h3>
                            <p class="news-card-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($a->contenu), 110) }}</p>
                            <span class="news-card-more">Lire la suite <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($liste->hasPages())
                <div class="mt-5" data-aos="fade-up">
                    {{ $liste->links() }}
                </div>
            @endif
        @endif
    </div>
</section>

@include('site.partials.footer', ['compagnie' => $compagnie])

<script src="{{ asset('assets_site/js/aos.js') }}"></script>
<script>AOS.init({ duration: 600, once: true, offset: 50 });</script>
</body>
</html>
