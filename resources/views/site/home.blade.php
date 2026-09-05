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
    <title>{{ $compagnie->nom_compagnie }} - Réservation & suivi de colis</title>
    <link rel="icon" href="{{ asset('assets_site/img/favicon.svg') }}">
    <link href="{{ asset('assets_site/css/inter.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets_site/css/all.min.css') }}">
    <link href="{{ asset('assets_site/css/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/swiper-bundle.min.css') }}" rel="stylesheet">
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
        /* ================================================================
           Refonte 2026-09-03 — maquette utilisateur, fidélité explicite demandée.
           Page : hero (recherche flottante) → bandeau 4 icônes → destinations
           populaires → application + chiffres clés → footer. Rien d'autre : les
           anciennes sections (suivi colis, "comment ça marche", CTA finale) ont
           été retirées de l'accueil car absentes de la maquette (le suivi de
           colis reste accessible via son propre lien de nav / sa propre page).
           ================================================================ */

        /* ========== HERO ========== */
        .hero {
            position: relative;
            min-height: 560px;
            overflow: hidden;
            color: white;
        }
        .hero .swiper, .hero .swiper-wrapper, .hero .swiper-slide { height: 560px; }
        .hero-slide {
            position: relative;
            height: 100%;
            display: flex;
            align-items: center;
            background-size: cover;
            background-position: center;
        }
        .hero-slide::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(90deg, rgba(10,26,44,.88) 0%, rgba(10,26,44,.62) 45%, rgba(10,26,44,.25) 75%, rgba(10,26,44,.15) 100%);
        }
        .hero-slide .container { position: relative; z-index: 2; width: 100%; }
        .hero-slide-text { max-width: 560px; }
        .hero-eyebrow { font-size: .8rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; opacity: .85; margin-bottom: 14px; }
        .hero-slide-text h1 { font-size: 2.7rem; line-height: 1.15; letter-spacing: -.5px; margin-bottom: 18px; }
        .hero-slide-text h1 .accent { color: var(--secondary); display: block; position: relative; }
        .hero-slide-text h1 .accent::after { content: ''; display: block; width: 70px; height: 4px; background: var(--secondary); border-radius: 2px; margin-top: 10px; }
        .hero-lead { font-size: 1rem; opacity: .88; line-height: 1.6; max-width: 460px; margin-bottom: 26px; }
        .hero-feature-row { display: flex; gap: 30px; flex-wrap: wrap; }
        .hero-feature { display: flex; align-items: center; gap: 10px; font-size: .82rem; font-weight: 600; }
        .hero-feature i { font-size: 1.3rem; opacity: .95; }
        .hero-feature span { line-height: 1.3; }

        .hero-carousel .swiper-pagination { bottom: 24px !important; left: 24px !important; width: auto !important; text-align: left !important; }
        .hero-carousel .swiper-pagination-bullet { width: 9px; height: 9px; background: rgba(255,255,255,.5); opacity: 1; margin: 0 4px 0 0 !important; transition: all .25s; }
        .hero-carousel .swiper-pagination-bullet-active { background: var(--secondary); width: 24px; border-radius: 5px; }
        .hero-carousel .swiper-button-prev, .hero-carousel .swiper-button-next {
            width: 42px; height: 42px; background: rgba(255,255,255,.15); border-radius: 50%; backdrop-filter: blur(4px);
        }
        .hero-carousel .swiper-button-prev { left: 20px; } .hero-carousel .swiper-button-next { right: 20px; }
        .hero-carousel .swiper-button-prev::after, .hero-carousel .swiper-button-next::after { font-size: .95rem; color: white; font-weight: 700; }
        @media (max-width: 1180px) { .hero-carousel .swiper-button-prev, .hero-carousel .swiper-button-next { display: none; } }

        /* Carte de recherche flottante — persistante, hors du carrousel */
        .hero-search-wrap { position: absolute; top: 0; right: 0; height: 100%; display: flex; align-items: center; z-index: 5; pointer-events: none; }
        .hero-search-card {
            pointer-events: auto;
            background: white; color: var(--dark); border-radius: var(--radius-lg);
            box-shadow: 0 25px 60px -12px rgba(0,0,0,.4); padding: 26px 26px 22px;
            width: 320px;
        }
        .hero-search-card h3 { font-size: 1.15rem; margin-bottom: 2px; }
        .hero-search-sub { font-size: .78rem; color: var(--gray); margin-bottom: 16px; }
        .trip-toggle { display: flex; gap: 8px; margin-bottom: 18px; }
        .trip-toggle button {
            flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
            border: 1px solid #e2e8f0; background: white; color: var(--gray);
            font-size: .78rem; font-weight: 600; padding: 8px 10px; border-radius: 50px; cursor: pointer; transition: all .2s;
        }
        .trip-toggle button i { font-size: .8rem; }
        .trip-toggle button.is-active { background: var(--primary-dark); border-color: var(--primary-dark); color: white; }
        .hsc-field { margin-bottom: 12px; }
        .hsc-field label { display: block; font-size: .68rem; font-weight: 700; color: var(--gray); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
        .hsc-field .hsc-input-wrap { position: relative; }
        .hsc-field select, .hsc-field input {
            width: 100%; border: 1px solid #e2e8f0; border-radius: var(--radius); padding: 9px 34px 9px 11px;
            font-size: .85rem; font-weight: 600; color: var(--dark); appearance: none; background: white;
        }
        .hsc-field select:focus, .hsc-field input:focus { outline: none; border-color: var(--secondary); }
        .hsc-field i.field-icon { position: absolute; right: 11px; top: 50%; transform: translateY(-50%); color: var(--gray); font-size: .8rem; pointer-events: none; }
        .hsc-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .hero-search-card .btn-block { margin-top: 6px; padding: 12px; font-size: .88rem; }
        .hsc-trust { display: flex; justify-content: space-between; margin-top: 16px; font-size: .68rem; color: var(--gray); }
        .hsc-trust span { display: flex; align-items: center; gap: 5px; }
        .hsc-trust i { color: var(--success); }

        @media (max-width: 1180px) {
            .hero-search-wrap { position: static; height: auto; margin-top: -60px; padding-bottom: 30px; justify-content: center; }
            .hero { min-height: 0; overflow: visible; }
            {{-- overflow:hidden ici (sur .swiper-wrapper/.swiper-slide en plus de .swiper,
                 qui l'a déjà par défaut via swiper-bundle.min.css) empêchait Chrome de
                 peindre quoi que ce soit sur les diapositives à cette largeur — image de
                 fond ET texte, silencieusement invisibles bien que présents et correctement
                 dimensionnés dans le DOM (confirmé : un double overflow:hidden sur un
                 parent ET un enfant transformé par Swiper, à cette taille précise,
                 empêche Chrome de composer/peindre le contenu). Reproduit et corrigé en
                 le retirant : .swiper garde son overflow:hidden natif (suffisant à lui
                 seul pour masquer les diapositives hors champ), plus besoin de le
                 réappliquer sur ses enfants. --}}
            .hero .swiper, .hero .swiper-wrapper, .hero .swiper-slide { height: 640px; }
        }
        @media (max-width: 480px) {
            .hero-slide-text h1 { font-size: 2rem; }
            .hero-search-card { width: 90vw; max-width: 340px; }
        }

        /* ========== BANDEAU 4 ICÔNES ========== */
        .feature-strip { background: white; padding: 40px 0; border-bottom: 1px solid #eef1f5; }
        .feature-strip-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
        .feature-strip-item { display: flex; align-items: center; gap: 16px; }
        .feature-strip-icon {
            width: 54px; height: 54px; border-radius: 50%; background: var(--gray-light); color: var(--primary);
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;
        }
        .feature-strip-item h4 { font-size: .92rem; margin-bottom: 2px; }
        .feature-strip-item p { font-size: .78rem; color: var(--gray); margin: 0; }

        /* ========== DESTINATIONS POPULAIRES ========== */
        .dest-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .dest-header h2 { font-size: 1.5rem; margin: 0; position: relative; padding-bottom: 10px; }
        .dest-header h2::after { content: ''; position: absolute; left: 0; bottom: 0; width: 46px; height: 3px; background: var(--secondary); border-radius: 2px; }
        .dest-header a { font-size: .85rem; font-weight: 700; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 6px; }
        .dest-header a:hover { color: var(--secondary); }

        .dest-scroll-wrap { position: relative; }
        .dest-scroll {
            display: grid; grid-auto-flow: column; grid-auto-columns: 220px;
            gap: 18px; overflow-x: auto; padding-bottom: 6px; scroll-behavior: smooth; scrollbar-width: none;
        }
        .dest-scroll::-webkit-scrollbar { display: none; }
        .dest-card {
            position: relative; height: 280px; border-radius: var(--radius-lg); overflow: hidden;
            display: flex; align-items: flex-end; text-decoration: none; color: white;
            background-size: cover; background-position: center;
            box-shadow: var(--shadow-md); transition: transform .25s ease;
        }
        .dest-card:hover { transform: translateY(-4px); }
        .dest-card::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,42,68,0) 35%, rgba(9,20,33,.55) 100%); }
        .dest-card-watermark { position: absolute; top: 18px; right: 14px; font-size: 2.2rem; color: rgba(255,255,255,.25); z-index: 1; }
        .dest-card-body { position: relative; z-index: 2; padding: 16px; width: 100%; }
        .dest-card-from { display: block; font-size: .75rem; opacity: .85; margin-bottom: 2px; }
        .dest-card-to { display: block; font-size: 1.15rem; font-weight: 800; margin-bottom: 10px; }
        .dest-card-price { display: flex; align-items: center; gap: 6px; font-size: .72rem; opacity: .9; }
        .dest-card-price strong { display: block; font-size: .92rem; color: white; }
        .dest-scroll-next {
            position: absolute; top: 50%; right: -10px; transform: translateY(-50%);
            width: 42px; height: 42px; border-radius: 50%; background: white; border: 1px solid #e2e8f0;
            color: var(--primary); display: flex; align-items: center; justify-content: center; cursor: pointer;
            box-shadow: var(--shadow-md); z-index: 3;
        }
        @media (max-width: 640px) { .dest-scroll-next { display: none; } }

        /* ========== APPLICATION + CHIFFRES CLÉS ========== */
        .promo-grid { display: grid; grid-template-columns: 1fr 1.3fr; gap: 24px; align-items: stretch; }
        .app-card {
            background: var(--primary-dark); border-radius: var(--radius-lg); color: white;
            padding: 34px; display: flex; flex-direction: column; justify-content: center;
            position: relative; overflow: hidden;
        }
        .app-card h3 { font-size: 1.3rem; margin-bottom: 8px; }
        .app-card p { font-size: .85rem; opacity: .8; margin-bottom: 22px; max-width: 320px; }
        .app-store-badges { display: flex; gap: 12px; flex-wrap: wrap; }
        .app-store-badge {
            display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15); border-radius: 10px; padding: 8px 14px;
            color: white; text-decoration: none; cursor: pointer;
        }
        .app-store-badge i { font-size: 1.5rem; }
        .app-store-badge span { display: block; line-height: 1.2; }
        .app-store-badge small { font-size: .62rem; opacity: .75; display: block; }
        .app-store-badge strong { font-size: .82rem; }

        .stats-card { background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow); padding: 34px; display: flex; flex-direction: column; justify-content: center; }
        .stats-card h3 { font-size: 1.2rem; margin-bottom: 22px; }
        .stats-card-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
        .stats-card-item { text-align: left; }
        .stats-card-icon {
            width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.05rem; margin-bottom: 10px;
        }
        .stats-card-item h4 { font-size: 1.35rem; margin-bottom: 2px; }
        .stats-card-item p { font-size: .74rem; color: var(--gray); margin: 0; line-height: 1.3; }

        @media (max-width: 992px) {
            .promo-grid { grid-template-columns: 1fr; }
            .stats-card-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .feature-strip-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .stats-card-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

@include('site.partials.nav', ['compagnie' => $compagnie])

<!-- HERO : carrousel Swiper en fond plein cadre (texte propre à chaque diapositive) +
     carte de recherche flottante persistante (ne tourne pas avec le carrousel). -->
<section class="hero hero-carousel">
    <div class="swiper heroSwiper">
        <div class="swiper-wrapper">

            <!-- Slide 1 : identité / recherche -->
            <div class="swiper-slide hero-slide" style="background-image:url('{{ asset('assets_site/img/hero-bg.jpg') }}');">
                <div class="container">
                    <div class="hero-slide-text">
                        <div class="hero-eyebrow">{{ $compagnie->nom_compagnie }}</div>
                        <h1>Voyagez en toute<span class="accent">confiance</span></h1>
                        <p class="hero-lead">{{ $compagnie->slogant ?: "Des bus confortables et des départs à l'heure pour un voyage sans stress, du premier au dernier kilomètre." }}</p>
                        <div class="hero-feature-row">
                            <div class="hero-feature"><i class="fas fa-couch"></i> <span>Confort<br>Premium</span></div>
                            <div class="hero-feature"><i class="fas fa-shield-alt"></i> <span>Sécurité<br>Garantie</span></div>
                            <div class="hero-feature"><i class="far fa-clock"></i> <span>Ponctualité<br>Assurée</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 : suivi de colis — background-color en repli si la photo n'existe
                 pas encore (déposer le fichier à public/assets_site/img/hero-bg-colis.jpg
                 pour qu'elle s'affiche, même traitement que hero-bg.jpg de la slide 1). -->
            <div class="swiper-slide hero-slide" style="background-color: var(--primary-dark); background-image: linear-gradient(120deg, rgba(15,59,94,.35), rgba(10,42,68,.55)), url('{{ asset('assets_site/img/hero-bg-colis.jpg') }}');">
                <div class="container">
                    <div class="hero-slide-text">
                        <div class="hero-eyebrow"><i class="fas fa-box"></i> Suivi 24/7</div>
                        <h1>Un colis à envoyer ?<span class="accent">On s'en charge</span></h1>
                        <p class="hero-lead">Déposez-le en gare, recevez un code, suivez-le en direct jusqu'à sa livraison.</p>
                        <a href="{{ route('site.suivi-colis') }}" class="btn btn-secondary"><i class="fas fa-search-location"></i> Suivre un colis</a>
                    </div>
                </div>
            </div>

            <!-- Slide(s) 3+ : photos réelles des cars de la compagnie (une par photo, uploadées
                 depuis Configuration → Compagnie → Photos). -->
            @foreach ($compagnie->photos->take(5) as $photo)
                <div class="swiper-slide hero-slide" style="background-image:url('{{ asset('images/compagnies_photos/'.$photo->chemin) }}');">
                    <div class="container">
                        <div class="hero-slide-text">
                            <div class="hero-eyebrow"><i class="fas fa-bus"></i> À bord</div>
                            <h1>Confort, ponctualité<span class="accent">à chaque trajet</span></h1>
                            <p class="hero-lead">Des bus confortables et des départs à l'heure pour un voyage sans stress.</p>
                            <a href="{{ route('site.compagnie.trajets', $compagnie) }}" class="btn btn-secondary">Voir nos trajets <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>

    <div class="hero-search-wrap">
        <div class="hero-search-card">
            <h3>Réservez votre billet</h3>
            <p class="hero-search-sub">Rapide • Simple • Sécurisé</p>

            <div class="trip-toggle">
                <button type="button" class="is-active" id="tripSimple"><i class="fas fa-circle-dot"></i> Aller simple</button>
                <button type="button" id="tripRetour"><i class="far fa-circle"></i> Aller-retour</button>
            </div>

            <form action="{{ route('site.recherche') }}" method="GET" id="homeSearchForm">
                <div class="hsc-field">
                    <label>Départ</label>
                    <div class="hsc-input-wrap">
                        <select name="depart" required>
                            <option value="">Choisissez la ville</option>
                            @foreach ($villes as $ville)
                                <option value="{{ $ville }}">{{ $ville }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-location-dot field-icon"></i>
                    </div>
                </div>
                <div class="hsc-field">
                    <label>Arrivée</label>
                    <div class="hsc-input-wrap">
                        <select name="destination" id="homeDestSelect" required>
                            <option value="">Choisissez la destination</option>
                            @foreach ($villes as $ville)
                                <option value="{{ $ville }}">{{ $ville }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-flag-checkered field-icon"></i>
                    </div>
                </div>
                <div class="hsc-row">
                    <div class="hsc-field">
                        <label>Date de départ</label>
                        <div class="hsc-input-wrap">
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="hsc-field" id="hscDateRetourField" hidden>
                        <label>Date de retour</label>
                        <div class="hsc-input-wrap">
                            <input type="date" name="date_retour" value="{{ date('Y-m-d', strtotime('+1 day')) }}" min="{{ date('Y-m-d') }}" disabled>
                        </div>
                    </div>
                    <div class="hsc-field" id="hscPassagersField">
                        <label>Passagers</label>
                        <div class="hsc-input-wrap">
                            <select name="passagers">
                                @for ($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}">{{ $i }} passager{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                            <i class="fas fa-user field-icon"></i>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary btn-block"><i class="fas fa-search"></i> Rechercher un billet</button>
            </form>

            <div class="hsc-trust">
                <span><i class="fas fa-lock"></i> Paiement sécurisé</span>
                <span><i class="fas fa-circle-check"></i> Confirmation instantanée</span>
            </div>
        </div>
    </div>
</section>

<!-- BANDEAU 4 ICÔNES -->
<section class="feature-strip">
    <div class="container">
        <div class="feature-strip-grid">
            <div class="feature-strip-item">
                <div class="feature-strip-icon"><i class="fas fa-route"></i></div>
                <div><h4>Trajets réguliers</h4><p>Des départs quotidiens vers vos destinations</p></div>
            </div>
            <div class="feature-strip-item">
                <div class="feature-strip-icon"><i class="fas fa-box"></i></div>
                <div><h4>Suivi de colis</h4><p>Expédiez et suivez vos colis en toute simplicité</p></div>
            </div>
            <div class="feature-strip-item">
                <div class="feature-strip-icon"><i class="fas fa-headset"></i></div>
                <div><h4>Service client 24/7</h4><p>Une équipe à votre écoute à tout moment</p></div>
            </div>
            <div class="feature-strip-item">
                <div class="feature-strip-icon"><i class="fas fa-credit-card"></i></div>
                <div><h4>Paiement sécurisé</h4><p>Payez en ligne en toute sécurité</p></div>
            </div>
        </div>
    </div>
</section>

<!-- DESTINATIONS POPULAIRES -->
@if (! empty($destinations))
    <section>
        <div class="container">
            <div class="dest-header" data-aos="fade-up">
                <h2>Nos destinations populaires</h2>
                <a href="{{ route('site.compagnie.trajets', $compagnie) }}">Voir toutes les destinations <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="dest-scroll-wrap" data-aos="fade-up">
                <div class="dest-scroll" id="destScroll">
                    @php
                        // Dégradé de marque par défaut (voir note site-common.css "DÉCOR SANS
                        // PHOTO") : reste le repli pour toute localité sans photo dédiée —
                        // aucune compagnie/ville ne doit dépendre d'une photo pour s'afficher
                        // correctement (multi-tenant : chaque compagnie a ses propres villes).
                        $degrades = [
                            'linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%)',
                            'linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%)',
                            'linear-gradient(135deg, var(--accent) 0%, var(--primary) 100%)',
                        ];

                        // Quelques villes maliennes ont désormais une vraie photo (voir
                        // public/assets_site/img/destinations/). Reconnaissance par mot-clé
                        // (insensible aux accents/casse) plutôt que par nom de gare exact, pour
                        // couvrir "Sogoniko" (gare de Bamako), "Kayes N'di"/"Kayes Ba", etc.
                        $photosVilles = [
                            'bamako' => 'bamako.jpg',
                            'sogoniko' => 'bamako.jpg',
                            'kayes' => 'kayes.jpg',
                            'kita' => 'kita.jpg',
                            'diema' => 'diema.jpg',
                            'yelimane' => 'yelimane.jpg',
                        ];
                        $trouverPhoto = function (string $localite) use ($photosVilles) {
                            $normalise = strtolower(str_replace(
                                ['é', 'è', 'ê', 'à', 'â', 'î', 'ï', 'ô', 'ù', 'û'],
                                ['e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'u', 'u'],
                                $localite
                            ));
                            foreach ($photosVilles as $motCle => $fichier) {
                                if (str_contains($normalise, $motCle)) {
                                    return $fichier;
                                }
                            }
                            return null;
                        };
                    @endphp
                    @foreach ($destinations as $i => $d)
                        @php $photo = $trouverPhoto($d->destinationLocalite); @endphp
                        <a href="{{ route('site.compagnie.trajets', $compagnie) }}" class="dest-card"
                           style="background-image: {{ $photo
                                ? "linear-gradient(180deg, rgba(15,42,68,.15) 0%, rgba(9,20,33,.25) 100%), url('" . asset('assets_site/img/destinations/' . $photo) . "')"
                                : $degrades[$i % 3] }};">
                            <span class="dest-card-watermark"><i class="fas fa-map-location-dot"></i></span>
                            @if (! $photo)
                                <div class="deco-pattern"></div>
                            @endif
                            <div class="dest-card-body">
                                <span class="dest-card-from">{{ $d->departLocalite }}</span>
                                <span class="dest-card-to">{{ $d->destinationLocalite }}</span>
                                <span class="dest-card-price"><i class="fas fa-route"></i> à partir de <strong>{{ number_format((float) $d->prix, 0, ',', ' ') }} FCFA</strong></span>
                            </div>
                        </a>
                    @endforeach
                </div>
                <button type="button" class="dest-scroll-next" id="destScrollNext" aria-label="Suivant"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>
@endif

<!-- APPLICATION + CHIFFRES CLÉS -->
<section style="padding-top: 0;">
    <div class="container">
        <div class="promo-grid" data-aos="fade-up">
            <div class="app-card">
                <div class="deco-pattern"></div>
                <div style="position:relative;z-index:2;">
                    <h3>Téléchargez notre application</h3>
                    <p>Réservez vos billets, suivez vos trajets et profitez d'une meilleure expérience.</p>
                    <div class="app-store-badges">
                        <a href="#" class="app-store-badge" onclick="tgBientot(event)">
                            <i class="fab fa-google-play"></i>
                            <span><small>Disponible sur</small><strong>Google Play</strong></span>
                        </a>
                        <a href="#" class="app-store-badge" onclick="tgBientot(event)">
                            <i class="fab fa-apple"></i>
                            <span><small>Télécharger dans</small><strong>l'App Store</strong></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="stats-card">
                <h3>Pourquoi choisir {{ $compagnie->nom_compagnie }} ?</h3>
                <div class="stats-card-grid">
                    <div class="stats-card-item">
                        <div class="stats-card-icon" style="background: var(--secondary);"><i class="fas fa-users"></i></div>
                        <h4 class="js-count" data-count="{{ $heroStats['clients'] }}">0</h4>
                        <p>Voyageurs satisfaits</p>
                    </div>
                    <div class="stats-card-item">
                        <div class="stats-card-icon" style="background: var(--primary);"><i class="fas fa-bus"></i></div>
                        <h4 class="js-count" data-count="{{ $heroStats['destinations'] }}">0</h4>
                        <p>Destinations desservies</p>
                    </div>
                    <div class="stats-card-item">
                        <div class="stats-card-icon" style="background: var(--secondary);"><i class="fas fa-calendar-check"></i></div>
                        <h4 class="js-count" data-count="{{ $heroStats['trajets'] }}">0</h4>
                        <p>Trajets programmés</p>
                    </div>
                    <div class="stats-card-item">
                        <div class="stats-card-icon" style="background: var(--primary);"><i class="fas fa-headset"></i></div>
                        <h4>24/7</h4>
                        <p>Support client</p>
                    </div>
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

    // Toggle "Aller simple / Aller-retour" : bascule vers "Aller-retour" affiche un champ
    // date de retour ; la recherche revient avec deux listes de trajets (aller + retour,
    // voir RechercheController), chacune réservable séparément — pas de billet combiné,
    // comme le legacy.
    (function () {
        const simple = document.getElementById('tripSimple');
        const retour = document.getElementById('tripRetour');
        const dateRetourField = document.getElementById('hscDateRetourField');
        const dateRetourInput = dateRetourField ? dateRetourField.querySelector('input') : null;
        if (!simple || !retour) return;

        function setMode(isRetour) {
            simple.classList.toggle('is-active', !isRetour);
            simple.querySelector('i').className = isRetour ? 'far fa-circle' : 'fas fa-circle-dot';
            retour.classList.toggle('is-active', isRetour);
            retour.querySelector('i').className = isRetour ? 'fas fa-circle-dot' : 'far fa-circle';
            if (dateRetourField) dateRetourField.hidden = !isRetour;
            if (dateRetourInput) dateRetourInput.disabled = !isRetour;
        }

        simple.addEventListener('click', function () { setMode(false); });
        retour.addEventListener('click', function () { setMode(true); });
    })();

    // Défilement horizontal des destinations au clic sur la flèche
    (function () {
        const scroller = document.getElementById('destScroll');
        const next = document.getElementById('destScrollNext');
        if (!scroller || !next) return;
        next.addEventListener('click', function () {
            scroller.scrollBy({ left: 240, behavior: 'smooth' });
        });
    })();
</script>
</body>
</html>
