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
    <title>Résultats de recherche - Sirali</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets_site/img/favicon.svg') }}">
    <link href="{{ asset('assets_site/css/inter.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets_site/css/all.min.css') }}">
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
        .search-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 28px;
            margin-top: -32px;
            position: relative;
            z-index: 5;
        }
        .search-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            align-items: end;
        }
        .form-group label {
            display: block; font-size: 0.75rem; font-weight: 600; margin-bottom: 8px;
            color: var(--gray); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .form-control, .form-select {
            width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: var(--radius);
            font-size: 0.9rem;
        }
        .form-control:focus, .form-select:focus { outline: none; border-color: var(--secondary); }

        .results-count { margin: 32px 0 16px; color: var(--gray); font-size: 0.9rem; }
        .results-section-title { font-size: 1.15rem; display: flex; align-items: center; gap: 10px; color: var(--dark); }
        .results-section-title i { color: var(--secondary); }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .result-card {
            background: white;
            border-radius: var(--radius-lg);
            border-left: 4px solid var(--primary);
            box-shadow: var(--shadow);
            padding: 22px;
            transition: all 0.3s;
        }
        .result-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-left-color: var(--secondary);
        }
        .result-card-top {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;
        }
        .result-compagnie {
            display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: var(--gray);
        }
        .result-compagnie img { width: 24px; height: 24px; object-fit: contain; border-radius: 4px; }
        .result-price {
            font-weight: 700; font-size: 0.8rem; color: white; background: var(--secondary);
            padding: 5px 14px; border-radius: 20px; white-space: nowrap;
        }
        .result-route {
            font-size: 1.05rem; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; color: var(--dark);
        }
        .result-route i { color: var(--gray); font-size: 0.8rem; }
        .result-heure {
            font-size: 0.85rem; color: var(--gray); display: flex; align-items: center; gap: 6px; margin-bottom: 16px;
        }
        .result-book {
            display: block; text-align: center; background: var(--gray-light); color: var(--primary);
            font-weight: 600; font-size: 0.85rem; padding: 10px; border-radius: var(--radius);
            text-decoration: none; transition: all 0.2s;
        }
        .result-book:hover { background: var(--primary); color: white; }

        .no-results {
            text-align: center; padding: 60px 20px; background: white; border-radius: var(--radius-lg);
            box-shadow: var(--shadow); color: var(--gray);
        }
        .no-results i { font-size: 2.5rem; color: var(--gray-light); margin-bottom: 16px; display: block; }

        @media (max-width: 992px) {
            .search-grid { grid-template-columns: repeat(3, 1fr); }
            .results-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .search-grid { grid-template-columns: 1fr; }
            .results-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

@include('site.partials.nav', ['compagnie' => $compagnie])

<section class="page-header" style="padding: 40px 0;">
    <span class="deco-blob blob-1"></span>
    <span class="deco-blob blob-2"></span>
    <div class="container">
        <h1 style="font-size: 1.8rem; margin-bottom: 8px;">Résultats de recherche</h1>
        <p style="opacity: 0.85; font-size: 0.9rem;">
            {{ $depart !== '' ? $depart : 'Toutes les villes' }}
            <i class="fas fa-long-arrow-alt-right"></i>
            {{ $destination !== '' ? $destination : 'Toutes les destinations' }}
        </p>
    </div>
</section>

<section style="padding-top: 0;">
    <div class="container">
        <div class="search-card" data-aos="fade-up">
            <form action="{{ route('site.recherche') }}" method="GET" class="search-grid">
                <div class="form-group">
                    <label>Départ</label>
                    <select name="depart" class="form-select">
                        <option value="">Toutes les villes</option>
                        @foreach ($villes as $ville)
                            <option value="{{ $ville }}" @selected($depart === $ville)>{{ $ville }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Destination</label>
                    <select name="destination" class="form-select">
                        <option value="">Toutes les destinations</option>
                        @foreach ($villes as $ville)
                            <option value="{{ $ville }}" @selected($destination === $ville)>{{ $ville }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}" min="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Rechercher</button>
                </div>
            </form>
        </div>

        @if ($resultatsRetour !== null)
            <h2 class="results-section-title"><i class="fas fa-arrow-right"></i> Aller — {{ $date ?: 'toutes dates' }}</h2>
        @endif
        <p class="results-count">{{ $resultats->count() }} trajet(s) trouvé(s)</p>

        @if ($resultats->isNotEmpty())
            <div class="results-grid">
                @foreach ($resultats as $r)
                    <div class="result-card" data-aos="fade-up">
                        <div class="result-card-top">
                            <span class="result-compagnie">
                                @if ($r->logo)
                                    <img src="{{ asset('images/logos/'.$r->logo) }}" alt="">
                                @endif
                                {{ $r->nom_compagnie }}
                            </span>
                            <span class="result-price">{{ number_format((float) $r->prix, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="result-route">
                            {{ $r->departLocalite }} <i class="fas fa-long-arrow-alt-right"></i> {{ $r->destinationLocalite }}
                        </div>
                        <p class="result-heure"><i class="far fa-clock"></i> Départ à {{ substr($r->heureDepart, 0, 5) }}</p>
                        <a href="#" onclick="openReservationModal({{ $r->idProgrammer }}); return false;" class="result-book">Réserver <i class="fas fa-arrow-right"></i></a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-results">
                <i class="fas fa-route"></i>
                Aucun trajet ne correspond à votre recherche.<br>Essayez une autre ville ou une autre compagnie.
            </div>
        @endif

        @if ($resultatsRetour !== null)
            <h2 class="results-section-title mt-5"><i class="fas fa-arrow-left"></i> Retour — {{ $dateRetour }}</h2>
            <p class="results-count">{{ $resultatsRetour->count() }} trajet(s) trouvé(s)</p>

            @if ($resultatsRetour->isNotEmpty())
                <div class="results-grid">
                    @foreach ($resultatsRetour as $r)
                        <div class="result-card" data-aos="fade-up">
                            <div class="result-card-top">
                                <span class="result-compagnie">
                                    @if ($r->logo)
                                        <img src="{{ asset('images/logos/'.$r->logo) }}" alt="">
                                    @endif
                                    {{ $r->nom_compagnie }}
                                </span>
                                <span class="result-price">{{ number_format((float) $r->prix, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="result-route">
                                {{ $r->departLocalite }} <i class="fas fa-long-arrow-alt-right"></i> {{ $r->destinationLocalite }}
                            </div>
                            <p class="result-heure"><i class="far fa-clock"></i> Départ à {{ substr($r->heureDepart, 0, 5) }}</p>
                            <a href="#" onclick="openReservationModal({{ $r->idProgrammer }}); return false;" class="result-book">Réserver <i class="fas fa-arrow-right"></i></a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-results">
                    <i class="fas fa-route"></i>
                    Aucun trajet retour ne correspond à votre recherche.
                </div>
            @endif
        @endif
    </div>
</section>

@include('site.partials.reservation-modal')

@include('site.partials.footer', ['compagnie' => $compagnie])

<script src="{{ asset('assets_site/js/aos.js') }}"></script>
<script>AOS.init({ duration: 600, once: true, offset: 50 });</script>
</body>
</html>
