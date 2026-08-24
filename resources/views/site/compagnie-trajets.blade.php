<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>{{ $compagnie->nom_compagnie }} - TransGest</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets_site/img/favicon.svg') }}">
    <link href="{{ asset('assets_site/css/inter.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/site-common.css') }}" rel="stylesheet">
    <style>
        /* ========== PAGE HEADER (variante avec blobs animés) ========== */
        .page-header {
            padding: 64px 0 40px;
            position: relative;
            overflow: hidden;
        }
        .page-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.07) 1.4px, transparent 1.4px);
            background-size: 22px 22px;
            opacity: 0.6;
            pointer-events: none;
        }
        .page-header .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(2px);
            background: rgba(255, 255, 255, 0.06);
            pointer-events: none;
            animation: floatBlob 12s ease-in-out infinite;
        }
        .page-header .blob-1 { width: 260px; height: 260px; top: -110px; left: -80px; }
        .page-header .blob-2 { width: 200px; height: 200px; bottom: -100px; right: -40px; animation-direction: reverse; }
        @keyframes floatBlob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(14px, -14px) scale(1.04); }
        }
        .page-header h1 { font-size: 2.4rem; letter-spacing: -0.3px; position: relative; z-index: 2; }
        .page-header p { opacity: 0.75; position: relative; z-index: 2; }
        .header-stats {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 28px;
            margin-top: 30px;
            position: relative;
            z-index: 2;
        }
        .header-stats .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
        }
        .header-stats .stat-item i { color: var(--secondary); font-size: 0.85rem; }
        .header-stats .stat-divider { width: 1px; height: 14px; background: rgba(255, 255, 255, 0.2); }
        @media (max-width: 768px) {
            .header-stats .stat-divider { display: none; }
        }

        /* ========== FILTRES ========== */
        .filters-section { margin-top: -30px; position: relative; z-index: 10; }
        .filters-card { background: white; border-radius: var(--radius-lg); padding: 25px 30px; box-shadow: var(--shadow-md); }
        .filters-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; align-items: end; }
        .filter-group label {
            display: block; font-size: 0.75rem; font-weight: 600; margin-bottom: 8px;
            color: var(--gray); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .filter-select, .filter-input {
            width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: var(--radius);
            font-size: 0.9rem; background: #f8fafc; transition: all 0.25s ease;
        }
        .filter-select:hover, .filter-input:hover { border-color: #cbd5e1; }
        .filter-select:focus, .filter-input:focus {
            outline: none; border-color: var(--secondary); background: white;
            box-shadow: 0 0 0 4px rgba(230, 126, 34, 0.12); transform: translateY(-1px);
        }
        .btn-filter {
            background: var(--secondary); color: white; border: none; padding: 12px 20px;
            border-radius: var(--radius); font-weight: 600; cursor: pointer; transition: all 0.3s;
            width: 100%; position: relative; overflow: hidden;
        }
        .btn-filter:hover { background: var(--secondary-dark); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(230, 126, 34, 0.3); }
        .btn-filter:active { transform: translateY(0); }
        .ripple {
            position: absolute; border-radius: 50%; background: rgba(255, 255, 255, 0.5);
            transform: scale(0); animation: rippleEffect 0.6s ease-out; pointer-events: none;
        }
        @keyframes rippleEffect { to { transform: scale(3); opacity: 0; } }

        /* ========== GRILLE DES VOYAGES ========== */
        .trips-section { padding: 60px 0; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .section-header h2 { font-size: 1.6rem; font-weight: 700; }
        .result-count { color: var(--gray); font-size: 0.9rem; }
        .section-toolbar { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .sort-select {
            padding: 9px 14px; border: 2px solid #e2e8f0; border-radius: var(--radius); font-size: 0.82rem;
            font-weight: 600; color: var(--dark); background: white; cursor: pointer; transition: border-color .2s;
        }
        .sort-select:hover, .sort-select:focus { outline: none; border-color: var(--secondary); }
        .view-toggle { display: flex; background: #eef1f5; border-radius: 50px; padding: 3px; gap: 2px; }
        .view-toggle button {
            border: none; background: transparent; padding: 7px 13px; border-radius: 50px; cursor: pointer;
            color: var(--gray); font-size: 0.85rem; display: flex; align-items: center; gap: 6px; transition: all .2s;
        }
        .view-toggle button.active { background: white; color: var(--primary); box-shadow: 0 2px 6px rgba(0,0,0,.08); font-weight: 700; }

        /* Groupes repliables (<details>/<summary>) */
        .depart-group { margin-bottom: 28px; }
        .depart-group-header {
            list-style: none; cursor: pointer; user-select: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white;
            padding: 14px 20px; border-radius: var(--radius); margin-bottom: 20px;
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            font-weight: 700; font-size: 1rem; transition: border-radius .2s ease;
        }
        .depart-group-header::-webkit-details-marker { display: none; }
        .depart-group-header .count-chip {
            display: flex; align-items: center; gap: 8px; font-size: 0.78rem; font-weight: 600; opacity: 0.9;
        }
        .depart-group-header .chevron { transition: transform 0.3s ease; }
        .depart-group[open] > .depart-group-header { margin-bottom: 20px; }
        .depart-group:not([open]) > .depart-group-header { margin-bottom: 0; border-radius: var(--radius); }
        .depart-group[open] > .depart-group-header .chevron { transform: rotate(180deg); }

        /* Transition douce quand un filtre masque une carte */
        .trip-card { transition: transform 0.35s cubic-bezier(.22,1,.36,1), box-shadow 0.35s ease, opacity .25s ease; }
        .trip-card.is-filtered-out { opacity: 0; transform: scale(.92); pointer-events: none; }

        .trips-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }
        .trip-card {
            background: white; border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow);
            transition: transform 0.35s cubic-bezier(.22,1,.36,1), box-shadow 0.35s ease;
            position: relative; border: 1px solid transparent; display: flex; flex-direction: column;
        }
        .trip-card:hover { transform: translateY(-8px) scale(1.01); box-shadow: var(--shadow-lg); border-color: rgba(230, 126, 34, 0.25); }
        .trip-banner {
            background: linear-gradient(120deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 26px 24px 20px; position: relative; overflow: hidden;
        }
        .trip-banner::before {
            content: ''; position: absolute; top: -30%; right: -10%; width: 140px; height: 140px;
            background: rgba(255, 255, 255, 0.06); border-radius: 50%;
        }
        .banner-top-row { display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 1; margin-bottom: 14px; }
        .banner-time {
            display: flex; align-items: center; gap: 7px; color: white; font-weight: 700; font-size: 1.05rem;
        }
        .banner-time .dot { width: 7px; height: 7px; border-radius: 50%; background: #4ade80; animation: pulseDot 1.5s ease-in-out infinite; box-shadow: 0 0 0 3px rgba(74,222,128,.25); }
        @keyframes pulseDot { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.4; transform: scale(1.4); } }
        .banner-gare { color: rgba(255,255,255,.7); font-size: 0.72rem; display: flex; align-items: center; gap: 5px; }
        .route-path { display: flex; align-items: center; gap: 0; position: relative; margin-bottom: 12px; }
        .route-dot { width: 10px; height: 10px; border-radius: 50%; background: white; flex-shrink: 0; box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.2); }
        .route-dot.end { background: var(--secondary); }
        .route-line {
            flex: 1; height: 2px; background-image: linear-gradient(90deg, rgba(255,255,255,0.7) 50%, transparent 50%);
            background-size: 10px 2px; background-repeat: repeat-x; position: relative; animation: dashMove 1s linear infinite;
        }
        @keyframes dashMove { from { background-position: 0 0; } to { background-position: -20px 0; } }
        .route-bus-icon { color: white; font-size: 0.95rem; margin: 0 10px; transition: transform 0.35s ease; }
        .trip-card:hover .route-bus-icon { transform: translateX(4px); }
        .trip-card:hover .route-line { animation-duration: 0.4s; }
        .banner-cities { display: flex; justify-content: space-between; color: white; font-size: 0.78rem; font-weight: 600; position: relative; z-index: 1; }
        .trip-company { display: flex; align-items: center; gap: 12px; padding: 16px 20px 0 20px; }
        .company-avatar {
            width: 40px; height: 40px; background: var(--gray-light); border-radius: 50%; display: flex;
            align-items: center; justify-content: center; font-size: 1.2rem; color: var(--primary);
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .trip-card:hover .company-avatar { transform: rotate(-8deg) scale(1.08); background: #dfeaf3; }
        .company-name { font-weight: 700; font-size: 1rem; }
        .trip-details { padding: 16px 20px 0; display: flex; flex-direction: column; flex: 1; }
        .trip-info { display: flex; justify-content: space-between; margin-bottom: 16px; padding-top: 4px; }
        .info-item { text-align: center; }
        .info-label { font-size: 0.7rem; color: var(--gray); }
        .info-value { font-weight: 700; font-size: 0.9rem; }
        .trip-price {
            display: flex; justify-content: space-between; align-items: center; margin-top: auto;
            padding: 14px 20px; border-top: 1px dashed #e2e8f0; background: linear-gradient(180deg, #fbfcfe, #f4f7fb);
        }
        .price-wrap { display: flex; flex-direction: column; line-height: 1.15; }
        .price-wrap .price-label { font-size: 0.65rem; color: var(--gray); text-transform: uppercase; letter-spacing: .4px; }
        .price { font-size: 1.4rem; font-weight: 800; color: var(--secondary); }
        .btn-book {
            background: var(--primary); color: white; border: none; padding: 8px 20px; border-radius: 50px;
            font-weight: 600; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center;
            position: relative; overflow: hidden;
        }
        .btn-book:hover { background: var(--primary-dark); transform: scale(1.04); box-shadow: 0 8px 20px rgba(15, 59, 94, 0.35); }
        .btn-book i { transition: transform 0.3s ease; }
        .btn-book:hover i { transform: translateX(4px); }
        .trip-card:hover .price { animation: priceBounce 0.4s ease; }
        @keyframes priceBounce { 0% { transform: scale(1); } 40% { transform: scale(1.08); } 100% { transform: scale(1); } }

        /* ========== VUE LISTE (bascule) ========== */
        .trips-section.view-list .trips-grid { grid-template-columns: 1fr; gap: 12px; }
        .trips-section.view-list .trip-card { flex-direction: row; align-items: stretch; }
        .trips-section.view-list .trip-banner { flex: 0 0 210px; padding: 18px; display: flex; flex-direction: column; justify-content: center; }
        .trips-section.view-list .trip-company { display: none; }
        .trips-section.view-list .trip-details { flex-direction: row; align-items: center; padding: 14px 22px; gap: 24px; }
        .trips-section.view-list .trip-info { margin-bottom: 0; flex: 1; }
        .trips-section.view-list .escales-block { display: none; }
        .trips-section.view-list .trip-price { border-top: none; background: none; padding: 0; flex-shrink: 0; }
        @media (max-width: 768px) {
            .trips-section.view-list .trip-card { flex-direction: column; }
            .trips-section.view-list .trip-banner { flex: none; }
            .trips-section.view-list .trip-details { flex-direction: column; align-items: stretch; }
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 992px) {
            .filters-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .filters-grid { grid-template-columns: 1fr; }
            .trips-grid { grid-template-columns: 1fr; }
            .section-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

@include('site.partials.nav', ['compagnie' => $compagnie])

<!-- PAGE HEADER -->
<section class="page-header">
    <span class="blob blob-1"></span>
    <span class="blob blob-2"></span>

    <div class="container">
        <h1 data-aos="fade-up">
            <i class="fas fa-bus" style="font-size:1.8rem;margin-right:10px;"></i>
            {{ $compagnie->nom_compagnie }}
        </h1>
        <p data-aos="fade-up" data-aos-delay="100">
            {{ $compagnie->slogant ?: 'Découvrez tous les trajets disponibles vers vos destinations préférées' }}
        </p>

        <div class="header-stats" data-aos="fade-up" data-aos-delay="150">
            @if ($nbAgences > 0)
                <span class="stat-item"><i class="fas fa-building"></i> {{ $nbAgences }} agence{{ $nbAgences > 1 ? 's' : '' }} desservie{{ $nbAgences > 1 ? 's' : '' }}</span>
                <span class="stat-divider"></span>
            @endif
            <span class="stat-item"><i class="fas fa-clock"></i> Ponctualité garantie</span>
            <span class="stat-divider"></span>
            <span class="stat-item"><i class="fas fa-lock"></i> Paiement sécurisé</span>
        </div>
    </div>
</section>

<!-- FILTRES -->
<section class="filters-section">
    <div class="container">
        <div class="filters-card" data-aos="fade-up">
            <div class="filters-grid">
                <div class="filter-group">
                    <label><i class="fas fa-map-marker-alt"></i> Départ</label>
                    <select class="filter-select" id="filterDepart">
                        <option value="">Toutes les villes</option>
                        @foreach ($villesDepart as $ville)
                            <option value="{{ $ville }}">{{ $ville }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-flag-checkered"></i> Destination</label>
                    <select class="filter-select" id="filterDestination">
                        <option value="">Toutes les destinations</option>
                        @foreach ($villesDestination as $ville)
                            <option value="{{ $ville }}">{{ $ville }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <button class="btn-filter" id="btnRechercher" type="button"><i class="fas fa-search"></i> Rechercher</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- LISTE DES VOYAGES -->
<section class="trips-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div>
                <h2>Programmes disponibles</h2>
                <span class="result-count">
                    @if ($programmes->isNotEmpty())
                        <span class="count-number" data-count="{{ $programmes->count() }}">0</span> voyage(s) trouvé(s)
                    @else
                        Aucun voyage disponible
                    @endif
                </span>
            </div>

            @if ($programmes->isNotEmpty())
                <div class="section-toolbar">
                    <select class="sort-select" id="sortTrips">
                        <option value="heure">Trier par heure</option>
                        <option value="prix-asc">Prix croissant</option>
                        <option value="prix-desc">Prix décroissant</option>
                    </select>
                    <div class="view-toggle" id="viewToggle">
                        <button type="button" class="active" data-view="grid"><i class="fas fa-th-large"></i> Grille</button>
                        <button type="button" data-view="list"><i class="fas fa-list"></i> Liste</button>
                    </div>
                </div>
            @endif
        </div>

        <div id="noResultsFilter" style="display:none;text-align:center;padding:60px 20px;">
            <i class="fas fa-search" style="font-size:3rem;color:#ddd;margin-bottom:16px;"></i>
            <h3 style="color:var(--gray);margin-bottom:8px;">Aucun voyage ne correspond à ces critères</h3>
            <p style="color:#aaa;">Essayez une autre ville de départ ou de destination.</p>
        </div>

        @forelse ($programmesParDepart as $depart => $programmesDepart)
            <details class="depart-group" open>
                <summary class="depart-group-header" data-aos="fade-right">
                    <span><i class="fas fa-map-marker-alt"></i> Départ depuis : {{ $depart }}</span>
                    <span class="count-chip">{{ $programmesDepart->count() }} trajet(s) <i class="fas fa-chevron-down chevron"></i></span>
                </summary>

                <div class="trips-grid">
                    @foreach ($programmesDepart as $index => $programme)
                        <div class="trip-card" data-aos="fade-up" data-aos-delay="{{ ($index % 3) * 100 }}"
                             data-depart="{{ trim($programme->departLocalite ?? '') }}"
                             data-destination="{{ trim($programme->destinationLocalite ?? '') }}"
                             data-prix="{{ (float) $programme->prix }}"
                             data-heure="{{ $programme->heureDepart ?? '' }}">
                            <div class="trip-banner">
                                <div class="banner-top-row">
                                    <span class="banner-time"><span class="dot"></span> {{ $programme->heureDepart ?? '--' }}</span>
                                    @if ($programme->rdv)
                                        <span class="banner-gare"><i class="fas fa-map-pin"></i> RDV {{ $programme->rdv }}</span>
                                    @endif
                                </div>
                                <div class="route-path">
                                    <span class="route-dot start"></span>
                                    <span class="route-line"></span>
                                    <i class="fas fa-bus route-bus-icon"></i>
                                    <span class="route-line"></span>
                                    <span class="route-dot end"></span>
                                </div>
                                <div class="banner-cities">
                                    <span>{{ $programme->departLocalite ?? 'Départ' }}</span>
                                    <span>{{ $programme->destinationLocalite ?? 'Destination' }}</span>
                                </div>
                            </div>
                            <div class="trip-company">
                                <div class="company-avatar"><i class="fas fa-bus"></i></div>
                                <div class="company-name">{{ $compagnie->nom_compagnie }}</div>
                            </div>
                            <div class="trip-details">
                                <div class="trip-info">
                                    <div class="info-item">
                                        <div class="info-label">Gare départ</div>
                                        <div class="info-value">{{ $programme->numeroGare1 ?: '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Gare arrivée</div>
                                        <div class="info-value">{{ $programme->numeroGare2 ?: '-' }}</div>
                                    </div>
                                </div>

                                @if ($programme->escales_avec_frais)
                                    <div class="escales-block" style="padding-bottom:10px;">
                                        <div style="font-size:0.75rem;color:var(--gray);margin-bottom:6px;"><i class="fas fa-code-branch"></i> Escales disponibles</div>
                                        @foreach (explode(', ', $programme->escales_avec_frais) as $escale)
                                            <span style="display:inline-block;background:#e8f4fd;color:var(--primary);padding:3px 10px;border-radius:50px;font-size:0.72rem;font-weight:600;margin:2px;">{{ $escale }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="trip-price">
                                <div class="price-wrap">
                                    <span class="price-label">Prix du billet</span>
                                    <span class="price">{{ number_format((float) $programme->prix, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <a href="#" onclick="openReservationModal({{ $programme->idProgrammer }}); return false;" class="btn-book">
                                    Réserver <i class="fas fa-arrow-right" style="margin-left:5px;"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>
        @empty
            <div style="text-align:center;padding:80px 20px;">
                <i class="fas fa-bus" style="font-size:4rem;color:#ddd;margin-bottom:20px;"></i>
                <h3 style="color:var(--gray);margin-bottom:10px;">Aucun programme disponible</h3>
                <p style="color:#aaa;">Cette compagnie n'a pas encore de trajets programmés.</p>
                <a href="{{ route('site.home') }}" style="display:inline-block;margin-top:20px;padding:12px 24px;background:var(--secondary);color:white;border-radius:var(--radius);text-decoration:none;font-weight:600;">Retour à l'accueil</a>
            </div>
        @endforelse
    </div>
</section>

@include('site.partials.reservation-modal')

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div><h4>TransGest</h4><p style="font-size: 0.85rem;">La plateforme N°1 de réservation de billets de bus et suivi de colis au Mali.</p></div>
            <div><h4>Liens rapides</h4>
                <a href="{{ route('site.home') }}">Accueil</a>
                <a href="{{ route('site.compagnies') }}">Compagnies</a>
                <a href="{{ route('site.contact') }}">Contact</a>
            </div>
            <div><h4>Support</h4><a href="#" onclick="tgBientot(event)">FAQ</a><a href="#" onclick="tgBientot(event)">Conditions générales</a><a href="#" onclick="tgBientot(event)">Politique de confidentialité</a></div>
            <div><h4>Contact</h4><a href="tel:+22390259438"><i class="fas fa-phone"></i> +223 90 25 94 38</a><a href="mailto:transgest@gmail.com"><i class="fas fa-envelope"></i> transgest@gmail.com</a><a href="#"><i class="fas fa-map-marker-alt"></i> Pelegana, Segou, Mali</a></div>
        </div>
        <div class="footer-bottom"><p>Copyright &copy; 2026 Computer Service Barry. All rights reserved.</p></div>
    </div>
</footer>

<script src="{{ asset('assets_site/js/aos.js') }}"></script>
<script>
    AOS.init({ duration: 650, once: true, offset: 60, easing: 'ease-out-cubic' });

    // Compteur animé du nombre de voyages
    function animateCount(el, target) {
        const duration = 500;
        const start = performance.now();
        const from = parseInt(el.textContent, 10) || 0;
        function tick(now) {
            const progress = Math.min((now - start) / duration, 1);
            el.textContent = Math.floor(from + (target - from) * progress);
            if (progress < 1) requestAnimationFrame(tick);
            else el.textContent = target;
        }
        requestAnimationFrame(tick);
    }
    document.querySelectorAll('.count-number').forEach(function (el) {
        animateCount(el, parseInt(el.dataset.count, 10) || 0);
    });

    // Filtre départ / destination sur les vraies données des programmes (transition en
    // fondu avant le masquage réel, plutôt qu'un display:none instantané)
    (function () {
        const departSelect = document.getElementById('filterDepart');
        const destSelect = document.getElementById('filterDestination');
        const searchBtn = document.getElementById('btnRechercher');
        const noResults = document.getElementById('noResultsFilter');
        const countEl = document.querySelector('.count-number');
        if (!departSelect || !destSelect) return;

        function normalize(str) {
            return (str || '').trim().toLowerCase();
        }

        function applyFilters() {
            const dep = normalize(departSelect.value);
            const dest = normalize(destSelect.value);
            let visibleCount = 0;

            document.querySelectorAll('.trip-card').forEach(function (card) {
                const matchDep = !dep || normalize(card.dataset.depart) === dep;
                const matchDest = !dest || normalize(card.dataset.destination) === dest;
                const visible = matchDep && matchDest;

                if (visible) {
                    card.classList.remove('is-filtered-out');
                    card.style.display = '';
                    visibleCount++;
                } else if (!card.classList.contains('is-filtered-out')) {
                    card.classList.add('is-filtered-out');
                    setTimeout(function () { if (card.classList.contains('is-filtered-out')) card.style.display = 'none'; }, 250);
                }
            });

            document.querySelectorAll('.depart-group').forEach(function (group) {
                const anyVisible = Array.from(group.querySelectorAll('.trip-card')).some(function (c) {
                    return !c.classList.contains('is-filtered-out');
                });
                group.style.display = anyVisible ? '' : 'none';
            });

            if (countEl) animateCount(countEl, visibleCount);
            if (noResults) noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        departSelect.addEventListener('change', applyFilters);
        destSelect.addEventListener('change', applyFilters);
        if (searchBtn) searchBtn.addEventListener('click', applyFilters);
    })();

    // Tri des cartes (par heure ou par prix) à l'intérieur de chaque groupe de départ
    (function () {
        const sortSelect = document.getElementById('sortTrips');
        if (!sortSelect) return;

        sortSelect.addEventListener('change', function () {
            const mode = this.value;
            document.querySelectorAll('.trips-grid').forEach(function (grid) {
                const cards = Array.from(grid.querySelectorAll('.trip-card'));
                cards.sort(function (a, b) {
                    if (mode === 'prix-asc') return parseFloat(a.dataset.prix) - parseFloat(b.dataset.prix);
                    if (mode === 'prix-desc') return parseFloat(b.dataset.prix) - parseFloat(a.dataset.prix);
                    return (a.dataset.heure || '').localeCompare(b.dataset.heure || '');
                });
                cards.forEach(function (card) { grid.appendChild(card); });
            });
        });
    })();

    // Bascule vue grille / liste, mémorisée pour la prochaine visite
    (function () {
        const toggle = document.getElementById('viewToggle');
        const tripsSection = document.querySelector('.trips-section');
        if (!toggle || !tripsSection) return;

        function setView(view) {
            tripsSection.classList.toggle('view-list', view === 'list');
            toggle.querySelectorAll('button').forEach(function (btn) {
                btn.classList.toggle('active', btn.dataset.view === view);
            });
            localStorage.setItem('trajetsView', view);
        }

        toggle.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () { setView(btn.dataset.view); });
        });

        const saved = localStorage.getItem('trajetsView');
        if (saved === 'list') setView('list');
    })();

    // Effet ripple sur les boutons
    document.querySelectorAll('.btn-filter, .btn-book').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const rect = btn.getBoundingClientRect();
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            ripple.style.left = (e.clientX - rect.left) + 'px';
            ripple.style.top = (e.clientY - rect.top) + 'px';
            btn.appendChild(ripple);
            setTimeout(function () { ripple.remove(); }, 600);
        });
    });
</script>
</body>
</html>
