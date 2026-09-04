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
    <title>Billet {{ $billet->numeroBillets }} - Sirali</title>
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
        .ticket-section { padding: 50px 0; }
        .ticket-card {
            max-width: 560px; margin: 0 auto; background: white;
            border-radius: var(--radius-xl); box-shadow: var(--shadow-lg); overflow: hidden;
        }
        .ticket-head {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white; padding: 32px; text-align: center;
        }
        .ticket-head .status-icon { font-size: 3rem; margin-bottom: 10px; }
        .ticket-head h1 { font-size: 1.5rem; margin-bottom: 6px; }
        .ticket-head p { opacity: .85; font-size: .9rem; margin-bottom: 0; }
        .ticket-body { padding: 32px; }
        .ticket-number {
            text-align: center; font-size: 1.6rem; font-weight: 800; letter-spacing: 1px;
            color: var(--primary); border: 2px dashed #e2e8f0; border-radius: var(--radius-lg);
            padding: 14px; margin-bottom: 24px;
        }
        .badge-row { display: flex; justify-content: center; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
        .badge {
            display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px;
            border-radius: 50px; font-size: .78rem; font-weight: 700;
        }
        .badge-warning { background: #fff7ed; color: #c2410c; }
        .badge-success { background: #f0fdf4; color: #15803d; }
        .badge-info { background: #e8f4fd; color: var(--primary); }
        table.ticket-infos { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.ticket-infos td { padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: .92rem; }
        table.ticket-infos td.label { color: var(--gray); }
        table.ticket-infos td.value { text-align: right; font-weight: 700; color: var(--dark); }
        .ticket-total {
            background: linear-gradient(135deg, #fef3e8, #fff5eb); border-radius: var(--radius-lg);
            padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
        }
        .ticket-total .label { font-weight: 600; color: var(--dark); }
        .ticket-total .value { font-size: 1.5rem; font-weight: 800; color: var(--secondary); }
        .payment-reminder {
            background: #fff7ed; border-left: 4px solid #f59e0b; border-radius: var(--radius);
            padding: 14px 16px; font-size: .85rem; color: #92400e; margin-bottom: 20px; display: flex; gap: 10px;
        }
        .ticket-actions { display: flex; gap: 12px; }
        .ticket-actions .btn { flex: 1; }
        @media print {
            .header, #mobileNav, #overlay, .footer, .ticket-actions, .no-print { display: none !important; }
            body { background: white; }
            .ticket-card { box-shadow: none; }
        }
    </style>
</head>
<body>

@include('site.partials.nav')

<section class="ticket-section">
    <div class="container">
        @php
            $enAttente = $billet->validation_billets === 'en_attente';
            $expire = $billet->delait_reservation && now()->greaterThan($billet->delait_reservation);
        @endphp
        <div class="ticket-card" data-aos="fade-up">
            <div class="ticket-head">
                <div class="status-icon"><i class="fas fa-ticket-alt"></i></div>
                <h1>{{ $compagnie->nom_compagnie ?? 'Billetterie' }}</h1>
                @if ($compagnie && $compagnie->slogant)
                    <p>{{ $compagnie->slogant }}</p>
                @endif
            </div>

            <div class="ticket-body">
                <div class="ticket-number">{{ $billet->numeroBillets }}</div>

                <div class="badge-row">
                    @if ($expire)
                        <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Délai de paiement expiré</span>
                    @elseif ($enAttente)
                        <span class="badge badge-warning"><i class="fas fa-clock"></i> En attente de validation</span>
                    @else
                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Validé</span>
                    @endif
                    <span class="badge badge-info"><i class="fas fa-globe"></i> Réservation en ligne</span>
                </div>

                <table class="ticket-infos">
                    <tr>
                        <td class="label">Client</td>
                        <td class="value">{{ $billet->client->Client ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Trajet</td>
                        <td class="value">{{ $billet->departId }} <i class="fas fa-long-arrow-alt-right"></i> {{ $billet->destinationId }}</td>
                    </tr>
                    <tr>
                        <td class="label">Date</td>
                        <td class="value">{{ $billet->jourVoyage?->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Heure</td>
                        <td class="value">{{ $billet->Heur_departs }}</td>
                    </tr>
                    <tr>
                        <td class="label">Passager(s)</td>
                        <td class="value">{{ $billet->nombrePassages }}</td>
                    </tr>
                    <tr>
                        <td class="label">Place(s)</td>
                        <td class="value">{{ $billet->numeroPlace }}</td>
                    </tr>
                    @if ($billet->num_gare)
                        <tr>
                            <td class="label">Gare de départ</td>
                            <td class="value">{{ $billet->num_gare }}</td>
                        </tr>
                    @endif
                </table>

                <div class="ticket-total">
                    <span class="label"><i class="fas fa-money-bill-wave"></i> Montant à payer</span>
                    <span class="value">{{ number_format((float) ($billet->client->montant_payer ?? 0), 0, ',', ' ') }} FCFA</span>
                </div>

                @if ($enAttente && ! $expire)
                    <div class="payment-reminder">
                        <i class="fas fa-info-circle"></i>
                        <span>Payez via Orange Money au numéro <strong>{{ $billet->client->numeroPaiement ?? '-' }}</strong> dans les 30 minutes suivant la réservation, sinon elle sera automatiquement annulée.</span>
                    </div>
                @endif

                <div class="ticket-actions no-print">
                    <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimer</button>
                    <a href="{{ route('site.recherche') }}" class="btn btn-outline">Nouvelle recherche</a>
                </div>
            </div>
        </div>
    </div>
</section>

@include('site.partials.footer')

<script src="{{ asset('assets_site/js/aos.js') }}"></script>
<script>AOS.init({ duration: 600, once: true, offset: 50 });</script>
</body>
</html>
