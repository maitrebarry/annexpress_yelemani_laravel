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
        /* ========== PAGE HEADER (variante avec blobs animés + stats) ==========
           Le motif de fond (points + blobs flottants) vit désormais dans site-common.css
           (.page-header::before + .deco-blob), partagé par toutes les pages du site. */
        .page-header { padding: 64px 0 40px; }
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

        /* ========== LISTE DE VOYAGES — cartes "billet d'embarquement" ==========
           Refonte 2026-08-26 à la demande de l'utilisateur : l'ancienne carte (bandeau
           dégradé + corps) est remplacée par une carte façon "billet de bus" (corps +
           talon détachable relié par un pointillé perforé), plus proche du métier. */
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

        .tickets-list { display: flex; flex-direction: column; gap: 18px; }

        .ticket-card {
            display: flex; background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow);
            position: relative; overflow: visible;
            transition: transform 0.3s cubic-bezier(.22,1,.36,1), box-shadow 0.3s ease, opacity .25s ease;
        }
        .ticket-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
        .ticket-card.is-filtered-out { opacity: 0; transform: scale(.94); pointer-events: none; }

        .ticket-main { flex: 1; min-width: 0; padding: 24px 28px; border-radius: var(--radius-lg) 0 0 var(--radius-lg); }
        .ticket-co-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 8px; }
        .ticket-co { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.85rem; color: var(--primary); }
        .ticket-co i { color: var(--secondary); }
        .ticket-rdv { font-size: 0.74rem; color: var(--gray); display: flex; align-items: center; gap: 5px; }

        .ticket-route-row { display: flex; align-items: center; gap: 18px; }
        .ticket-point { min-width: 0; }
        .ticket-point.align-right { text-align: right; }
        .ticket-time { display: block; font-size: 1.6rem; font-weight: 800; color: var(--dark); line-height: 1.1; }
        .ticket-time.is-empty { visibility: hidden; }
        .ticket-city { display: block; font-weight: 700; font-size: 0.95rem; color: var(--primary); margin-top: 4px; }
        .ticket-gare { display: block; font-size: 0.72rem; color: var(--gray); margin-top: 2px; }

        .ticket-path { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; }
        .ticket-path-line { width: 100%; border-top: 2px dashed #d8dfe8; }
        .ticket-path i.fa-bus {
            position: relative; margin-top: -11px; background: white; padding: 0 10px;
            color: var(--secondary); font-size: 1.05rem; transition: transform 0.3s ease;
        }
        .ticket-card:hover .ticket-path i.fa-bus { transform: translateX(4px); }

        .ticket-escales { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 18px; padding-top: 16px; border-top: 1px dashed #eef1f5; }
        .ticket-escales .escale-label { width: 100%; font-size: 0.72rem; color: var(--gray); display: flex; align-items: center; gap: 5px; margin-bottom: 2px; }
        .ticket-escales .escale-chip { background: #e8f4fd; color: var(--primary); padding: 3px 10px; border-radius: 50px; font-size: 0.72rem; font-weight: 600; }

        .ticket-stub {
            flex: 0 0 190px; position: relative; border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
            background: linear-gradient(160deg, var(--primary) 0%, var(--primary-dark) 100%); color: white;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 8px; padding: 22px 18px; text-align: center;
        }
        .ticket-stub::before {
            content: ''; position: absolute; left: 0; top: 16px; bottom: 16px; border-left: 2px dashed rgba(255, 255, 255, 0.35);
        }
        .ticket-notch { position: absolute; left: -10px; width: 20px; height: 20px; background: #f5f7fb; border-radius: 50%; z-index: 2; }
        .ticket-notch.top { top: -10px; }
        .ticket-notch.bottom { bottom: -10px; }
        .ticket-stub-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.75; }
        .ticket-stub-price { font-size: 1.5rem; font-weight: 800; }
        .ticket-stub-btn {
            margin-top: 4px; background: var(--secondary); color: white; border: none; padding: 9px 18px;
            border-radius: 50px; font-weight: 700; font-size: 0.82rem; cursor: pointer; transition: all 0.3s;
            display: inline-flex; align-items: center; gap: 6px; position: relative; overflow: hidden;
        }
        .ticket-stub-btn:hover { background: var(--secondary-dark); transform: scale(1.05); box-shadow: 0 8px 18px rgba(0, 0, 0, 0.25); }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 992px) {
            .filters-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .filters-grid { grid-template-columns: 1fr; }
            .section-header { flex-direction: column; align-items: flex-start; }
            .ticket-card { flex-direction: column; }
            .ticket-main { border-radius: var(--radius-lg) var(--radius-lg) 0 0; padding: 20px 22px; }
            .ticket-route-row { gap: 10px; }
            .ticket-time { font-size: 1.3rem; }
            .ticket-stub {
                flex: none; flex-direction: row; justify-content: space-between; text-align: left;
                border-radius: 0 0 var(--radius-lg) var(--radius-lg); padding: 16px 22px;
            }
            .ticket-stub::before { left: 18px; right: 18px; top: 0; bottom: auto; border-left: none; border-top: 2px dashed rgba(255, 255, 255, 0.35); }
            .ticket-notch { top: -10px; left: 14px; }
            .ticket-notch.bottom { top: -10px; bottom: auto; left: auto; right: 14px; }
        }
    </style>
</head>
<body>

@include('site.partials.nav', ['compagnie' => $compagnie])

<!-- PAGE HEADER -->
<section class="page-header">
    <span class="deco-blob blob-1"></span>
    <span class="deco-blob blob-2"></span>

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

                <div class="tickets-list">
                    @foreach ($programmesDepart as $index => $programme)
                        <div class="ticket-card" data-aos="fade-up" data-aos-delay="{{ ($index % 4) * 70 }}"
                             data-depart="{{ trim($programme->departLocalite ?? '') }}"
                             data-destination="{{ trim($programme->destinationLocalite ?? '') }}"
                             data-prix="{{ (float) $programme->prix }}"
                             data-heure="{{ $programme->heureDepart ?? '' }}">
                            <div class="ticket-main">
                                <div class="ticket-co-row">
                                    <span class="ticket-co"><i class="fas fa-bus"></i> {{ $compagnie->nom_compagnie }}</span>
                                    @if ($programme->rdv)
                                        <span class="ticket-rdv"><i class="fas fa-map-pin"></i> RDV {{ $programme->rdv }}</span>
                                    @endif
                                </div>

                                <div class="ticket-route-row">
                                    <div class="ticket-point">
                                        <span class="ticket-time">{{ $programme->heureDepart ? substr($programme->heureDepart, 0, 5) : '--:--' }}</span>
                                        <span class="ticket-city">{{ $programme->departLocalite ?? 'Départ' }}</span>
                                        <span class="ticket-gare">Gare {{ $programme->numeroGare1 ?: '-' }}</span>
                                    </div>
                                    <div class="ticket-path">
                                        <span class="ticket-path-line"></span>
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <div class="ticket-point align-right">
                                        <span class="ticket-time is-empty">--:--</span>
                                        <span class="ticket-city">{{ $programme->destinationLocalite ?? 'Destination' }}</span>
                                        <span class="ticket-gare">Gare {{ $programme->numeroGare2 ?: '-' }}</span>
                                    </div>
                                </div>

                                @if ($programme->escales_avec_frais)
                                    <div class="ticket-escales">
                                        <span class="escale-label"><i class="fas fa-code-branch"></i> Escales disponibles</span>
                                        @foreach (explode(', ', $programme->escales_avec_frais) as $escale)
                                            <span class="escale-chip">{{ $escale }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="ticket-stub">
                                <span class="ticket-notch top"></span>
                                <span class="ticket-notch bottom"></span>
                                <span class="ticket-stub-label">Prix du billet</span>
                                <strong class="ticket-stub-price">{{ number_format((float) $programme->prix, 0, ',', ' ') }} FCFA</strong>
                                <button type="button" class="ticket-stub-btn" onclick="openReservationModal({{ $programme->idProgrammer }})">
                                    Réserver <i class="fas fa-arrow-right"></i>
                                </button>
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

@include('site.partials.footer', ['compagnie' => $compagnie])

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

            document.querySelectorAll('.ticket-card').forEach(function (card) {
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
                const anyVisible = Array.from(group.querySelectorAll('.ticket-card')).some(function (c) {
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
            document.querySelectorAll('.tickets-list').forEach(function (list) {
                const cards = Array.from(list.querySelectorAll('.ticket-card'));
                cards.sort(function (a, b) {
                    if (mode === 'prix-asc') return parseFloat(a.dataset.prix) - parseFloat(b.dataset.prix);
                    if (mode === 'prix-desc') return parseFloat(b.dataset.prix) - parseFloat(a.dataset.prix);
                    return (a.dataset.heure || '').localeCompare(b.dataset.heure || '');
                });
                cards.forEach(function (card) { list.appendChild(card); });
            });
        });
    })();

    // Effet ripple sur les boutons
    document.querySelectorAll('.btn-filter, .ticket-stub-btn').forEach(function (btn) {
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
