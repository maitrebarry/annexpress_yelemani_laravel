@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Supervision Escale · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-wallet me-1"></i> Caisse</span>
@endsection
@section('breadcrumb-active', 'Supervision Escale')

@section('breadcrumb-actions')
    @if ($idAgence && $authUser->userHasPermission('Caisse_modifier'))
        <button type="button" class="btn btn-sm btn-warning rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalClotureEscale">
            <i class="fas fa-check-double me-1"></i> Clôturer la journée
        </button>
    @endif
@endsection

@section('content')


    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('admin.caisse.caisses-escale') }}" class="row g-3 align-items-end">
                @if ($estAdmin)
                    <div class="col-md-5">
                        <label class="form-label mb-1">Gare</label>
                        <select class="form-select" name="id_agence" onchange="this.form.submit()">
                            <option value="" disabled {{ ! $idAgence ? 'selected' : '' }}>Choisissez une gare</option>
                            @foreach ($listeAgences as $a)
                                <option value="{{ $a->idAgence }}" {{ $idAgence == $a->idAgence ? 'selected' : '' }}>{{ $a->localite.' ('.$a->numeroGare.')' }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-4">
                    <label class="form-label mb-1">Date</label>
                    <input type="date" class="form-control" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    @if (! $idAgence)
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-building-columns fs-1 text-muted"></i>
                <p class="text-muted mt-2 mb-0">Choisissez une gare pour afficher ses caisses.</p>
            </div>
        </div>
    @else
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                    <div class="card-body">
                        <div class="text-muted small">Billets</div>
                        <div class="fs-5 fw-bold">{{ number_format($totalBillets, 0, ',', ' ') }} F</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                    <div class="card-body">
                        <div class="text-muted small">Colis</div>
                        <div class="fs-5 fw-bold">{{ number_format($totalColis, 0, ',', ' ') }} F</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card bg-primary text-white border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small opacity-75">Total recette</div>
                        <div class="fs-5 fw-bold">{{ number_format($totalBillets + $totalColis, 0, ',', ' ') }} F</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm border-start border-4 {{ $totalEcarts < 0 ? 'border-danger' : 'border-success' }} h-100">
                    <div class="card-body">
                        <div class="text-muted small">Écarts cumulés</div>
                        <div class="fs-5 fw-bold {{ $totalEcarts < 0 ? 'text-danger' : 'text-success' }}">{{ $totalEcarts > 0 ? '+' : '' }}{{ number_format($totalEcarts, 0, ',', ' ') }} F</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="fas fa-wallet me-1"></i> Caisses des opérateurs
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Opérateur</th>
                                        <th>Statut</th>
                                        <th class="text-end">Recette</th>
                                        <th class="text-end">Compté</th>
                                        <th class="text-end">Écart</th>
                                        <th>Versement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($caisses as $c)
                                        <tr>
                                            <td>{{ $c->utilisateurs }}<br><small class="text-muted">{{ $c->reference }}</small></td>
                                            <td>
                                                @if ($c->statut === 'ouverte')
                                                    <span class="badge bg-success">Ouverte</span>
                                                @elseif ($c->statut === 'fermee')
                                                    <span class="badge bg-secondary">Fermée</span>
                                                @else
                                                    <span class="badge bg-primary">Versée</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ number_format($c->total_billets + $c->total_colis, 0, ',', ' ') }} F</td>
                                            <td class="text-end">{{ $c->montant_compte !== null ? number_format($c->montant_compte, 0, ',', ' ').' F' : '—' }}</td>
                                            <td class="text-end {{ $c->ecart > 0 ? 'text-success' : ($c->ecart < 0 ? 'text-danger' : '') }}">{{ $c->ecart !== null ? ($c->ecart > 0 ? '+' : '').number_format($c->ecart, 0, ',', ' ').' F' : '—' }}</td>
                                            <td>
                                                @if ($c->statut_versement === 'en_attente')
                                                    <span class="badge bg-warning text-dark">En attente</span>
                                                @elseif ($c->statut_versement === 'valide')
                                                    <span class="badge bg-success">{{ number_format($c->montant_verse, 0, ',', ' ') }} F validé</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-muted fst-italic text-center py-3">Aucune caisse pour cette date.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card shadow-sm border-0 border-top border-4 border-warning h-100">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-clock me-1"></i> Versements en attente</span>
                        <span class="badge bg-warning text-dark">{{ $versementsAttente->count() }}</span>
                    </div>
                    <div class="card-body">
                        @forelse ($versementsAttente as $v)
                            <div class="card mb-2 border">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold">{{ $v->utilisateurs }}</div>
                                            <div class="text-muted small">{{ \Illuminate\Support\Carbon::parse($v->date_versement)->format('H:i') }}</div>
                                        </div>
                                        <div class="fs-5 fw-bold text-primary">{{ number_format($v->montant, 0, ',', ' ') }} F</div>
                                    </div>
                                    @if ($v->commentaire)
                                        <div class="small fst-italic bg-light rounded p-1 mt-1">{{ $v->commentaire }}</div>
                                    @endif
                                    @if ($authUser->userHasPermission('Caisse_modifier'))
                                        <div class="d-flex gap-2 mt-2">
                                            <form method="post" action="{{ route('admin.caisse.valider-versement') }}" class="flex-fill">
                                                @csrf
                                                <input type="hidden" name="id_versement" value="{{ $v->id_versement }}">
                                                <input type="hidden" name="action" value="valide">
                                                <input type="hidden" name="id_agence" value="{{ $idAgence }}">
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-check"></i> Valider</button>
                                            </form>
                                            <form method="post" action="{{ route('admin.caisse.valider-versement') }}" class="reject-versement-form flex-fill">
                                                @csrf
                                                <input type="hidden" name="id_versement" value="{{ $v->id_versement }}">
                                                <input type="hidden" name="action" value="rejete">
                                                <input type="hidden" name="id_agence" value="{{ $idAgence }}">
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 reject-versement-btn"><i class="fas fa-xmark"></i> Rejeter</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small fst-italic mb-0">Aucun versement en attente.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-clock-rotate-left me-1"></i> Historique des versements
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Opérateur</th>
                                <th>Date</th>
                                <th class="text-end">Montant</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historiqueVersements as $v)
                                <tr>
                                    <td>{{ $v->utilisateurs }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($v->date_versement)->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">{{ number_format($v->montant, 0, ',', ' ') }} F</td>
                                    <td>
                                        @if ($v->statut === 'en_attente')
                                            <span class="badge bg-warning text-dark">En attente</span>
                                        @elseif ($v->statut === 'valide')
                                            <span class="badge bg-success">Validé</span>
                                        @else
                                            <span class="badge bg-danger">Rejeté</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted fst-italic text-center py-3">Aucun versement.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('modals')
    @if ($idAgence && $authUser->userHasPermission('Caisse_modifier'))
        <div class="modal fade" id="modalClotureEscale" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark"><i class="fas fa-check-double me-1"></i> Clôturer la journée du {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($caissesOuvertes > 0)
                            <div class="alert alert-danger">
                                <i class="fas fa-circle-exclamation me-1"></i>
                                Clôture impossible : <strong>{{ $caissesOuvertes }}</strong> caisse(s) sont encore ouvertes pour cette gare aujourd'hui. Toutes doivent être fermées avant la clôture.
                            </div>
                        @else
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="p-2 bg-light rounded text-center">
                                        <div class="text-muted small">Billets</div>
                                        <div class="fw-bold">{{ number_format($totalBillets, 0, ',', ' ') }} F</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 bg-light rounded text-center">
                                        <div class="text-muted small">Colis</div>
                                        <div class="fw-bold">{{ number_format($totalColis, 0, ',', ' ') }} F</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 rounded text-center" style="background: rgba(13,110,253,.1);">
                                        <div class="text-muted small">Total</div>
                                        <div class="fw-bold text-primary">{{ number_format($totalBillets + $totalColis, 0, ',', ' ') }} F</div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-warning small mb-0">
                                <i class="fas fa-triangle-exclamation me-1"></i> Cette action est définitive : elle génère le rapport officiel de clôture pour l'Admin et les propriétaires. Assurez-vous que tous les versements sont validés.
                            </div>
                        @endif

                        @if ($historiqueClotures->isNotEmpty())
                            <hr>
                            <p class="fw-semibold small mb-2">Clôtures précédentes</p>
                            <ul class="list-group list-group-flush">
                                @foreach ($historiqueClotures->take(5) as $hc)
                                    <li class="list-group-item px-0 py-1 d-flex justify-content-between small">
                                        <span>{{ \Illuminate\Support\Carbon::parse($hc->date_cloture)->format('d/m/Y') }}</span>
                                        <span class="fw-semibold">{{ number_format($hc->total_billets + $hc->total_colis, 0, ',', ' ') }} F</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        @if ($caissesOuvertes === 0)
                            <form method="post" action="{{ route('admin.caisse.cloture-escale') }}">
                                @csrf
                                <input type="hidden" name="id_agence" value="{{ $idAgence }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                <button type="submit" class="btn btn-warning fw-semibold text-dark"><i class="fas fa-check-double me-2"></i>Valider la clôture</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.reject-versement-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var form = btn.closest('form');
                    Swal.fire({
                        title: 'Rejeter ce versement ?',
                        text: "Cette action ne peut pas être annulée.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Oui, rejeter',
                        cancelButtonText: 'Annuler',
                        customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-secondary' },
                        buttonsStyling: false,
                    }).then(function (result) {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        });
    </script>
@endsection
