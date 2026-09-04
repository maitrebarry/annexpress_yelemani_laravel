@extends('layouts.admin')

@php
    $authUser = auth('staff')->user();
    $aujourdhui = now()->toDateString();
    $moisCourant = now()->format('Y-m');
    $totalMoisValide = $listeDepenses->filter(fn ($d) => $d->statut === 'valide' && \Illuminate\Support\Carbon::parse($d->date_depense)->format('Y-m') === $moisCourant)->sum('montant');
    $enAttente = $listeDepenses->where('statut', 'en_attente');
    $totalEnAttente = $enAttente->sum('montant');
@endphp

@section('title', 'Dépenses · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-money-bill-wave me-1"></i> Finances</span>
@endsection
@section('breadcrumb-active', 'Dépenses')

@section('breadcrumb-actions')
    <div class="d-flex gap-2">
        @if (in_array($authUser->droit, ['Admin', 'PDG', 'secretaire'], true))
            <a href="{{ route('admin.depense.benefice') }}" class="btn btn-sm btn-outline-success rounded-pill shadow-sm">
                <i class="fas fa-chart-line me-1"></i> Bénéfice de la compagnie
            </a>
        @endif
        @if ($authUser->droit !== 'PDG')
            <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNouvelleDepense">
                <i class="fas fa-plus me-1"></i> Enregistrer une dépense
            </button>
        @endif
    </div>
@endsection

@section('content')


    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                <div class="card-body">
                    <div class="text-muted small">Dépenses validées ce mois</div>
                    <div class="fs-5 fw-bold text-danger">-{{ number_format($totalMoisValide, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="text-muted small">En attente de validation</div>
                    <div class="fs-5 fw-bold">{{ $enAttente->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="text-muted small">Montant en attente</div>
                    <div class="fs-5 fw-bold">{{ number_format($totalEnAttente, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card bg-primary text-white border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small opacity-75">Total dépenses enregistrées</div>
                    <div class="fs-5 fw-bold">{{ $listeDepenses->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-list-ul me-1"></i> Historique des dépenses
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Catégorie</th>
                        <th>Libellé</th>
                        <th>Gare</th>
                        <th class="text-end">Montant</th>
                        <th>Enregistré par</th>
                        <th>Statut</th>
                        @if ($authUser->droit === 'Admin')
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($listeDepenses as $d)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($d->date_depense)->format('d/m/Y') }}</td>
                            <td><span class="badge bg-secondary">{{ $d->categorie }}</span></td>
                            <td>{{ $d->libelle ?? '-' }}</td>
                            <td>
                                @if ($d->localite)
                                    {{ $d->localite }} ({{ $d->numeroGare }})
                                @else
                                    <span class="badge bg-dark">Globale (compagnie)</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-danger">-{{ number_format($d->montant, 0, ',', ' ') }} F</td>
                            <td>{{ $d->agent ?? '-' }}</td>
                            <td>
                                @if ($d->statut === 'valide')
                                    <span class="badge bg-success">Validée</span>
                                @elseif ($d->statut === 'en_attente')
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @else
                                    <span class="badge bg-danger">Rejetée</span>
                                @endif
                            </td>
                            @if ($authUser->droit === 'Admin')
                                <td>
                                    @if ($d->statut === 'en_attente')
                                        <div class="d-flex gap-2">
                                            <form method="post" action="{{ route('admin.depense.valider', $d->id_depense) }}" class="depense-action-form">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-success py-0 px-2 depense-valider-btn"
                                                    data-libelle="{{ $d->libelle ?: $d->categorie }}" data-montant="{{ number_format($d->montant, 0, ',', ' ') }}">
                                                    <i class="fas fa-check"></i> Valider
                                                </button>
                                            </form>
                                            <form method="post" action="{{ route('admin.depense.rejeter', $d->id_depense) }}" class="depense-action-form">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-danger py-0 px-2 depense-rejeter-btn"
                                                    data-libelle="{{ $d->libelle ?: $d->categorie }}" data-montant="{{ number_format($d->montant, 0, ',', ' ') }}">
                                                    <i class="fas fa-xmark"></i> Rejeter
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $authUser->droit === 'Admin' ? 8 : 7 }}" class="text-center text-muted py-4">Aucune dépense enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('modals')
    @if ($authUser->droit !== 'PDG')
        <div class="modal fade" id="modalNouvelleDepense" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="{{ route('admin.depense.store') }}">
                        @csrf
                        <div class="modal-header bg-success">
                            <h5 class="modal-title text-white"><i class="fas fa-circle-plus me-1"></i> Enregistrer une dépense</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                @if ($authUser->droit === 'Admin')
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Portée</label>
                                        <select class="form-select" id="depensePortee" name="portee">
                                            <option value="locale">Locale (une gare précise)</option>
                                            <option value="globale">Globale (compagnie)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6" id="depenseChampGare">
                                        <label class="form-label fw-semibold">Gare concernée</label>
                                        <select class="form-select" name="id_agence">
                                            <option value="" disabled selected>Choisir la gare</option>
                                            @foreach ($listeAgences as $agence)
                                                <option value="{{ $agence->idAgence }}">{{ $agence->localite }} ({{ $agence->numeroGare }})</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Déduite de la caisse ouverte de cette gare.</small>
                                    </div>
                                @else
                                    <input type="hidden" name="portee" value="locale">
                                @endif

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Catégorie</label>
                                    <select class="form-select" id="depenseCategorie" name="categorie" required>
                                        <option value="" disabled selected>Choisir une catégorie</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Montant</label>
                                    <div class="input-group">
                                        <input type="number" min="1" step="1" class="form-control" name="montant" required>
                                        <span class="input-group-text">FCFA</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Date de la dépense</label>
                                    <input type="date" class="form-control" name="date_depense" value="{{ $aujourdhui }}" max="{{ $aujourdhui }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Libellé <span id="depenseLibelleHint" class="text-muted small"></span></label>
                                    <input type="text" class="form-control" id="depenseLibelle" name="libelle" placeholder="ex: achat imprimante guichet">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-floppy-disk me-1"></i> Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        (function () {
            const porteeSelect = document.getElementById('depensePortee');
            const champGare = document.getElementById('depenseChampGare');
            const categorieSelect = document.getElementById('depenseCategorie');
            const libelleInput = document.getElementById('depenseLibelle');
            const libelleHint = document.getElementById('depenseLibelleHint');

            function toggleChampGare() {
                if (!porteeSelect || !champGare) return;
                champGare.style.display = porteeSelect.value === 'globale' ? 'none' : '';
            }

            function toggleLibelleHint() {
                if (!categorieSelect) return;
                const isAutre = categorieSelect.value === 'Autre';
                libelleHint.textContent = isAutre ? '(obligatoire pour "Autre")' : '';
                libelleInput.required = isAutre;
            }

            porteeSelect?.addEventListener('change', toggleChampGare);
            categorieSelect?.addEventListener('change', toggleLibelleHint);
            toggleChampGare();
            toggleLibelleHint();

            document.querySelectorAll('.depense-valider-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const form = btn.closest('form');
                    Swal.fire({
                        title: 'Valider la dépense ?',
                        html: `Valider la dépense "<strong>${btn.dataset.libelle}</strong>" de <strong>${btn.dataset.montant} FCFA</strong> ? Elle sera déduite de la caisse.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Oui, valider',
                        cancelButtonText: 'Annuler',
                        customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-secondary' },
                        buttonsStyling: false,
                    }).then((result) => { if (result.isConfirmed) form.submit(); });
                });
            });

            document.querySelectorAll('.depense-rejeter-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const form = btn.closest('form');
                    Swal.fire({
                        title: 'Rejeter la dépense ?',
                        html: `Rejeter la dépense "<strong>${btn.dataset.libelle}</strong>" de <strong>${btn.dataset.montant} FCFA</strong> ?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Oui, rejeter',
                        cancelButtonText: 'Annuler',
                        customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-secondary' },
                        buttonsStyling: false,
                    }).then((result) => { if (result.isConfirmed) form.submit(); });
                });
            });
        })();
    </script>
@endsection
