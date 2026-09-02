@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Dépôt en banque · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-building-columns me-1"></i> Banque</span>
@endsection
@section('breadcrumb-active', 'Dépôt en banque')

@section('breadcrumb-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.depot-banque.historique') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
            <i class="fas fa-clock-rotate-left me-1"></i> Historique complet
        </a>
        @if ($authUser->droit !== 'PDG')
            <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNouveauDepot">
                <i class="fas fa-paper-plane me-1"></i> Faire un dépôt
            </button>
        @endif
    </div>
@endsection

@section('content')


    @if ($authUser->droit === 'chef_d_escale')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <div class="text-muted small"><i class="fas fa-wallet me-1"></i> Solde disponible pour dépôt (versements validés non encore déposés)</div>
                    <div class="fs-3 fw-bold {{ $soldeDisponible > 0 ? 'text-success' : 'text-muted' }}">{{ number_format($soldeDisponible, 0, ',', ' ') }} FCFA</div>
                </div>
                <div class="text-muted small text-end" style="max-width: 320px;">
                    Ce solde s'accumule avec chaque versement validé par vous — pas besoin de déposer le jour même, il reste disponible jusqu'à votre prochain dépôt.
                </div>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;">
            <i class="fas fa-list-ul fs-5"></i>
            <span class="fw-semibold">{{ $authUser->droit === 'chef_d_escale' ? 'Mes demandes' : 'Demandes récentes' }}</span>
        </div>
        <div class="table-responsive p-2">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th class="border-0">Date</th>
                        @if ($authUser->droit !== 'chef_d_escale')
                            <th class="border-0">Gare</th>
                        @endif
                        <th class="border-0">Banque</th>
                        <th class="border-0">Montant</th>
                        <th class="border-0">Référence</th>
                        <th class="border-0">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($listeDemandes as $d)
                        <tr>
                            <td>{{ optional($d->date_demande)->format('d/m/Y H:i') }}</td>
                            @if ($authUser->droit !== 'chef_d_escale')
                                <td>{{ $d->localite }} ({{ $d->numeroGare }})</td>
                            @endif
                            <td>{{ $d->nom_banque }}</td>
                            <td class="fw-bold">{{ number_format($d->montant, 0, ',', ' ') }} F</td>
                            <td>{{ $d->reference ?? '-' }}</td>
                            <td>
                                @if ($d->statut === 'en_attente')
                                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">En attente</span>
                                @elseif ($d->statut === 'confirme')
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">Confirmé</span>
                                @else
                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-2" title="{{ $d->motif_rejet }}">Rejeté</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $authUser->droit !== 'chef_d_escale' ? 6 : 5 }}" class="text-center text-muted py-4">Aucune demande enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('modals')
    @if ($authUser->droit !== 'PDG')
        <div class="modal fade" id="modalNouveauDepot" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="{{ route('admin.depot-banque.store') }}">
                        @csrf
                        <div class="modal-header bg-success">
                            <h5 class="modal-title text-white"><i class="fas fa-paper-plane me-1"></i> Nouvelle demande de dépôt</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @if ($listeBanques->isEmpty())
                                <p class="text-muted mb-0">Aucun compte banque actif n'est disponible pour l'instant. Contactez votre admin.</p>
                            @else
                                <div class="row g-3">
                                    @if ($authUser->droit === 'Admin')
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Gare concernée</label>
                                            <select class="form-select" name="id_agence" required>
                                                <option value="" disabled selected>Choisir la gare</option>
                                                @foreach ($listeAgences as $agence)
                                                    <option value="{{ $agence->idAgence }}">{{ $agence->localite }} ({{ $agence->numeroGare }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div class="col-12">
                                            <div class="alert alert-light border small mb-0">
                                                Solde disponible : <strong>{{ number_format($soldeDisponible, 0, ',', ' ') }} FCFA</strong>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Compte banque</label>
                                        <select class="form-select" name="id_banque" required>
                                            <option value="" disabled selected>Choisir un compte</option>
                                            @foreach ($listeBanques as $b)
                                                <option value="{{ $b->id_banque }}">{{ $b->nom }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Montant à déposer</label>
                                        <div class="input-group">
                                            <input type="number" min="1" step="1" class="form-control" name="montant" required>
                                            <span class="input-group-text">FCFA</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Référence (optionnel)</label>
                                        <input type="text" class="form-control" name="reference" placeholder="n° de bordereau">
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-3">
                                    @if ($authUser->droit === 'chef_d_escale')
                                        La demande sera soumise à l'admin. L'argent ne sort de votre solde qu'après sa validation.
                                    @else
                                        L'argent n'est débité qu'après confirmation depuis "Demandes en attente".
                                    @endif
                                </small>
                            @endif
                        </div>
                        @if ($listeBanques->isNotEmpty())
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> Envoyer la demande</button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
