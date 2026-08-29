<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>{{ $compagnie->nom_compagnie }} - Réservation & suivi de colis</title>
    <link rel="icon" href="{{ asset('assets_site/img/favicon.svg') }}">
    <link href="{{ asset('assets_site/css/inter.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets_site/css/all.min.css') }}">
    <link href="{{ asset('assets_site/css/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/site-common.css') }}" rel="stylesheet">
    <style>
        /* ========== SEARCH CARD ========== */
        .search-section { margin-top: -40px; position: relative; z-index: 10; }
        .search-card { background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); padding: 32px; }
        .search-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; align-items: end; }
        .form-group label {
            display: block; font-size: 0.75rem; font-weight: 600; margin-bottom: 8px;
            color: var(--gray); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .form-control, .form-select {
            width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: var(--radius);
            font-size: 0.9rem; transition: all 0.3s;
        }
        .form-select {
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%237f8c8d'><path d='M5.5 7.5l4.5 5 4.5-5z'/></svg>");
            background-repeat: no-repeat; background-position: right 14px center; background-size: 14px;
            padding-right: 40px; cursor: pointer;
        }
        .form-control:focus, .form-select:focus { outline: none; border-color: var(--secondary); box-shadow: 0 0 0 4px rgba(230, 126, 34, 0.12); }

        /* Chips "villes populaires" sous la recherche — clic = pré-remplit le champ destination */
        .quick-chips { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 16px; }
        .quick-chips span.label { font-size: 0.72rem; color: var(--gray); text-transform: uppercase; letter-spacing: 0.5px; margin-right: 2px; }
        .quick-chip {
            border: 1px solid #e2e8f0; background: white; color: var(--primary); font-size: 0.78rem; font-weight: 600;
            padding: 6px 14px; border-radius: 50px; cursor: pointer; transition: all 0.2s;
        }
        .quick-chip:hover, .quick-chip.is-active { background: var(--primary); border-color: var(--primary); color: white; transform: translateY(-1px); }

        /* ========== MARQUEE DES VILLES DESSERVIES ========== */
        .marquee-section { background: var(--primary-dark); overflow: hidden; padding: 14px 0; }
        .marquee-track { display: flex; width: max-content; animation: marqueeScroll 30s linear infinite; }
        .marquee-track:hover { animation-play-state: paused; }
        .marquee-item {
            display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.85);
            font-size: 0.82rem; font-weight: 600; letter-spacing: 0.3px; padding: 0 28px; white-space: nowrap;
        }
        .marquee-item i { color: var(--secondary); font-size: 0.7rem; }
        @keyframes marqueeScroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* ========== SECTION HEADER ========== */
        .section-header { text-align: center; margin-bottom: 48px; }
        .section-header .eyebrow {
            display: inline-block; color: var(--secondary); font-weight: 700; font-size: 0.75rem;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;
        }
        .section-header h2 { font-size: 2rem; margin-bottom: 12px; }
        .section-header p { color: var(--gray); max-width: 600px; margin: 0 auto; }

        /* ========== DESTINATIONS : cartes "route" avec tilt 3D + filtre ========== */
        .dest-filters { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 36px; }
        .dest-filter-btn {
            border: 2px solid #e2e8f0; background: white; color: var(--gray); font-weight: 600; font-size: 0.82rem;
            padding: 8px 18px; border-radius: 50px; cursor: pointer; transition: all 0.25s;
        }
        .dest-filter-btn:hover { border-color: var(--secondary); color: var(--secondary); }
        .dest-filter-btn.is-active { background: var(--primary); border-color: var(--primary); color: white; }

        .dest-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; perspective: 1200px; }
        .route-card {
            background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow); overflow: hidden;
            transition: transform 0.15s ease, box-shadow 0.3s ease, opacity .25s ease;
            transform-style: preserve-3d; will-change: transform;
        }
        .route-card.is-filtered-out { opacity: 0; transform: scale(.92) !important; pointer-events: none; }
        .route-card:hover { box-shadow: var(--shadow-lg); }
        .route-card-top { height: 6px; }
        .route-card-body { padding: 22px 24px; }
        .route-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
        .route-card-badge { font-size: 0.7rem; font-weight: 700; color: var(--gray); text-transform: uppercase; letter-spacing: 0.5px; }
        .route-card-price { font-weight: 800; font-size: 0.85rem; color: white; padding: 5px 14px; border-radius: 20px; white-space: nowrap; }
        .route-card-cities { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
        .rc-city { font-weight: 700; font-size: 1rem; color: var(--dark); }
        .rc-path { flex: 1; display: flex; align-items: center; position: relative; min-width: 40px; }
        .rc-path .rc-dash { flex: 1; border-top: 2px dashed #dbe2ea; }
        .rc-path i.fa-bus { font-size: 0.85rem; margin: 0 7px; color: var(--secondary); transition: transform 0.3s ease; }
        .route-card:hover .rc-path i.fa-bus { transform: translateX(3px); }
        .rc-heures-label { font-size: 0.72rem; color: var(--gray); display: flex; align-items: center; gap: 6px; margin-bottom: 8px; }
        .dest-heures { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 18px; }
        .heure-badge { font-size: 0.76rem; font-weight: 600; color: var(--primary); background: var(--gray-light); padding: 4px 10px; border-radius: 20px; }
        .route-card-cta {
            display: flex; align-items: center; justify-content: space-between; padding-top: 16px;
            border-top: 1px dashed #eef1f5; text-decoration: none; color: var(--primary); font-weight: 700; font-size: 0.85rem;
        }
        .route-card-cta i { transition: transform 0.3s ease; }
        .route-card:hover .route-card-cta i { transform: translateX(5px); }

        .dest-tab-empty { text-align: center; padding: 40px; color: var(--gray); }

        /* ========== POURQUOI NOUS CHOISIR ========== */
        .why-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
        .why-card { text-align: center; padding: 30px 20px; border-radius: var(--radius-lg); transition: all 0.3s; }
        .why-card:hover { background: white; box-shadow: var(--shadow-lg); transform: translateY(-6px); }
        .why-icon {
            width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px; font-size: 1.5rem; color: white;
        }
        .why-card h4 { font-size: 1rem; margin-bottom: 8px; }
        .why-card p { font-size: 0.85rem; color: var(--gray); line-height: 1.5; }

        /* ========== TRACKING SECTION avec mini timeline animée ========== */
        .tracking-section { background: linear-gradient(135deg, #0f3b5e 0%, #0a2a44 100%); border-radius: var(--radius-lg); padding: 48px; position: relative; overflow: hidden; color: white; }
        .tracking-section .deco-pattern { opacity: 0.35; }
        .tracking-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center; position: relative; z-index: 2; }
        .tracking-info h3 { font-size: 1.8rem; margin-bottom: 16px; }
        .tracking-info p { opacity: 0.85; }
        .tracking-features { display: flex; gap: 24px; margin-top: 24px; flex-wrap: wrap; }
        .tracking-features span { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; opacity: 0.85; }
        .tracking-box { background: white; border-radius: var(--radius-lg); padding: 32px; color: var(--dark); }
        .tracking-form { display: flex; flex-direction: column; gap: 12px; }
        .input-group { display: flex; gap: 12px; }
        .input-group input { flex: 1; padding: 14px 20px; border: 1px solid #ddd; border-radius: var(--radius); font-size: 0.9rem; }

        .mini-timeline { display: flex; justify-content: space-between; margin-top: 26px; padding: 0 4px; }
        .mini-step { display: flex; flex-direction: column; align-items: center; gap: 8px; flex: 1; position: relative; }
        .mini-step::before {
            content: ''; position: absolute; top: 13px; left: -50%; width: 100%; height: 2px; background: #e2e8f0; z-index: 0;
        }
        .mini-step:first-child::before { display: none; }
        .mini-step.is-done::before { background: var(--success); transition: background 0.4s ease; }
        .mini-dot {
            width: 26px; height: 26px; border-radius: 50%; background: #f1f5f9; border: 2px solid #e2e8f0;
            display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: var(--gray);
            position: relative; z-index: 1; transition: all 0.4s ease;
        }
        .mini-step.is-done .mini-dot { background: var(--success); border-color: var(--success); color: white; }
        .mini-step.is-active .mini-dot { background: var(--secondary); border-color: var(--secondary); color: white; animation: pulseDot 1.4s infinite; }
        .mini-step span.mini-label { font-size: 0.65rem; color: var(--gray); text-align: center; }

        /* ========== COMMENT ÇA MARCHE (stepper interactif) ========== */
        .stepper { display: grid; grid-template-columns: 320px 1fr; gap: 50px; align-items: center; }
        .stepper-list { display: flex; flex-direction: column; gap: 6px; }
        .stepper-item {
            display: flex; align-items: center; gap: 16px; padding: 16px 18px; border-radius: var(--radius-lg);
            cursor: pointer; transition: all 0.25s; border: 2px solid transparent;
        }
        .stepper-item:hover { background: var(--gray-light); }
        .stepper-item.is-active { background: white; border-color: rgba(230, 126, 34, 0.25); box-shadow: var(--shadow-md); }
        .stepper-num {
            width: 38px; height: 38px; border-radius: 50%; background: var(--gray-light); color: var(--primary);
            display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; transition: all 0.25s;
        }
        .stepper-item.is-active .stepper-num { background: var(--secondary); color: white; }
        .stepper-item h4 { font-size: 0.95rem; margin-bottom: 2px; }
        .stepper-item p { font-size: 0.78rem; color: var(--gray); margin: 0; }
        .stepper-panel {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: var(--radius-xl);
            color: white; padding: 44px; min-height: 300px; position: relative; overflow: hidden;
        }
        .stepper-panel .deco-pattern { opacity: 0.4; }
        .stepper-panel-inner { position: relative; z-index: 2; }
        .stepper-panel-icon { font-size: 2.6rem; color: var(--secondary); margin-bottom: 20px; }
        .stepper-panel h3 { font-size: 1.5rem; margin-bottom: 12px; }
        .stepper-panel p { opacity: 0.85; max-width: 440px; line-height: 1.6; }

        /* ========== STATS BAR ========== */
        .stats-bar { background: var(--primary); color: white; padding: 56px 0; position: relative; overflow: hidden; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); text-align: center; gap: 32px; position: relative; z-index: 2; }
        .stats-grid h3 { font-size: 2.2rem; margin-bottom: 8px; }
        .stats-grid p { font-size: 0.85rem; opacity: 0.7; }

        /* ========== CTA FINALE ========== */
        .final-cta {
            background: linear-gradient(120deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            border-radius: var(--radius-xl); padding: 56px; text-align: center; color: white;
            position: relative; overflow: hidden;
        }
        .final-cta h2 { font-size: 2rem; margin-bottom: 12px; }
        .final-cta p { opacity: 0.9; max-width: 520px; margin: 0 auto 28px; }
        .final-cta .cta-buttons { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; position: relative; z-index: 2; }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 992px) {
            .search-grid { grid-template-columns: repeat(3, 1fr); }
            .dest-grid, .stats-grid, .why-grid { grid-template-columns: repeat(2, 1fr); }
            .tracking-grid { grid-template-columns: 1fr; }
            .tracking-section { padding: 30px; }
            .stepper { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .search-grid { grid-template-columns: 1fr; }
            .dest-grid, .stats-grid, .why-grid { grid-template-columns: 1fr; }
            .input-group { flex-direction: column; }
            .final-cta { padding: 36px 24px; }
            .final-cta h2 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

@include('site.partials.nav', ['compagnie' => $compagnie])

<!-- HERO : carousel Swiper (3 messages), pas de banque photo — dégradés de marque,
     motif "route" et la seule photo de bus authentique du projet, présentée sans
     prétendre à un lieu précis (voir décision du 2026-08-26). -->
<section class="hero-carousel">
    <div class="swiper heroSwiper">
        <div class="swiper-wrapper">

            <!-- Slide 1 : identité / recherche -->
            <div class="swiper-slide hero-slide hero-slide--1">
                <span class="deco-blob blob-1"></span>
                <span class="deco-blob blob-2"></span>
                <div class="deco-pattern"></div>
                <div class="container">
                    <div class="hero-slide-inner">
                        <div class="hero-slide-text">
                            <div class="hero-badge">✓ Compagnie agréée</div>
                            <h1>{{ $compagnie->nom_compagnie }}<br><span>Votre voyage commence ici</span></h1>
                            <p class="hero-lead">{{ $compagnie->slogant ?: "La plateforme qui simplifie vos déplacements et l'envoi de vos colis au Mali." }} Réservez en ligne, embarquez l'esprit tranquille.</p>
                            <div class="deco-route">
                                <span class="deco-dot start"></span>
                                <span class="deco-line"></span>
                                <i class="fas fa-bus deco-bus"></i>
                                <span class="deco-line"></span>
                                <span class="deco-dot end"></span>
                            </div>
                            <div class="hero-stats">
                                <div class="hero-stat">
                                    <h3 class="js-count" data-count="{{ $heroStats['destinations'] }}">0</h3>
                                    <p>Destinations</p>
                                </div>
                                <div class="hero-stat">
                                    <h3 class="js-count" data-count="{{ $heroStats['trajets'] }}">0</h3>
                                    <p>Trajets</p>
                                </div>
                                <div class="hero-stat">
                                    <h3 class="js-count" data-count="{{ $heroStats['clients'] }}">0</h3>
                                    <p>Clients</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 : suivi de colis -->
            <div class="swiper-slide hero-slide hero-slide--2">
                <span class="deco-blob blob-1"></span>
                <span class="deco-blob blob-2"></span>
                <div class="deco-pattern"></div>
                <div class="container">
                    <div class="hero-slide-inner">
                        <div class="hero-slide-text">
                            <div class="hero-badge"><i class="fas fa-box"></i> Suivi 24/7</div>
                            <h1>Un colis à envoyer ?<br><span>On s'occupe du trajet</span></h1>
                            <p class="hero-lead">Déposez-le en gare, recevez un code, suivez-le en direct jusqu'à sa livraison. Simple comme bonjour.</p>
                            <a href="{{ route('site.suivi-colis') }}" class="btn btn-secondary"><i class="fas fa-search-location"></i> Suivre un colis</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3 : confort / la seule photo réelle du projet -->
            <div class="swiper-slide hero-slide hero-slide--3">
                <div class="deco-pattern"></div>
                <div class="container">
                    <div class="hero-slide-inner has-visual">
                        <div class="hero-slide-text">
                            <div class="hero-badge"><i class="fas fa-shield-alt"></i> Voyagez sereinement</div>
                            <h1>Confort, ponctualité<br><span>et sécurité à chaque trajet</span></h1>
                            <p class="hero-lead">Des bus confortables et des départs à l'heure pour un voyage sans stress, du premier au dernier kilomètre.</p>
                            <a href="{{ route('site.compagnie.trajets', $compagnie) }}" class="btn btn-secondary">Voir nos trajets <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <div>
                            <div class="hero-visual-card">
                                <img src="{{ asset('assets_site/img/hero-slides/slide-1.jpg') }}" alt="Confort à bord">
                            </div>
                            <div class="hero-visual-caption"><i class="fas fa-check-circle" style="color:#4ade80;"></i> Confort à bord, à chaque trajet</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
</section>

<!-- RECHERCHE -->
<section class="search-section">
    <div class="container">
        <div class="search-card" data-aos="fade-up">
            <form action="{{ route('site.recherche') }}" method="GET" class="search-grid" id="homeSearchForm">
                <div class="form-group">
                    <label>Départ</label>
                    <select name="depart" class="form-select" required>
                        <option value="">Choisissez la ville</option>
                        @foreach ($villes as $ville)
                            <option value="{{ $ville }}">{{ $ville }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-flag-checkered"></i> Destination</label>
                    <select name="destination" id="homeDestSelect" class="form-select" required>
                        <option value="">Choisissez la destination</option>
                        @foreach ($villes as $ville)
                            <option value="{{ $ville }}">{{ $ville }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Date</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Rechercher</button>
                </div>
            </form>

            @if ($villes->isNotEmpty())
                <div class="quick-chips">
                    <span class="label">Populaires :</span>
                    @foreach ($villes->take(5) as $ville)
                        <button type="button" class="quick-chip" data-ville="{{ $ville }}">{{ $ville }}</button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

<!-- MARQUEE DES VILLES DESSERVIES -->
@if ($villes->isNotEmpty())
    <section class="marquee-section">
        <div class="marquee-track" id="villesMarquee">
            @foreach ($villes as $ville)
                <span class="marquee-item"><i class="fas fa-map-marker-alt"></i> {{ $ville }}</span>
            @endforeach
        </div>
    </section>
@endif

<!-- DESTINATIONS -->
<section style="background: var(--gray-light);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="eyebrow">Nos trajets</span>
            <h2>Où voulez-vous aller ?</h2>
            <p>{{ $compagnie->nom_compagnie }} dessert ces destinations, avec plusieurs départs par jour.</p>
        </div>

        @if (! empty($destinations))
            <div class="dest-filters" data-aos="fade-up">
                <button type="button" class="dest-filter-btn is-active" data-max="">Tous les trajets</button>
                <button type="button" class="dest-filter-btn" data-max="5000">Moins de 5 000 FCFA</button>
                <button type="button" class="dest-filter-btn" data-max="999999" data-min="5000">5 000 FCFA et plus</button>
            </div>

            <div class="dest-grid" id="destGrid" data-aos="fade-up">
                @foreach ($destinations as $i => $p)
                    @php $accent = ['var(--primary)', 'var(--secondary)', 'var(--accent)'][$i % 3]; @endphp
                    <div class="route-card js-tilt" data-aos="fade-up" data-aos-delay="{{ ($i % 3 + 1) * 100 }}" data-prix="{{ (float) $p->prix }}">
                        <div class="route-card-top" style="background: {{ $accent }};"></div>
                        <div class="route-card-body">
                            <div class="route-card-head">
                                <span class="route-card-badge"><i class="fas fa-bus"></i> Trajet direct</span>
                                <span class="route-card-price" style="background: {{ $accent }};">{{ number_format((float) $p->prix, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="route-card-cities">
                                <span class="rc-city">{{ $p->departLocalite }}</span>
                                <span class="rc-path"><span class="rc-dash"></span><i class="fas fa-bus"></i><span class="rc-dash"></span></span>
                                <span class="rc-city">{{ $p->destinationLocalite }}</span>
                            </div>
                            <p class="rc-heures-label"><i class="far fa-clock"></i> {{ count($p->heures) }} départ{{ count($p->heures) > 1 ? 's' : '' }} par jour</p>
                            <div class="dest-heures">
                                @foreach ($p->heures as $h)
                                    <span class="heure-badge">{{ substr($h, 0, 5) }}</span>
                                @endforeach
                            </div>
                            <a href="{{ route('site.compagnie.trajets', $compagnie) }}" class="route-card-cta">Voir ce trajet <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div id="destEmptyFilter" style="display:none;text-align:center;padding:40px 20px;color:var(--gray);" data-aos="fade-up">
                <i class="fas fa-filter" style="font-size:2rem;color:#ccc;margin-bottom:12px;display:block;"></i>
                Aucun trajet dans cette tranche de prix pour le moment.
            </div>
            <div style="text-align: center; margin-top: 36px;" data-aos="fade-up">
                <a href="{{ route('site.compagnie.trajets', $compagnie) }}" class="btn btn-primary">Voir tous nos trajets <i class="fas fa-arrow-right"></i></a>
            </div>
        @else
            <div class="dest-tab-empty">Aucun trajet programmé pour le moment.</div>
        @endif
    </div>
</section>

<!-- POURQUOI NOUS CHOISIR -->
<section>
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="eyebrow">Nos engagements</span>
            <h2>Pourquoi voyager avec nous</h2>
            <p>Quatre raisons pour lesquelles nos clients nous font confiance, trajet après trajet.</p>
        </div>
        <div class="why-grid">
            <div class="why-card" data-aos="fade-up" data-aos-delay="100">
                <div class="why-icon" style="background: var(--primary);"><i class="fas fa-clock"></i></div>
                <h4>Ponctualité</h4>
                <p>Des départs respectés à l'heure annoncée, tous les jours.</p>
            </div>
            <div class="why-card" data-aos="fade-up" data-aos-delay="200">
                <div class="why-icon" style="background: var(--secondary);"><i class="fas fa-shield-alt"></i></div>
                <h4>Sécurité</h4>
                <p>Des véhicules entretenus et des conducteurs expérimentés.</p>
            </div>
            <div class="why-card" data-aos="fade-up" data-aos-delay="300">
                <div class="why-icon" style="background: var(--accent);"><i class="fas fa-credit-card"></i></div>
                <h4>Paiement en ligne</h4>
                <p>Réservez et payez par Orange Money, sans passer par la gare.</p>
            </div>
            <div class="why-card" data-aos="fade-up" data-aos-delay="400">
                <div class="why-icon" style="background: var(--success);"><i class="fas fa-headset"></i></div>
                <h4>Support 24/7</h4>
                <p>Une équipe disponible pour répondre à toutes vos questions.</p>
            </div>
        </div>
    </div>
</section>

<!-- SUIVI COLIS -->
<section>
    <div class="container">
        <div class="tracking-section" data-aos="fade-up">
            <div class="deco-pattern"></div>
            <div class="tracking-grid">
                <div class="tracking-info">
                    <div class="hero-badge">Suivi 24/7</div>
                    <h3>Suivez vos colis en temps réel</h3>
                    <p>Entrez votre numéro de suivi et connaissez à tout moment l'emplacement exact de votre colis.</p>
                    <div class="tracking-features">
                        <span><i class="fas fa-check-circle" style="color: #4ade80;"></i> Livraison garantie</span>
                        <span><i class="fas fa-shield-alt" style="color: #ffd9a8;"></i> Colis assurés</span>
                        <span><i class="fas fa-clock" style="color: #ffd9a8;"></i> Mise à jour en direct</span>
                    </div>

                    <!-- Mini démo animée du parcours d'un colis (purement illustrative) -->
                    <div class="mini-timeline" id="miniTimeline">
                        <div class="mini-step is-done"><div class="mini-dot"><i class="fas fa-box"></i></div><span class="mini-label">Pris en charge</span></div>
                        <div class="mini-step"><div class="mini-dot"><i class="fas fa-truck"></i></div><span class="mini-label">En route</span></div>
                        <div class="mini-step"><div class="mini-dot"><i class="fas fa-warehouse"></i></div><span class="mini-label">Arrivé</span></div>
                        <div class="mini-step"><div class="mini-dot"><i class="fas fa-check"></i></div><span class="mini-label">Livré</span></div>
                    </div>
                </div>
                <div class="tracking-box">
                    <form action="{{ route('site.suivi-colis') }}" method="GET" class="tracking-form">
                        <input type="hidden" name="id_compagnie" value="{{ $compagnie->id_compagnie }}">
                        <div class="input-group">
                            <input type="text" name="code_colis" placeholder="Ex: BL-2024-001234" required>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Suivre</button>
                        </div>
                    </form>
                    <p style="font-size: 0.7rem; color: var(--gray); margin-top: 16px;">Exemple : BL-2024-001234, BL-2024-567890</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- COMMENT ÇA MARCHE (stepper interactif) -->
<section style="background: var(--gray-light);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="eyebrow">Simple et rapide</span>
            <h2>Comment ça marche ?</h2>
            <p>Réservez votre billet en 3 étapes, sans passer par la gare.</p>
        </div>

        <div class="stepper" data-aos="fade-up">
            <div class="stepper-list" id="stepperList">
                <div class="stepper-item is-active" data-step="0">
                    <div class="stepper-num">1</div>
                    <div><h4>Recherchez</h4><p>Trouvez votre trajet en quelques secondes</p></div>
                </div>
                <div class="stepper-item" data-step="1">
                    <div class="stepper-num">2</div>
                    <div><h4>Réservez & payez</h4><p>Choisissez vos places, payez en ligne</p></div>
                </div>
                <div class="stepper-item" data-step="2">
                    <div class="stepper-num">3</div>
                    <div><h4>Voyagez</h4><p>Présentez votre billet et embarquez</p></div>
                </div>
            </div>

            <div class="stepper-panel" id="stepperPanel">
                <div class="deco-pattern"></div>
                <div class="stepper-panel-inner">
                    <div class="stepper-panel-icon"><i class="fas fa-search"></i></div>
                    <h3>1. Recherchez votre trajet</h3>
                    <p>Indiquez votre ville de départ, votre destination et la date de voyage : nous affichons tous les départs disponibles avec leurs horaires et leurs prix.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="stats-bar">
    <div class="deco-pattern"></div>
    <div class="container">
        <div class="stats-grid">
            <div data-aos="zoom-in"><h3 class="js-count" data-count="{{ $heroStats['destinations'] }}">0</h3><p>Destinations</p></div>
            <div data-aos="zoom-in" data-aos-delay="100"><h3 class="js-count" data-count="{{ $heroStats['clients'] }}">0</h3><p>Clients satisfaits</p></div>
            <div data-aos="zoom-in" data-aos-delay="200"><h3 class="js-count" data-count="{{ $heroStats['trajets'] }}">0</h3><p>Trajets quotidiens</p></div>
            <div data-aos="zoom-in" data-aos-delay="300"><h3>24/7</h3><p>Support client</p></div>
        </div>
    </div>
</section>

<!-- CTA FINALE -->
<section>
    <div class="container">
        <div class="final-cta" data-aos="zoom-in">
            <span class="deco-blob blob-1"></span>
            <span class="deco-blob blob-2"></span>
            <div style="position:relative;z-index:2;">
                <h2>Prêt à embarquer ?</h2>
                <p>Réservez votre billet en ligne dès maintenant ou suivez un colis en quelques secondes.</p>
                <div class="cta-buttons">
                    <a href="{{ route('site.compagnie.trajets', $compagnie) }}" class="btn btn-outline-light"><i class="fas fa-bus"></i> Voir nos trajets</a>
                    <a href="{{ route('site.suivi-colis') }}" class="btn" style="background:white;color:var(--secondary-dark);"><i class="fas fa-box"></i> Suivre un colis</a>
                </div>
            </div>
        </div>
    </div>
</section>

@include('site.partials.footer', ['compagnie' => $compagnie])

<script src="{{ asset('assets_site/js/aos.js') }}"></script>
<script src="{{ asset('assets_site/js/swiper-bundle.min.js') }}"></script>
<script>
    AOS.init({ duration: 600, once: true, offset: 50 });

    new Swiper('.heroSwiper', {
        loop: true,
        autoplay: { delay: 6000, disableOnInteraction: false },
        speed: 700,
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    });

    // Compteurs animés (déclenchés uniquement quand ils entrent dans le viewport)
    (function () {
        function animateCount(el, target) {
            const duration = 1200;
            const start = performance.now();
            function tick(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(eased * target);
                if (progress < 1) requestAnimationFrame(tick);
                else el.textContent = target;
            }
            requestAnimationFrame(tick);
        }

        const counters = document.querySelectorAll('.js-count');
        if (!counters.length) return;

        if (!('IntersectionObserver' in window)) {
            counters.forEach(function (el) { animateCount(el, parseInt(el.dataset.count, 10) || 0); });
            return;
        }

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCount(entry.target, parseInt(entry.target.dataset.count, 10) || 0);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });

        counters.forEach(function (el) { observer.observe(el); });
    })();

    // Marquee des villes desservies : on double le contenu pour une boucle infinie fluide
    (function () {
        const track = document.getElementById('villesMarquee');
        if (!track) return;
        track.innerHTML += track.innerHTML;
    })();

    // Chips "populaires" : pré-remplissent le champ destination de la recherche
    (function () {
        const destSelect = document.getElementById('homeDestSelect');
        if (!destSelect) return;
        document.querySelectorAll('.quick-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                destSelect.value = chip.dataset.ville;
                document.querySelectorAll('.quick-chip').forEach(function (c) { c.classList.remove('is-active'); });
                chip.classList.add('is-active');
                destSelect.focus();
            });
        });
    })();

    // Tilt 3D léger sur les cartes destinations, au mouvement de la souris
    (function () {
        if (window.matchMedia('(pointer: coarse)').matches) return; // pas de tilt tactile
        document.querySelectorAll('.js-tilt').forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                const rect = card.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = 'rotateY(' + (x * 8) + 'deg) rotateX(' + (y * -8) + 'deg) translateY(-4px)';
            });
            card.addEventListener('mouseleave', function () {
                card.style.transform = '';
            });
        });
    })();

    // Filtre des destinations par tranche de prix
    (function () {
        const buttons = document.querySelectorAll('.dest-filter-btn');
        const cards = document.querySelectorAll('#destGrid .route-card');
        const empty = document.getElementById('destEmptyFilter');
        if (!buttons.length) return;

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                buttons.forEach(function (b) { b.classList.remove('is-active'); });
                btn.classList.add('is-active');

                const min = parseFloat(btn.dataset.min || '0');
                const max = btn.dataset.max ? parseFloat(btn.dataset.max) : Infinity;
                let visible = 0;

                cards.forEach(function (card) {
                    const prix = parseFloat(card.dataset.prix);
                    const match = prix >= min && prix <= max;
                    card.classList.toggle('is-filtered-out', !match);
                    if (match) visible++;
                });

                if (empty) empty.style.display = visible === 0 ? 'block' : 'none';
            });
        });
    })();

    // Mini timeline de suivi de colis : petite démo qui boucle automatiquement (illustrative)
    (function () {
        const steps = document.querySelectorAll('#miniTimeline .mini-step');
        if (!steps.length) return;
        let active = 0;
        setInterval(function () {
            active = (active + 1) % steps.length;
            steps.forEach(function (step, i) {
                step.classList.toggle('is-done', i < active);
                step.classList.toggle('is-active', i === active);
            });
        }, 2200);
    })();

    // Stepper "Comment ça marche" : clic sur une étape -> change le panneau détaillé
    (function () {
        const items = document.querySelectorAll('#stepperList .stepper-item');
        const panel = document.getElementById('stepperPanel');
        if (!items.length || !panel) return;

        const details = [
            { icon: 'fa-search', title: '1. Recherchez votre trajet', text: "Indiquez votre ville de départ, votre destination et la date de voyage : nous affichons tous les départs disponibles avec leurs horaires et leurs prix." },
            { icon: 'fa-credit-card', title: '2. Réservez et payez en ligne', text: "Choisissez votre horaire, indiquez le nombre de passagers et réglez directement par Orange Money, sans vous déplacer." },
            { icon: 'fa-ticket-alt', title: '3. Voyagez l’esprit tranquille', text: "Recevez votre billet par email, présentez-le à l’embarquement et profitez du trajet." },
        ];

        items.forEach(function (item) {
            item.addEventListener('click', function () {
                items.forEach(function (i) { i.classList.remove('is-active'); });
                item.classList.add('is-active');
                const d = details[parseInt(item.dataset.step, 10)] || details[0];
                panel.style.opacity = 0;
                setTimeout(function () {
                    panel.innerHTML = '<div class="deco-pattern"></div>' +
                        '<div class="stepper-panel-inner">' +
                        '<div class="stepper-panel-icon"><i class="fas ' + d.icon + '"></i></div>' +
                        '<h3>' + d.title + '</h3><p>' + d.text + '</p></div>';
                    panel.style.opacity = 1;
                }, 200);
            });
        });
        panel.style.transition = 'opacity 0.2s ease';
    })();
</script>
</body>
</html>
