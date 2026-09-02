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
        .tracking-card > .intro img {
            width: 150px;
            height: auto;
            margin: 0 auto 16px;
            display: block;
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
            .code-search-row { flex-direction: column; }
            .timeline-label { max-width: 70px; font-size: 0.65rem; }
            .timeline-dot { width: 32px; height: 32px; font-size: 0.85rem; }
        }
    </style>
</head>
<body>

@include('site.partials.nav', ['compagnie' => \App\Models\Compagnie::site()])

<!-- PAGE HEADER -->
<section class="page-header">
    <span class="deco-blob blob-1"></span>
    <span class="deco-blob blob-2"></span>
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
                <img src="{{ asset('assets_site/img/Suividecolis.png') }}" alt="Suivi de colis">
                <h2>Où est mon colis ?</h2>
                <p>Entrez le code de suivi reçu au dépôt de votre colis.</p>
            </div>

            <form action="{{ route('site.suivi-colis') }}" method="GET" id="trackingForm">
                <div class="code-search-row">
                    <input type="text" name="code_colis" id="code" class="form-control"
                        placeholder="Ex : COLIS123456"
                        value="{{ $codeColisSaisi }}">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
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

@include('site.partials.footer')

<script src="{{ asset('assets_site/js/aos.js') }}"></script>
<script>
    AOS.init({ duration: 600, once: true, offset: 50 });
</script>
</body>
</html>
