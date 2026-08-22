@extends('layouts.admin')

@php
    $authUser = auth('staff')->user()->load('agence');
    $droit = $authUser->droit;
    $profile = $authUser->profile;
@endphp

@section('title', 'Accueil · TransHub Admin')

@section('hero')
    <div class="tg-hero mb-4">
        <div class="tg-hero__content d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="tg-hero__icon">
                    @if ($droit === 'super_admin')
                        <i class="bi bi-diagram-3-fill"></i>
                    @elseif ($droit === 'Admin')
                        <i class="bi bi-building"></i>
                    @elseif ($droit === 'chef_d_escale')
                        <i class="bi bi-geo-alt-fill"></i>
                    @elseif ($profile === 'billet')
                        <i class="bi bi-ticket-perforated-fill"></i>
                    @elseif ($profile === 'colis')
                        <i class="bi bi-box-seam-fill"></i>
                    @else
                        <i class="bi bi-person-fill"></i>
                    @endif
                </div>
                <div>
                    <h4 class="tg-hero__title mb-0">
                        @if ($droit === 'super_admin')
                            Tableau de bord plateforme
                        @elseif ($droit === 'Admin')
                            Tableau de bord administrateur
                            @if (! empty($gareLabel)) — {{ $gareLabel }} @endif
                        @elseif ($droit === 'chef_d_escale')
                            Gestion de la gare – {{ $authUser->agence?->localite }}
                        @elseif ($profile === 'billet')
                            Espace billetterie
                        @elseif ($profile === 'colis')
                            Espace colis & courrier
                        @else
                            Espace personnel
                        @endif
                    </h4>
                    <p class="tg-hero__subtitle">
                        @if ($droit === 'super_admin')
                            Vue globale de l'ensemble des compagnies
                        @elseif ($droit === 'Admin')
                            {{ ! empty($gareLabel) ? "Activité de la gare de $gareLabel" : "Vue globale de l'ensemble des gares et activités" }}
                        @elseif ($droit === 'chef_d_escale')
                            Suivez en temps réel les opérations de votre gare
                        @else
                            Consultez vos performances et réservations
                        @endif
                    </p>
                </div>
            </div>
            <div class="tg-hero__date-chip">
                <div class="tg-date-label">Aujourd'hui</div>
                <div class="tg-date-value" id="currentDate"></div>
            </div>
        </div>
    </div>
@endsection

@section('content')

@if ($mode === 'plateforme')

    <!-- VUE PLATEFORME (super_admin) -->
    <div class="tg-stat-grid mb-4">
        <a href="{{ url('/admin/Compagnies') }}" class="tg-stat-card" style="--tg-stat-color: var(--accent);">
            <div class="tg-stat-card__top">
                <div>
                    <div class="tg-stat-card__value">{{ $platformStats['totalCompagnies'] }}</div>
                    <div class="tg-stat-card__label">Compagnies</div>
                </div>
                <div class="tg-stat-card__icon"><i class="bi bi-building"></i></div>
            </div>
        </a>
        <a href="{{ url('/admin/Compagnies') }}" class="tg-stat-card" style="--tg-stat-color: var(--info);">
            <div class="tg-stat-card__top">
                <div>
                    <div class="tg-stat-card__value">{{ $platformStats['totalGares'] }}</div>
                    <div class="tg-stat-card__label">Gares</div>
                </div>
                <div class="tg-stat-card__icon"><i class="bi bi-geo-alt-fill"></i></div>
            </div>
        </a>
        <a href="{{ url('/admin/Compagnies') }}" class="tg-stat-card" style="--tg-stat-color: var(--success);">
            <div class="tg-stat-card__top">
                <div>
                    <div class="tg-stat-card__value">{{ $platformStats['totalUtilisateurs'] }}</div>
                    <div class="tg-stat-card__label">Utilisateurs actifs</div>
                </div>
                <div class="tg-stat-card__icon"><i class="bi bi-people-fill"></i></div>
            </div>
        </a>
        <a href="{{ url('/admin/Compagnies') }}" class="tg-stat-card" style="--tg-stat-color: var(--warning);">
            <div class="tg-stat-card__top">
                <div>
                    <div class="tg-stat-card__value">{{ $platformStats['totalBilletsJour'] }}</div>
                    <div class="tg-stat-card__label">Billets vendus aujourd'hui</div>
                </div>
                <div class="tg-stat-card__icon"><i class="bi bi-ticket-perforated"></i></div>
            </div>
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="tg-panel">
                <div class="d-flex align-items-center mb-3">
                    <span class="tg-panel__icon" style="background: rgba(59,130,246,0.12); color: var(--accent);"><i class="bi bi-building"></i></span>
                    <div>
                        <h5 class="tg-panel__title">Activité par compagnie</h5>
                        <p class="tg-panel__subtitle">Vue consolidée toutes compagnies confondues</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Compagnie</th>
                                <th>Gares</th>
                                <th>Utilisateurs actifs</th>
                                <th>Billets aujourd'hui</th>
                                <th>Colis ce mois</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($compagniesOverview as $compagnie)
                                <tr>
                                    <td class="fw-semibold">{{ $compagnie['nom_compagnie'] }}</td>
                                    <td>{{ (int) $compagnie['nb_gares'] }}</td>
                                    <td>{{ (int) $compagnie['nb_utilisateurs'] }}</td>
                                    <td>{{ (int) $compagnie['billets_jour'] }}</td>
                                    <td>{{ (int) $compagnie['colis_mois'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Aucune compagnie enregistrée</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@else

    @php
        $dateLabel = $date === now()->toDateString() ? "Aujourd'hui" : \Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
    @endphp

    <!-- FILTRE PAR GARE (Admin) ET PAR DATE (tous les roles) -->
    <div class="tg-filter-bar mb-4">
        <label for="dateFiltre" class="fw-semibold small text-muted mb-0">
            <i class="bi bi-funnel-fill me-1"></i> Chiffres du {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}
        </label>
        <form method="get" class="d-flex flex-wrap align-items-center gap-2 mb-0">
            @if ($droit === 'Admin' && $listeGares->isNotEmpty())
                <select name="gare" id="gareSelectFiltre" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Toutes les gares (vue globale)</option>
                    @foreach ($listeGares as $gare)
                        <option value="{{ $gare->idAgence }}" {{ (string) $gareId === (string) $gare->idAgence ? 'selected' : '' }}>
                            {{ $gare->localite }} ({{ $gare->numeroGare }})
                        </option>
                    @endforeach
                </select>
            @endif
            <input type="date" name="date" id="dateFiltre" class="form-control form-control-sm"
                value="{{ $date }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()">
        </form>
        @if (! empty($gareId) || $date !== now()->toDateString())
            <a href="{{ url()->current() }}" class="tg-reset-pill"><i class="bi bi-x-circle me-1"></i>Réinitialiser</a>
        @endif
    </div>

    <!-- ACTIONS RAPIDES -->
    @if ($showBillets || $showColis)
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
            <span class="tg-section-label mb-0 me-2"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Actions rapides</span>
            @if ($showBillets)
                <a href="{{ url('/admin/Add_billets') }}" class="tg-quick-action"><i class="bi bi-plus-circle"></i> Nouvelle vente</a>
            @endif
            @if ($showColis)
                <a href="{{ url('/admin/Mouvement_colis') }}" class="tg-quick-action"><i class="bi bi-truck"></i> Suivi colis</a>
            @endif
        </div>
    @endif

    <!-- KPI BILLETS -->
    @if ($showBillets)
        <div class="tg-section-label"><i class="bi bi-ticket-perforated me-1"></i>Billetterie — {{ $dateLabel }}</div>
        <div class="tg-stat-grid mb-4">
            <a href="{{ url('/admin/Liste_du_jours') }}" class="tg-stat-card" style="--tg-stat-color: var(--accent);">
                <div class="tg-stat-card__top">
                    <div>
                        <div class="tg-stat-card__value">{{ $billetsJour['presentiel'] }}</div>
                        <div class="tg-stat-card__label">Billets en présentiel</div>
                    </div>
                    <div class="tg-stat-card__icon"><i class="bi bi-person-badge"></i></div>
                </div>
            </a>
            <a href="{{ url('/admin/Liste_du_jours') }}" class="tg-stat-card" style="--tg-stat-color: var(--success);">
                <div class="tg-stat-card__top">
                    <div>
                        <div class="tg-stat-card__value">{{ $billetsJour['en_ligne'] }}</div>
                        <div class="tg-stat-card__label">Billets en ligne validés</div>
                    </div>
                    <div class="tg-stat-card__icon"><i class="bi bi-laptop"></i></div>
                </div>
            </a>
            <a href="{{ url('/admin/Liste_ententes') }}" class="tg-stat-card" style="--tg-stat-color: var(--warning);">
                <div class="tg-stat-card__top">
                    <div>
                        <div class="tg-stat-card__value">{{ $billetsJour['en_attente'] }}</div>
                        <div class="tg-stat-card__label">En attente de validation</div>
                    </div>
                    <div class="tg-stat-card__icon"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </a>
            @if ($showVoyages)
                <a href="{{ url('/admin/Programmation_voyages/liste_programmer_voyage') }}" class="tg-stat-card" style="--tg-stat-color: var(--info);">
                    <div class="tg-stat-card__top">
                        <div>
                            <div class="tg-stat-card__value">{{ $voyagesJour }}</div>
                            <div class="tg-stat-card__label">Voyages programmés</div>
                        </div>
                        <div class="tg-stat-card__icon"><i class="bi bi-bus-front"></i></div>
                    </div>
                </a>
            @endif
        </div>
    @elseif ($showVoyages)
        <div class="tg-section-label"><i class="bi bi-bus-front me-1"></i>Voyages — {{ $dateLabel }}</div>
        <div class="tg-stat-grid mb-4">
            <a href="{{ url('/admin/Programmation_voyages/liste_programmer_voyage') }}" class="tg-stat-card" style="--tg-stat-color: var(--info);">
                <div class="tg-stat-card__top">
                    <div>
                        <div class="tg-stat-card__value">{{ $voyagesJour }}</div>
                        <div class="tg-stat-card__label">Voyages programmés</div>
                    </div>
                    <div class="tg-stat-card__icon"><i class="bi bi-bus-front"></i></div>
                </div>
            </a>
        </div>
    @endif

    <!-- KPI COLIS -->
    @if ($showColis)
        <div class="tg-section-label"><i class="bi bi-box-seam me-1"></i>Colis & courrier — {{ $dateLabel }}</div>
        <div class="tg-stat-grid mb-4">
            <a href="{{ url('/admin/Colis_prise_en_charges') }}" class="tg-stat-card" style="--tg-stat-color: var(--accent);">
                <div class="tg-stat-card__top">
                    <div>
                        <div class="tg-stat-card__value">{{ $colisJour['prise_en_charge'] }}</div>
                        <div class="tg-stat-card__label">Colis pris en charge</div>
                    </div>
                    <div class="tg-stat-card__icon"><i class="bi bi-box-seam"></i></div>
                </div>
            </a>
            <a href="{{ url('/admin/Mouvement_colis') }}" class="tg-stat-card" style="--tg-stat-color: var(--warning);">
                <div class="tg-stat-card__top">
                    <div>
                        <div class="tg-stat-card__value">{{ $colisJour['en_cours'] }}</div>
                        <div class="tg-stat-card__label">Colis en cours</div>
                    </div>
                    <div class="tg-stat-card__icon"><i class="bi bi-truck"></i></div>
                </div>
            </a>
            <a href="{{ url('/admin/Mouvement_colis') }}" class="tg-stat-card" style="--tg-stat-color: var(--success);">
                <div class="tg-stat-card__top">
                    <div>
                        <div class="tg-stat-card__value">{{ $colisJour['recu'] ?? 0 }}</div>
                        <div class="tg-stat-card__label">Colis reçus</div>
                    </div>
                    <div class="tg-stat-card__icon"><i class="bi bi-inbox"></i></div>
                </div>
            </a>
            <a href="{{ url('/admin/Livraison_colis') }}" class="tg-stat-card" style="--tg-stat-color: var(--info);">
                <div class="tg-stat-card__top">
                    <div>
                        <div class="tg-stat-card__value">{{ $colisJour['livre'] }}</div>
                        <div class="tg-stat-card__label">Colis livrés</div>
                    </div>
                    <div class="tg-stat-card__icon"><i class="bi bi-check2-circle"></i></div>
                </div>
            </a>
        </div>
    @endif

    <!-- CARS VERS MA GARE (chef d'escale uniquement) : en transit + programmés non partis -->
    @if ($droit === 'chef_d_escale')
        <div class="tg-section-label"><i class="bi bi-bus-front me-1"></i>Cars vers votre gare</div>
        <div class="row mb-4">
            <div class="col-12">
                <div class="tg-panel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center">
                            <span class="tg-panel__icon" style="background: rgba(59,130,246,0.12); color: var(--accent);"><i class="bi bi-bus-front"></i></span>
                            <div>
                                <h5 class="tg-panel__title">Cars en approche</h5>
                                <p class="tg-panel__subtitle">En transit ou programmés vers {{ $authUser->agence?->localite }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.programmation-voyage.dashboard') }}" class="btn btn-sm btn-outline-primary">
                            Gérer <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    @if ($carsEnTransit->isEmpty() && $carsProgrammes->isEmpty())
                        <div class="tg-empty">
                            <i class="bi bi-bus-front"></i>
                            Aucun car en approche pour le moment.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Numéro Car</th>
                                        <th>Places</th>
                                        <th>Provenance</th>
                                        <th>Heure prévue</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($carsEnTransit as $car)
                                        <tr>
                                            <td class="fw-semibold">{{ $car->numero_car }}</td>
                                            <td>{{ $car->nbr_place }}</td>
                                            <td>{{ $car->provenance ?? '—' }}</td>
                                            <td>{{ $car->id_horaire ?? '—' }}</td>
                                            <td><span class="badge bg-success">En transit</span></td>
                                        </tr>
                                    @endforeach
                                    @foreach ($carsProgrammes as $car)
                                        <tr>
                                            <td class="fw-semibold">{{ $car->numero_car }}</td>
                                            <td>{{ $car->nbr_place }}</td>
                                            <td>{{ $car->localite_user }}</td>
                                            <td>{{ $car->id_horaire }}</td>
                                            <td><span class="badge bg-warning text-dark">Programmé</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- FINANCES : aperçu bénéfice (Admin/PDG) / état de la caisse (chef d'escale) -->
    @if ((in_array($droit, ['Admin', 'PDG'], true) && ! empty($beneficeJour)) || $droit === 'chef_d_escale')
        <div class="tg-section-label"><i class="bi bi-cash-coin me-1"></i>Finances</div>
        <div class="tg-stat-grid mb-4">
            @if (in_array($droit, ['Admin', 'PDG'], true) && ! empty($beneficeJour))
                <a href="{{ url('/admin/Depenses/benefice') }}" class="tg-stat-card" style="--tg-stat-color: {{ $beneficeJour['benefice'] >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                    <div class="tg-stat-card__top">
                        <div>
                            <div class="tg-stat-card__value">{{ number_format($beneficeJour['benefice'], 0, ',', ' ') }} F</div>
                            <div class="tg-stat-card__label">Bénéfice — {{ $dateLabel }}</div>
                        </div>
                        <div class="tg-stat-card__icon"><i class="bi bi-graph-up-arrow"></i></div>
                    </div>
                    <div class="tg-stat-card__meta"><i class="bi bi-arrow-right-circle"></i> Voir le détail</div>
                </a>
            @endif

            @if (in_array($droit, ['Admin', 'chef_d_escale', 'PDG'], true) && ! empty($beneficeJour))
                <div class="tg-stat-card" style="--tg-stat-color: var(--primary);">
                    <div class="tg-stat-card__top">
                        <div>
                            <div class="tg-stat-card__value">{{ number_format($beneficeJour['revenus_billets'], 0, ',', ' ') }} F</div>
                            <div class="tg-stat-card__label">Revenus billets</div>
                        </div>
                        <div class="tg-stat-card__icon"><i class="bi bi-ticket-perforated"></i></div>
                    </div>
                </div>

                <div class="tg-stat-card" style="--tg-stat-color: var(--info);">
                    <div class="tg-stat-card__top">
                        <div>
                            <div class="tg-stat-card__value">{{ number_format($beneficeJour['revenus_colis'], 0, ',', ' ') }} F</div>
                            <div class="tg-stat-card__label">Revenus colis</div>
                        </div>
                        <div class="tg-stat-card__icon"><i class="bi bi-box-seam"></i></div>
                    </div>
                </div>
            @endif

            @if ($droit === 'chef_d_escale')
                <a href="{{ url('/admin/Caisse') }}" class="tg-stat-card" style="--tg-stat-color: {{ $caisseGare ? 'var(--success)' : 'var(--danger)' }};">
                    <div class="tg-stat-card__top">
                        <div>
                            <div class="tg-stat-card__value">
                                {{ $caisseGare ? number_format($caisseGare->solde, 0, ',', ' ') . ' F' : 'Fermée' }}
                            </div>
                            <div class="tg-stat-card__label">État de ma caisse</div>
                        </div>
                        <div class="tg-stat-card__icon"><i class="bi bi-wallet2"></i></div>
                    </div>
                    <div class="tg-stat-card__meta">
                        <i class="bi bi-arrow-right-circle"></i>
                        {{ $caisseGare ? 'Voir / clôturer' : 'Ouvrir une caisse' }}
                    </div>
                </a>
            @endif
        </div>
    @endif

    <div class="row g-4 mb-4">

        <!-- TOP GARES (Admin, vue globale uniquement) -->
        @if ($showTopGares && ! empty($topGares))
            @php
                $maxBillets = max(array_column($topGares, 'total_billets'));
            @endphp
            <div class="col-lg-7">
                <div class="tg-panel h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center">
                            <span class="tg-panel__icon" style="background: rgba(245,158,11,0.14); color: var(--tg-orange);"><i class="bi bi-trophy-fill"></i></span>
                            <div>
                                <h5 class="tg-panel__title">Top des gares</h5>
                                <p class="tg-panel__subtitle">Classement par billets vendus</p>
                            </div>
                        </div>
                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill"><i class="bi bi-calendar-month"></i> {{ now()->translatedFormat('F Y') }}</span>
                    </div>
                    @foreach ($topGares as $rang => $gare)
                        @php
                            $percent = $maxBillets > 0 ? ($gare['total_billets'] / $maxBillets) * 100 : 0;
                            $rangAffiche = $rang + 1;
                            $rankClass = $rangAffiche <= 3 ? ' tg-rank--'.$rangAffiche : '';
                        @endphp
                        <div class="tg-leaderboard-item">
                            <div class="tg-rank{{ $rankClass }}">{{ $rangAffiche }}</div>
                            <div class="tg-leaderboard-body">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-semibold"><i class="bi bi-geo-alt-fill text-muted me-1"></i>{{ $gare['gare'] }}</span>
                                    <span class="badge bg-primary rounded-pill">{{ number_format($gare['total_billets']) }} billets</span>
                                </div>
                                <div class="progress-custom">
                                    <div class="progress-bar-custom" style="width: {{ $percent }}%;"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- RÉPARTITION COLIS (données réelles, donut CSS) -->
        @if ($showColis)
            @php
                $segments = [
                    ['label' => 'Pris en charge', 'valeur' => (int) $colisJour['prise_en_charge'], 'color' => '#3b82f6'],
                    ['label' => 'En cours', 'valeur' => (int) $colisJour['en_cours'], 'color' => '#f59e0b'],
                    ['label' => 'Reçus', 'valeur' => (int) $colisJour['recu'], 'color' => '#06b6d4'],
                    ['label' => 'Livrés', 'valeur' => (int) $colisJour['livre'], 'color' => '#10b981'],
                    ['label' => 'En attente', 'valeur' => (int) $colisJour['attente'], 'color' => '#ef4444'],
                ];
                $totalColis = array_sum(array_column($segments, 'valeur'));
                $stops = [];
                $cursor = 0;
                foreach ($segments as $segment) {
                    if ($totalColis === 0 || $segment['valeur'] === 0) {
                        continue;
                    }
                    $start = $cursor;
                    $cursor += ($segment['valeur'] / $totalColis) * 360;
                    $stops[] = "{$segment['color']} {$start}deg {$cursor}deg";
                }
                $conicGradient = $totalColis > 0 ? 'conic-gradient('.implode(', ', $stops).')' : 'conic-gradient(var(--tg-gray-200) 0deg 360deg)';
            @endphp
            <div class="col-lg-5">
                <div class="tg-panel h-100">
                    <div class="d-flex align-items-center mb-3">
                        <span class="tg-panel__icon" style="background: rgba(16,185,129,0.14); color: var(--tg-success);"><i class="bi bi-pie-chart-fill"></i></span>
                        <div>
                            <h5 class="tg-panel__title">Répartition colis</h5>
                            <p class="tg-panel__subtitle">{{ $dateLabel }}</p>
                        </div>
                    </div>
                    @if ($totalColis === 0)
                        <div class="tg-empty">
                            <i class="bi bi-inbox"></i>
                            Aucun colis enregistré pour cette date.
                        </div>
                    @else
                        <div class="tg-donut-wrap">
                            <div class="tg-donut" style="background: {{ $conicGradient }};">
                                <div class="tg-donut__hole">
                                    <strong>{{ (int) $totalColis }}</strong>
                                    <span>colis</span>
                                </div>
                            </div>
                            <div class="tg-donut-legend">
                                @foreach ($segments as $segment)
                                    @continue($segment['valeur'] === 0)
                                    @php $percent = round(($segment['valeur'] / $totalColis) * 100); @endphp
                                    <div class="tg-donut-legend__item">
                                        <span class="tg-donut-legend__label">
                                            <span class="tg-donut-legend__dot" style="background: {{ $segment['color'] }};"></span>
                                            {{ $segment['label'] }}
                                        </span>
                                        <span class="tg-donut-legend__value">{{ $percent }}% <span class="text-muted fw-normal">({{ $segment['valeur'] }})</span></span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>

    <!-- ACTIVITÉS RÉCENTES (données réelles, timeline) -->
    <div class="row">
        <div class="col-12">
            <div class="tg-panel">
                <div class="d-flex align-items-center mb-3">
                    <span class="tg-panel__icon" style="background: rgba(15,23,42,0.06); color: var(--tg-navy);"><i class="bi bi-clock-history"></i></span>
                    <div>
                        <h5 class="tg-panel__title">Activités récentes</h5>
                        <p class="tg-panel__subtitle">Dernières actions dans votre périmètre</p>
                    </div>
                </div>
                @if (empty($activiteRecente))
                    <div class="tg-empty">
                        <i class="bi bi-hourglass"></i>
                        Aucune activité récente.
                    </div>
                @else
                    <div class="tg-timeline">
                        @foreach ($activiteRecente as $activite)
                            @php $estBillet = $activite['type'] === 'billet'; @endphp
                            <div class="tg-timeline-item">
                                <div class="tg-timeline-dot" style="background: {{ $estBillet ? 'var(--tg-accent, #3b82f6)' : 'var(--tg-success, #10b981)' }};">
                                    <i class="bi {{ $estBillet ? 'bi-ticket-perforated' : 'bi-truck' }}"></i>
                                </div>
                                <div class="tg-timeline-card">
                                    <div class="d-flex justify-content-between flex-wrap gap-2">
                                        <span class="fw-semibold">{{ $activite['titre'] }}</span>
                                        <small class="text-muted">{{ $activite['date'] }}</small>
                                    </div>
                                    <p class="text-muted small mb-0">{{ $activite['detail'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

@endif

@endsection

@section('scripts')
    <script>
        function updateDate() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('currentDate').innerHTML = now.toLocaleDateString('fr-FR', options);
        }
        updateDate();
    </script>
@endsection
