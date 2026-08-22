<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Suivi de colis - TransGest</title>
    <link rel="icon" href="{{ asset('assets_site/img/favicon.svg') }}">
    <link href="{{ asset('assets_site/css/inter.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/site-common.css') }}" rel="stylesheet">
    <style>
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* ========== PAGE HEADER avec IMAGE DE FOND ========== */
        .page-header {
            position: relative;
            color: white;
            padding: 90px 0;
            text-align: center;
            overflow: hidden;
            min-height: 320px;
            display: flex;
            align-items: center;
        }
        .page-header-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }
        .page-header-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15, 59, 94, 0.75) 0%, rgba(10, 42, 68, 0.65) 100%);
            z-index: 1;
        }
        .page-header .container {
            position: relative;
            z-index: 2;
        }
        .page-header h1 {
            font-size: 2.8rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .page-header p {
            font-size: 1.05rem;
            opacity: 0.95;
            text-shadow: 0 1px 5px rgba(0,0,0,0.3);
        }

        /* ========== SECTION SUIVI ========== */
        .tracking-page-section {
            padding: 60px 0;
        }
        .tracking-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: 44px;
        }
        .tracking-card > .intro {
            text-align: center;
            max-width: 640px;
            margin: 0 auto 40px;
        }
        .tracking-card > .intro i {
            font-size: 2.2rem;
            color: var(--secondary);
            margin-bottom: 12px;
        }
        .tracking-card > .intro h2 {
            font-size: 1.6rem;
            color: var(--primary);
            margin-bottom: 8px;
        }
        .tracking-card > .intro p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        /* ---- Etapes ---- */
        .step-label {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.05rem;
        }
        .step-num {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        /* ---- Etape 1 : cartes compagnies ---- */
        .company-picker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 26px;
        }
        .company-pick-card {
            position: relative;
            background: white;
            border-radius: var(--radius-xl);
            overflow: hidden;
            cursor: pointer;
            box-shadow: var(--shadow);
            border: 3px solid transparent;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .company-pick-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }
        .company-pick-card.selected {
            border-color: var(--secondary);
            box-shadow: 0 12px 30px rgba(230, 126, 34, 0.25);
        }
        .company-pick-cover {
            height: 90px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            position: relative;
        }
        .company-pick-card.selected .company-pick-cover {
            background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
        }
        .company-pick-logo {
            width: 84px;
            height: 84px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -42px auto 0;
            position: relative;
            z-index: 2;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        .company-pick-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .company-pick-logo i {
            font-size: 2.2rem;
            color: var(--primary);
        }
        .company-pick-content {
            padding: 16px 18px 22px;
            text-align: center;
        }
        .company-pick-name {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--dark);
        }
        .company-pick-check {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: white;
            color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            opacity: 0;
            transform: scale(0.4);
            transition: all 0.25s ease;
            z-index: 3;
            box-shadow: var(--shadow);
        }
        .company-pick-card.selected .company-pick-check {
            opacity: 1;
            transform: scale(1);
        }
        .empty-companies {
            grid-column: 1 / -1;
            text-align: center;
            color: var(--gray);
            padding: 20px;
        }

        /* ---- Etape 2 : code de suivi ---- */
        .step-code {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            margin-top: 0;
            transition: max-height 0.5s ease, opacity 0.4s ease, margin 0.4s ease;
        }
        .step-code.is-open {
            max-height: 400px;
            opacity: 1;
            margin-top: 36px;
            padding-top: 32px;
            border-top: 1px dashed #e2e8f0;
        }
        .selected-company-hint {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 16px;
        }
        .selected-company-hint strong {
            color: var(--primary);
        }
        .selected-company-hint a {
            color: var(--secondary);
            font-weight: 600;
            text-decoration: none;
            margin-left: 8px;
        }
        .selected-company-hint a:hover {
            text-decoration: underline;
        }
        .code-search-row {
            display: flex;
            gap: 14px;
            max-width: 560px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 0.9rem;
            transition: all 0.3s;
            background: #f8fafc;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            background: white;
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.1);
        }
        .code-search-row .form-control {
            flex: 1;
        }
        .code-search-row .btn {
            flex-shrink: 0;
        }

        /* ========== RESULTATS ========== */
        .alert-box {
            padding: 16px 20px;
            border-radius: var(--radius);
            margin: 28px auto 0;
            max-width: 700px;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .alert-success {
            background: #eafaf1;
            color: var(--success);
            border: 1px solid #c8e6d0;
        }
        .alert-danger {
            background: #fdecea;
            color: var(--secondary-dark);
            border: 1px solid #f5c6cb;
        }
        .colis-card {
            max-width: 700px;
            margin: 24px auto 0;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        .colis-card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 20px 24px;
        }
        .colis-card-header h3 {
            font-size: 1.1rem;
        }
        .colis-card-body {
            padding: 8px 24px 24px;
        }
        .colis-info-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid #eee;
        }
        .colis-info-item:last-child {
            border-bottom: none;
        }
        .colis-info-item i {
            color: var(--secondary);
            width: 20px;
            text-align: center;
            margin-top: 3px;
        }
        .colis-info-item .label {
            display: block;
            font-size: 0.72rem;
            color: var(--gray);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 2px;
        }
        .colis-info-item .value {
            font-size: 0.95rem;
            color: var(--dark);
            font-weight: 500;
        }

        /* ---- Timeline de statut ---- */
        .status-timeline {
            max-width: 700px;
            margin: 32px auto 0;
            padding: 0 10px;
        }
        .timeline-track {
            position: relative;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin: 18px 26px 0;
        }
        .timeline-fill {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--secondary), var(--success));
            border-radius: 2px;
            transition: width 1.2s ease;
        }
        .timeline-steps {
            display: flex;
            justify-content: space-between;
            margin-top: -22px;
        }
        .timeline-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            flex: 1;
        }
        .timeline-dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            font-size: 1rem;
            transition: all 0.5s ease;
            z-index: 2;
        }
        .timeline-step.completed .timeline-dot {
            border-color: var(--success);
            background: var(--success);
            color: white;
        }
        .timeline-step.active .timeline-dot {
            border-color: var(--secondary);
            background: var(--secondary);
            color: white;
            animation: pulseDot 1.6s infinite;
        }
        .timeline-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-align: center;
            color: var(--gray);
            max-width: 100px;
        }
        .timeline-step.completed .timeline-label,
        .timeline-step.active .timeline-label {
            color: var(--dark);
        }
        @keyframes pulseDot {
            0% { box-shadow: 0 0 0 0 rgba(230, 126, 34, 0.5); }
            70% { box-shadow: 0 0 0 14px rgba(230, 126, 34, 0); }
            100% { box-shadow: 0 0 0 0 rgba(230, 126, 34, 0); }
        }

        .status-phrase-box {
            display: flex;
            align-items: center;
            gap: 16px;
            max-width: 700px;
            margin: 28px auto 0;
            padding: 18px 22px;
            border-radius: var(--radius-lg);
            background: #eef6ff;
            border: 1px solid #d6e9fc;
        }
        .status-phrase-box i {
            font-size: 1.5rem;
            color: var(--accent);
            flex-shrink: 0;
        }
        .status-phrase-box p {
            font-size: 0.95rem;
            color: var(--dark);
            font-weight: 500;
            line-height: 1.5;
        }
        .status-phrase-box.is-livre {
            background: #eafaf1;
            border-color: #c8e6d0;
        }
        .status-phrase-box.is-livre i {
            color: var(--success);
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .tracking-card { padding: 28px 20px; }
            .page-header h1 { font-size: 2rem; }
            .page-header { padding: 60px 0; min-height: 260px; }
            .page-header p { font-size: 0.9rem; }
            .code-search-row { flex-direction: column; }
            .timeline-label { max-width: 70px; font-size: 0.65rem; }
            .timeline-dot { width: 32px; height: 32px; font-size: 0.85rem; }
        }
    </style>
</head>
<body>

@include('site.partials.nav')

<!-- PAGE HEADER -->
<section class="page-header">
    <img src="{{ asset('assets_site/img/Suividecolis.png') }}" alt="Suivi de colis" class="page-header-bg">
    <div class="page-header-overlay"></div>
    <div class="container">
        <h1 data-aos="fade-up">Suivi de colis</h1>
        <p data-aos="fade-up" data-aos-delay="100">Suivez en temps réel l'état de votre envoi en entrant le code de suivi</p>
    </div>
</section>

<!-- FORMULAIRE DE SUIVI -->
<section class="tracking-page-section">
    <div class="container">
        <div class="tracking-card" data-aos="fade-up">
            <div class="intro">
                <i class="fas fa-box-open"></i>
                <h2>Où est mon colis ?</h2>
                <p>Choisissez la compagnie qui a pris en charge votre envoi, puis entrez le code de suivi reçu au dépôt.</p>
            </div>

            <form action="{{ route('site.suivi-colis') }}" method="GET" id="trackingForm">
                <input type="hidden" name="id_compagnie" id="idCompagnieInput" value="{{ $idCompagnieSelectionnee }}">

                <!-- ETAPE 1 : Compagnie -->
                <div class="step-1">
                    <div class="step-label"><span class="step-num">1</span> Choisissez votre compagnie</div>
                    <div class="company-picker-grid">
                        @forelse ($compagnies as $i => $compagnie)
                            <div class="company-pick-card{{ (string) $idCompagnieSelectionnee === (string) $compagnie->id_compagnie ? ' selected' : '' }}"
                                 data-id="{{ $compagnie->id_compagnie }}"
                                 data-nom="{{ $compagnie->nom_compagnie }}"
                                 data-aos="zoom-in" data-aos-delay="{{ $i * 80 }}">
                                <div class="company-pick-check"><i class="fas fa-check"></i></div>
                                <div class="company-pick-cover"></div>
                                <div class="company-pick-logo">
                                    @if ($compagnie->logo)
                                        <img src="{{ asset('images/logos/'.$compagnie->logo) }}" alt="{{ $compagnie->nom_compagnie }}">
                                    @else
                                        <i class="fas fa-bus"></i>
                                    @endif
                                </div>
                                <div class="company-pick-content">
                                    <div class="company-pick-name">{{ $compagnie->nom_compagnie }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="empty-companies">Aucune compagnie disponible pour le moment.</p>
                        @endforelse
                    </div>
                </div>

                <!-- ETAPE 2 : Code de suivi -->
                <div class="step-code{{ $idCompagnieSelectionnee ? ' is-open' : '' }}" id="stepCode">
                    <div class="step-label"><span class="step-num">2</span> Entrez le code de suivi</div>
                    <p class="selected-company-hint">
                        Compagnie choisie : <strong id="selectedCompanyName">{{ $compagnies->firstWhere('id_compagnie', $idCompagnieSelectionnee)?->nom_compagnie }}</strong>
                        <a href="#" id="changeCompanyLink"><i class="fas fa-rotate-left"></i> changer</a>
                    </p>
                    <div class="code-search-row">
                        <input type="text" name="code_colis" id="code" class="form-control"
                            placeholder="Ex : COLIS123456"
                            value="{{ $codeColisSaisi }}"
                            {{ $idCompagnieSelectionnee ? '' : 'disabled' }}>
                        <button type="submit" class="btn btn-primary" id="submitBtn" {{ $idCompagnieSelectionnee ? '' : 'disabled' }}>
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Message d'alerte -->
        @if ($colis && $colis->code_colis)
            <div class="alert-box alert-success" data-aos="fade-up">
                <i class="fas fa-check-circle"></i> Colis trouvé avec succès
            </div>
        @elseif ($erreur)
            <div class="alert-box alert-danger" data-aos="fade-up">
                <i class="fas fa-exclamation-triangle"></i> {{ $erreur }}
            </div>
        @endif

        <!-- Détails du colis -->
        @if ($colis && $colis->code_colis)
            @php
                $etapes = [
                    'enregistre' => ['label' => 'Pris en charge',      'icon' => 'fa-box',           'phrase' => "Votre colis est arrivé à la gare de départ et a été pris en charge."],
                    'en_cours'   => ['label' => "En cours d'envoi",     'icon' => 'fa-truck',         'phrase' => "Votre colis est actuellement en cours d'envoi vers sa destination."],
                    'recu'       => ['label' => 'Reçu à destination',  'icon' => 'fa-warehouse',     'phrase' => "Votre colis a été reçu par la gare de destination."],
                    'livre'      => ['label' => 'Livré',               'icon' => 'fa-check-circle',  'phrase' => "Votre colis a été livré à son destinataire."],
                ];
                $ordreStatuts = array_keys($etapes);
                $indexActuel = array_search($colis->status, $ordreStatuts, true);
                $pourcentage = $indexActuel !== false ? ($indexActuel / (count($ordreStatuts) - 1)) * 100 : 0;
            @endphp
            <div class="colis-card" data-aos="fade-up">
                <div class="colis-card-header">
                    <h3><i class="fas fa-box"></i> Détails du colis</h3>
                </div>
                <div class="colis-card-body">
                    <div class="colis-info-item">
                        <i class="fas fa-barcode"></i>
                        <div>
                            <span class="label">Code</span>
                            <span class="value">{{ $colis->code_colis }}</span>
                        </div>
                    </div>
                    <div class="colis-info-item">
                        <i class="fas fa-user"></i>
                        <div>
                            <span class="label">Expéditeur</span>
                            <span class="value">{{ $colis->expediteur }} ({{ $colis->numero_exp }})</span>
                        </div>
                    </div>
                    <div class="colis-info-item">
                        <i class="fas fa-user-check"></i>
                        <div>
                            <span class="label">Destinataire</span>
                            <span class="value">{{ $colis->destinataire }} ({{ $colis->numero_dest }})</span>
                        </div>
                    </div>
                    <div class="colis-info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <span class="label">Provenance</span>
                            <span class="value">{{ $colis->provient_de }}</span>
                        </div>
                    </div>
                    <div class="colis-info-item">
                        <i class="fas fa-flag-checkered"></i>
                        <div>
                            <span class="label">Destination</span>
                            <span class="value">{{ $colis->destination }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline animée du statut -->
            <div class="status-timeline" data-aos="fade-up">
                <div class="timeline-track">
                    <div class="timeline-fill" style="width: {{ $pourcentage }}%"></div>
                </div>
                <div class="timeline-steps">
                    @foreach ($ordreStatuts as $i => $cle)
                        @php
                            $etat = '';
                            if ($indexActuel !== false) {
                                if ($i < $indexActuel) $etat = 'completed';
                                elseif ($i === $indexActuel) $etat = 'active';
                            }
                            $icone = $etat === 'completed' ? 'fa-check' : $etapes[$cle]['icon'];
                        @endphp
                        <div class="timeline-step {{ $etat }}">
                            <div class="timeline-dot"><i class="fas {{ $icone }}"></i></div>
                            <span class="timeline-label">{{ $etapes[$cle]['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Phrase explicative du statut -->
            <div class="status-phrase-box{{ $colis->status === 'livre' ? ' is-livre' : '' }}" data-aos="fade-up">
                <i class="fas {{ $indexActuel !== false ? 'fa-info-circle' : 'fa-question-circle' }}"></i>
                <p>{{ $indexActuel !== false ? $etapes[$colis->status]['phrase'] : $colis->status }}</p>
            </div>
        @endif
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
                <a href="{{ route('site.suivi-colis') }}">Suivis de colis</a>
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

    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.company-pick-card');
        const idInput = document.getElementById('idCompagnieInput');
        const stepCode = document.getElementById('stepCode');
        const nameSpan = document.getElementById('selectedCompanyName');
        const codeInput = document.getElementById('code');
        const submitBtn = document.getElementById('submitBtn');
        const changeLink = document.getElementById('changeCompanyLink');

        cards.forEach(function(card) {
            card.addEventListener('click', function() {
                cards.forEach(function(c) { c.classList.remove('selected'); });
                card.classList.add('selected');
                idInput.value = card.dataset.id;
                nameSpan.textContent = card.dataset.nom;
                stepCode.classList.add('is-open');
                codeInput.removeAttribute('disabled');
                submitBtn.removeAttribute('disabled');
                setTimeout(function() {
                    stepCode.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    codeInput.focus();
                }, 200);
            });
        });

        if (changeLink) {
            changeLink.addEventListener('click', function(e) {
                e.preventDefault();
                cards.forEach(function(c) { c.classList.remove('selected'); });
                idInput.value = '';
                codeInput.value = '';
                stepCode.classList.remove('is-open');
                codeInput.setAttribute('disabled', true);
                submitBtn.setAttribute('disabled', true);
            });
        }
    });
</script>
</body>
</html>
