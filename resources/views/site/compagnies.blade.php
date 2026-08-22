<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Nos Compagnies - TransGest</title>
    <link rel="icon" href="{{ asset('assets_site/img/favicon.svg') }}">
    <link href="{{ asset('assets_site/css/inter.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/site-common.css') }}" rel="stylesheet">
    <style>
        /* ========== FILTER SECTION ========== */
        .filter-section {
            margin-top: -30px;
            position: relative;
            z-index: 10;
        }
        .filter-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 20px 30px;
            box-shadow: var(--shadow-md);
        }
        .filter-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }
        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
            background: var(--gray-light);
            padding: 5px 15px;
            border-radius: 50px;
            width: 100%;
            max-width: 360px;
        }
        .search-box input {
            border: none;
            background: none;
            padding: 10px 0;
            width: 200px;
            outline: none;
        }
        .search-box i {
            color: var(--gray);
        }

        /* ========== COMPANY CARDS ========== */
        .company-card {
            background: white;
            border-radius: var(--radius-xl);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow);
            position: relative;
        }
        .company-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }
        .company-cover {
            height: 140px;
            background: linear-gradient(135deg, #0f3b5e, #1a5276);
            position: relative;
        }
        .company-logo {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -50px auto 0;
            position: relative;
            z-index: 2;
            box-shadow: var(--shadow-md);
        }
        .company-logo i {
            font-size: 3rem;
            color: var(--primary);
        }
        .company-logo img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .company-content {
            padding: 20px;
            text-align: center;
        }
        .company-name {
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .company-desc {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 16px;
            line-height: 1.5;
        }
        .company-stats {
            display: flex;
            justify-content: space-around;
            padding: 15px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            margin-bottom: 16px;
        }
        .stat {
            text-align: center;
        }
        .stat-value {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary);
        }
        .stat-label {
            font-size: 0.7rem;
            color: var(--gray);
        }
        .company-footer {
            display: flex;
            gap: 12px;
        }
        .company-footer .btn {
            flex: 1;
            padding: 10px;
            font-size: 0.8rem;
        }

        /* ========== CTA SECTION ========== */
        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            text-align: center;
            padding: 60px;
            border-radius: var(--radius-xl);
            margin: 40px 0;
        }
        .cta-section h2 {
            color: white;
            font-size: 2rem;
            margin-bottom: 16px;
        }
        .cta-section p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 30px;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box {
                max-width: none;
            }
            .company-stats {
                flex-wrap: wrap;
                gap: 10px;
            }
            .cta-section {
                padding: 40px 20px;
            }
            .cta-section h2 {
                font-size: 1.5rem;
            }
        }
        @media (max-width: 480px) {
            .company-footer {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

@include('site.partials.nav')

<!-- PAGE HEADER -->
<section class="page-header">
    <div class="container">
        <h1 data-aos="fade-up">Nos compagnies partenaires</h1>
        <p data-aos="fade-up" data-aos-delay="100">Des transporteurs fiables et agréés pour vos déplacements au Mali</p>
    </div>
</section>

<!-- FILTER SECTION -->
<section class="filter-section">
    <div class="container">
        <div class="filter-card" data-aos="fade-up">
            <div class="filter-group">
                <span style="font-weight: 600; color: var(--dark);">{{ $compagnies->count() }} compagnie(s) partenaire(s)</span>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="rechercheCompagnie" placeholder="Rechercher une compagnie...">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- COMPAGNIES GRID -->
<section>
    <div class="container">
        <div id="grilleCompagnies" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px;">
            @forelse ($compagnies as $i => $c)
                <div class="company-card" data-nom="{{ strtolower($c->nom_compagnie) }}" data-aos="fade-up" data-aos-delay="{{ ($i + 1) * 100 }}">
                    <div class="company-cover"></div>
                    <div class="company-logo">
                        @if ($c->logo)
                            <img src="{{ asset('images/logos/'.$c->logo) }}" alt="{{ $c->nom_compagnie }}" style="width: 100%; height: 100%; object-fit: contain; border-radius: 20px;">
                        @else
                            <i class="fas fa-bus"></i>
                        @endif
                    </div>
                    <div class="company-content">
                        <h3 class="company-name">{{ $c->nom_compagnie }}</h3>
                        <p class="company-desc">{{ $c->slogant ?: 'Transport sécurisé et fiable au Mali.' }}</p>
                        <div class="company-stats">
                            <div class="stat"><div class="stat-value">{{ $statsParCompagnie[$c->id_compagnie]['trajets'] ?? 0 }}</div><div class="stat-label">Trajets</div></div>
                            <div class="stat"><div class="stat-value">{{ $statsParCompagnie[$c->id_compagnie]['destinations'] ?? 0 }}</div><div class="stat-label">Destinations</div></div>
                        </div>
                        <div class="company-footer">
                            <a href="{{ route('site.compagnie.trajets', $c) }}" class="btn btn-primary" style="flex: 1; text-decoration: none; padding: 10px; font-size: 0.8rem; text-align: center;">Voir les trajets</a>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p style="color: var(--gray);">Aucune compagnie disponible pour le moment.</p>
                </div>
            @endforelse
        </div>
        <p id="aucunResultat" style="display:none; text-align:center; padding: 40px; color: var(--gray);">Aucune compagnie ne correspond à votre recherche.</p>
    </div>
</section>

<!-- CTA SECTION -->
<section>
    <div class="container">
        <div class="cta-section" data-aos="fade-up">
            <h2>Vous êtes une compagnie de transport ?</h2>
            <p>Rejoignez TransGest et développez votre clientèle</p>
            <a href="{{ route('site.partenaire.login') }}" class="btn btn-outline-light btn-lg" style="text-decoration:none;">Devenir partenaire <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <h4>TransGest</h4>
                <p style="font-size: 0.85rem;">La plateforme N°1 de réservation de billets de bus et suivi de colis au Mali.</p>
            </div>
            <div>
                <h4>Liens rapides</h4>
                <a href="{{ route('site.home') }}">Accueil</a>
                <a href="{{ route('site.compagnies') }}">Compagnies</a>
                <a href="{{ route('site.contact') }}">Contact</a>
            </div>
            <div>
                <h4>Support</h4>
                <a href="#" onclick="tgBientot(event)">FAQ</a>
                <a href="#" onclick="tgBientot(event)">Conditions générales</a>
                <a href="#" onclick="tgBientot(event)">Politique de confidentialité</a>
            </div>
            <div>
                <h4>Contact</h4>
                <a href="tel:+22390259438"><i class="fas fa-phone"></i> +223 90 25 94 38</a>
                <a href="mailto:transgest@gmail.com"><i class="fas fa-envelope"></i> transgest@gmail.com</a>
                <a href="#"><i class="fas fa-map-marker-alt"></i> Pelegana, Segou, Mali</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>Copyright &copy; 2026 Computer Service Barry. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="{{ asset('assets_site/js/aos.js') }}"></script>
<script>
    AOS.init({ duration: 600, once: true, offset: 50 });

    // Recherche en direct parmi les compagnies affichées
    const rechercheInput = document.getElementById('rechercheCompagnie');
    const cartesCompagnies = document.querySelectorAll('#grilleCompagnies .company-card');
    const aucunResultat = document.getElementById('aucunResultat');
    if (rechercheInput) {
        rechercheInput.addEventListener('input', function() {
            const terme = this.value.trim().toLowerCase();
            let visibles = 0;
            cartesCompagnies.forEach(function(carte) {
                const correspond = carte.getAttribute('data-nom').includes(terme);
                carte.style.display = correspond ? '' : 'none';
                if (correspond) visibles++;
            });
            if (aucunResultat) {
                aucunResultat.style.display = visibles === 0 ? 'block' : 'none';
            }
        });
    }
</script>
</body>
</html>
