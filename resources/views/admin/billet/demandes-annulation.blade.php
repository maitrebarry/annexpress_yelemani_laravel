@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', "Demandes d'annulation · TransGest Admin")

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-list me-1"></i> G-réservation</span>
@endsection
@section('breadcrumb-active', "Demandes d'annulation")

@section('breadcrumb-actions')
    <a href="{{ route('admin.billet.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
@endsection

@section('content')


    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="text-muted small"><i class="fas fa-clock me-1"></i> Demandes en attente</div>
                    <div class="fs-4 fw-bold">{{ $listeDemandes->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;">
            <i class="fas fa-clock fs-5"></i>
            <span class="fw-semibold">À valider</span>
        </div>
        <div class="table-responsive p-2">
            <table id="example" class="table table-hover align-middle mb-0" style="width:100%">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th class="border-0">N° billet</th>
                        <th class="border-0">Client</th>
                        <th class="border-0">Destination</th>
                        <th class="border-0">Voyage</th>
                        <th class="border-0">Montant</th>
                        <th class="border-0">Motif</th>
                        <th class="border-0">Demandé par</th>
                        <th class="border-0">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listeDemandes as $d)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $d->numeroBillets }}</span></td>
                            <td class="fw-semibold">{{ $d->Client }}</td>
                            <td>{{ $d->departId }} → {{ $d->destinationId }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($d->jourVoyage)->format('d/m/Y') }} {{ \Illuminate\Support\Carbon::parse($d->Heur_departs)->format('H:i') }}</td>
                            <td class="fw-bold">{{ number_format((float) preg_replace('/[^\d.]/', '', (string) $d->montant_payer), 0, ',', ' ') }} F</td>
                            <td>{{ $d->motif_annulation ?? '-' }}</td>
                            <td>{{ $d->demandeur ?? '-' }}</td>
                            <td class="d-flex gap-2">
                                @if ($authUser->estLectureSeule())
                                    <span class="text-muted small">Lecture seule</span>
                                @else
                                    <form method="post" action="{{ route('admin.billet.confirmer-annulation', $d->idBillets) }}" id="form-confirmer-{{ $d->idBillets }}">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-success btn-confirmer-annulation-demande"
                                            data-id="{{ $d->idBillets }}" data-numero="{{ $d->numeroBillets }}">
                                            <i class="fas fa-check"></i> Confirmer
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('admin.billet.rejeter-annulation', $d->idBillets) }}" id="form-rejeter-{{ $d->idBillets }}">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-rejeter-annulation-demande"
                                            data-id="{{ $d->idBillets }}" data-numero="{{ $d->numeroBillets }}">
                                            <i class="fas fa-xmark"></i> Rejeter
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.btn-confirmer-annulation-demande').forEach(function (btn) {
            btn.addEventListener('click', function () {
                Swal.fire({
                    title: 'Confirmer cette annulation ?',
                    html: 'Le billet <strong>' + btn.dataset.numero + '</strong> sera annulé : la place sera restituée et un remboursement enregistré comme dépense.',
                    icon: 'warning',
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

        document.querySelectorAll('.btn-rejeter-annulation-demande').forEach(function (btn) {
            btn.addEventListener('click', function () {
                Swal.fire({
                    title: 'Rejeter cette demande ?',
                    html: 'Le billet <strong>' + btn.dataset.numero + '</strong> restera actif, aucune place ni argent ne sera libéré.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, rejeter',
                    cancelButtonText: 'Annuler',
                    customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-secondary' },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.isConfirmed) document.getElementById('form-rejeter-' + btn.dataset.id).submit();
                });
            });
        });
    </script>
@endsection
