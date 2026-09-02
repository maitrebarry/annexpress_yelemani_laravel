@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Demandes de report · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-list me-1"></i> G-réservation</span>
@endsection
@section('breadcrumb-active', 'Demandes de report')

@section('breadcrumb-actions')
    <a href="{{ route('admin.billet.embarquement') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour à l'embarquement
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
            <span class="fw-semibold">{{ $estAdmin ? 'À valider (transmises par un chef d\'escale)' : 'À examiner (votre gare)' }}</span>
        </div>
        <div class="table-responsive p-2">
            <table id="example" class="table table-hover align-middle mb-0" style="width:100%">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th class="border-0">N° billet</th>
                        <th class="border-0">Client</th>
                        <th class="border-0">Départ actuel</th>
                        <th class="border-0">Nouveau départ demandé</th>
                        <th class="border-0">Demandé par</th>
                        @if ($estAdmin)
                            <th class="border-0">Transmis par</th>
                        @endif
                        <th class="border-0">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listeDemandes as $d)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $d->numeroBillets }}</span></td>
                            <td class="fw-semibold">{{ $d->Client }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($d->jourVoyage)->format('d/m/Y') }} {{ \Illuminate\Support\Carbon::parse($d->Heur_departs)->format('H:i') }}<br>
                                <span class="text-muted small">{{ $d->departId }} → {{ $d->destinationId }}</span></td>
                            <td class="fw-semibold text-primary">{{ \Illuminate\Support\Carbon::parse($d->nouvelle_date_demandee)->format('d/m/Y') }} {{ \Illuminate\Support\Carbon::parse($d->nouvelle_heure_demandee)->format('H:i') }}</td>
                            <td>{{ $d->demandeur ?? '-' }}</td>
                            @if ($estAdmin)
                                <td>{{ $d->transmis_par_nom ?? '-' }}</td>
                            @endif
                            <td class="d-flex gap-2">
                                @if ($authUser->estLectureSeule())
                                    <span class="text-muted small">Lecture seule</span>
                                @elseif ($estAdmin)
                                    <form method="post" action="{{ route('admin.billet.confirmer-report', $d->idBillets) }}" id="form-confirmer-report-{{ $d->idBillets }}">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-success btn-confirmer-report" data-id="{{ $d->idBillets }}" data-numero="{{ $d->numeroBillets }}">
                                            <i class="fas fa-check"></i> Confirmer
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('admin.billet.rejeter-report', $d->idBillets) }}" id="form-rejeter-report-{{ $d->idBillets }}">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-rejeter-report" data-id="{{ $d->idBillets }}" data-numero="{{ $d->numeroBillets }}">
                                            <i class="fas fa-xmark"></i> Rejeter
                                        </button>
                                    </form>
                                @else
                                    <form method="post" action="{{ route('admin.billet.transmettre-report', $d->idBillets) }}" id="form-transmettre-report-{{ $d->idBillets }}">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-success btn-transmettre-report" data-id="{{ $d->idBillets }}" data-numero="{{ $d->numeroBillets }}">
                                            <i class="fas fa-paper-plane"></i> Transmettre
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('admin.billet.rejeter-report', $d->idBillets) }}" id="form-rejeter-report-{{ $d->idBillets }}">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-rejeter-report" data-id="{{ $d->idBillets }}" data-numero="{{ $d->numeroBillets }}">
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
        document.querySelectorAll('.btn-confirmer-report').forEach(function (btn) {
            btn.addEventListener('click', function () {
                Swal.fire({
                    title: 'Confirmer ce report ?',
                    html: 'Le billet <strong>' + btn.dataset.numero + '</strong> sera reprogrammé à la nouvelle date/heure demandée.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, confirmer',
                    cancelButtonText: 'Annuler',
                    customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-secondary' },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.isConfirmed) document.getElementById('form-confirmer-report-' + btn.dataset.id).submit();
                });
            });
        });

        document.querySelectorAll('.btn-transmettre-report').forEach(function (btn) {
            btn.addEventListener('click', function () {
                Swal.fire({
                    title: "Transmettre cette demande à l'Admin ?",
                    html: 'Le billet <strong>' + btn.dataset.numero + '</strong> attendra la validation finale.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, transmettre',
                    cancelButtonText: 'Annuler',
                    customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-secondary' },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.isConfirmed) document.getElementById('form-transmettre-report-' + btn.dataset.id).submit();
                });
            });
        });

        document.querySelectorAll('.btn-rejeter-report').forEach(function (btn) {
            btn.addEventListener('click', function () {
                Swal.fire({
                    title: 'Rejeter cette demande ?',
                    html: 'Le billet <strong>' + btn.dataset.numero + '</strong> restera sur son départ initial.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, rejeter',
                    cancelButtonText: 'Annuler',
                    customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-secondary' },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.isConfirmed) document.getElementById('form-rejeter-report-' + btn.dataset.id).submit();
                });
            });
        });
    </script>
@endsection
