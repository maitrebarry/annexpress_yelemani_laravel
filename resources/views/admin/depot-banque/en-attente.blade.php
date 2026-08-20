@extends('layouts.admin')

@php $authUser = auth('staff')->user(); $totalEnAttente = $listeDemandes->sum('montant'); @endphp

@section('title', 'Demandes de dépôt en attente · TransHub Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="bx bx-buildings me-1"></i> Banque</span>
@endsection
@section('breadcrumb-active', 'Demandes en attente')

@section('breadcrumb-actions')
    <a href="{{ route('admin.depot-banque.historique') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="bx bx-history me-1"></i> Historique complet
    </a>
@endsection

@section('content')

    @include('admin.partials.set_flash')

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="text-muted small"><i class="bx bx-time me-1"></i> Demandes en attente</div>
                    <div class="fs-4 fw-bold">{{ $listeDemandes->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card bg-primary text-white border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small opacity-75"><i class="bx bx-money me-1"></i> Montant total</div>
                    <div class="fs-4 fw-bold">{{ number_format($totalEnAttente, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, #0f3b5e, #1d6fa5); color: #fff;">
            <i class="bx bx-time fs-5"></i>
            <span class="fw-semibold">À valider</span>
        </div>
        <div class="table-responsive p-2">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th class="border-0">Date</th>
                        <th class="border-0">Gare</th>
                        <th class="border-0">Banque</th>
                        <th class="border-0">Montant</th>
                        <th class="border-0">Référence</th>
                        <th class="border-0">Demandé par</th>
                        <th class="border-0">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($listeDemandes as $d)
                        <tr>
                            <td>{{ optional($d->date_demande)->format('d/m/Y H:i') }}</td>
                            <td>{{ $d->localite }} ({{ $d->numeroGare }})</td>
                            <td>{{ $d->nom_banque }}</td>
                            <td class="fw-bold">{{ number_format($d->montant, 0, ',', ' ') }} F</td>
                            <td>{{ $d->reference ?? '-' }}</td>
                            <td>{{ $d->demandeur }}</td>
                            <td class="d-flex gap-2">
                                @if ($authUser->droit === 'PDG')
                                    <span class="text-muted small">Lecture seule</span>
                                @else
                                    <form method="post" action="{{ route('admin.depot-banque.confirmer', $d->id_depot) }}" id="form-confirmer-{{ $d->id_depot }}">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-success btn-confirmer-depot"
                                            data-id="{{ $d->id_depot }}" data-montant="{{ (float) $d->montant }}" data-gare="{{ $d->localite }} ({{ $d->numeroGare }})">
                                            <i class="bx bx-check"></i> Confirmer
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRejet{{ $d->id_depot }}">
                                        <i class="bx bx-x"></i> Rejeter
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Aucune demande en attente.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('modals')
    @if ($authUser->droit !== 'PDG')
        @foreach ($listeDemandes as $d)
            <div class="modal fade" id="modalRejet{{ $d->id_depot }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="post" action="{{ route('admin.depot-banque.rejeter', $d->id_depot) }}">
                            @csrf
                            <div class="modal-header bg-danger">
                                <h5 class="modal-title text-white">Rejeter la demande</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label fw-semibold">Motif (optionnel)</label>
                                <textarea class="form-control" name="motif_rejet" rows="3"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-danger">Rejeter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.btn-confirmer-depot').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const montantFmt = Number(btn.dataset.montant).toLocaleString('fr-FR');
                Swal.fire({
                    title: 'Confirmer ce dépôt ?',
                    html: '<strong>' + montantFmt + ' FCFA</strong> seront débités du solde de <strong>' + btn.dataset.gare + '</strong> et crédités sur le compte banque.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, confirmer',
                    cancelButtonText: 'Annuler',
                    customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-secondary' },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.isConfirmed) document.getElementById('form-confirmer-' + btn.dataset.id).submit();
                });
            });
        });
    </script>
@endsection
