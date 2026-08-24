@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Tickets en entente · TransHub Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="bx bx-category me-1"></i> G-réservation</span>
@endsection
@section('breadcrumb-active', 'Tickets en entente')

@section('breadcrumb-actions')
    <a href="{{ route('admin.billet.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="bx bx-left-arrow-alt me-1"></i> Retour
    </a>
@endsection

@section('content')

    @include('admin.partials.set_flash')

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="text-muted small"><i class="bx bx-time me-1"></i> Réservations en attente</div>
                    <div class="fs-4 fw-bold">{{ $liste->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, #0f3b5e, #1d6fa5); color: #fff;">
            <i class="bx bx-globe fs-5"></i>
            <span class="fw-semibold">Réservations en ligne à valider</span>
        </div>
        <div class="table-responsive p-2">
            <table id="example" class="table table-hover align-middle mb-0" style="width:100%">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th class="border-0">N° billet</th>
                        <th class="border-0">Client</th>
                        <th class="border-0">Destination</th>
                        <th class="border-0">Passagers</th>
                        <th class="border-0">Voyage</th>
                        <th class="border-0">Expire le</th>
                        <th class="border-0 text-end">Montant</th>
                        <th class="border-0">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($liste as $b)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $b->numeroBillets }}</span></td>
                            <td class="fw-semibold">{{ $b->Client }}</td>
                            <td>{{ $b->departId }} <i class="bx bx-right-arrow-alt"></i> {{ $b->destinationId }}</td>
                            <td>{{ $b->nombrePassages }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($b->jourVoyage)->format('d/m/Y') }} {{ \Illuminate\Support\Carbon::parse($b->Heur_departs)->format('H:i') }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($b->date_expiration)->format('d/m/Y') }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format((float) preg_replace('/[^\d.]/', '', (string) $b->montant_payer), 0, ',', ' ') }} F</td>
                            <td>
                                @if ($authUser->estLectureSeule())
                                    <span class="text-muted small">Lecture seule</span>
                                @else
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalValider{{ $b->idBillets }}">
                                        <i class="bx bx-check-shield"></i> Valider
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@if (! $authUser->estLectureSeule())
    @foreach ($liste as $b)
        <div class="modal fade" id="modalValider{{ $b->idBillets }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="{{ route('admin.entente.valider', $b->idBillets) }}">
                        @csrf
                        <div class="modal-header bg-success">
                            <h5 class="modal-title text-white"><i class="bx bx-check-shield me-1"></i> Valider la réservation</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label small text-muted">Client</label>
                                    <input type="text" class="form-control" value="{{ $b->Client }}" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted">Destination</label>
                                    <input type="text" class="form-control" value="{{ $b->destinationId }}" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted">Jour / heure</label>
                                    <input type="text" class="form-control" value="{{ \Illuminate\Support\Carbon::parse($b->jourVoyage)->format('d/m/Y') }} — {{ \Illuminate\Support\Carbon::parse($b->Heur_departs)->format('H:i') }}" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted">Email client</label>
                                    <input type="text" class="form-control" value="{{ $b->emailClient ?: 'Non renseigné' }}" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted">Montant à encaisser</label>
                                    <input type="text" class="form-control fw-bold" value="{{ number_format((float) preg_replace('/[^\d.]/', '', (string) $b->montant_payer), 0, ',', ' ') }} FCFA" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Confirmer le n° de paiement</label>
                                    <input type="text" class="form-control" name="confirme" placeholder="Numéro Orange Money utilisé" required>
                                </div>
                            </div>
                            <div class="alert alert-info small mt-3 mb-0">
                                <i class="bx bx-info-circle me-1"></i> Le client a communiqué le n° <strong>{{ $b->numeroPaiement }}</strong> lors du paiement — demandez-lui de le confirmer avant de valider. Le montant sera crédité sur votre caisse ouverte et un reçu envoyé au client si un email a été renseigné.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-success">Valider la réservation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif
