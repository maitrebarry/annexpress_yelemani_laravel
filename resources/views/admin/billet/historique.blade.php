@extends('layouts.admin')

@php
    $authUser = auth('staff')->user();
    $montantTotal = $listeHistorique->sum(fn ($b) => (float) preg_replace('/[^\d.]/', '', (string) $b->montant_payer));
    $peutImprimer = $authUser->userHasPermission('Billets_impression');
@endphp

@section('title', 'Historique des billets · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-list me-1"></i> G-réservation</span>
@endsection
@section('breadcrumb-active', 'Historique des billets')

@section('breadcrumb-actions')
    <a href="{{ route('admin.billet.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
@endsection

@section('content')


    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('admin.billet.historique') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Date</label>
                    <input type="date" class="form-control" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Destination</label>
                    <select class="form-select" name="destination">
                        <option value="">Toutes</option>
                        @foreach ($destinations as $d)
                            <option value="{{ $d }}" {{ $destinationSelectionnee === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Heure</label>
                    <input type="time" class="form-control" name="heure" value="{{ $heureSelectionnee }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="text-muted small">Billets — {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</div>
                    <div class="fs-4 fw-bold">{{ $listeHistorique->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card bg-primary text-white border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small opacity-75">Montant total</div>
                    <div class="fs-4 fw-bold">{{ number_format($montantTotal, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;">
            <i class="fas fa-list-ul fs-5"></i>
            <span class="fw-semibold">Billets du {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</span>
        </div>
        <div class="table-responsive p-2">
            <table id="example" class="table table-hover align-middle mb-0" style="width:100%">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th class="border-0">N° billet</th>
                        <th class="border-0">Client</th>
                        <th class="border-0">Destination</th>
                        <th class="border-0">N° place</th>
                        <th class="border-0">Heure</th>
                        <th class="border-0">Statut</th>
                        <th class="border-0 text-end">Montant</th>
                        @if ($peutImprimer)
                            <th class="border-0 text-center">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listeHistorique as $b)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $b->numeroBillets }}</span></td>
                            <td class="fw-semibold">{{ $b->Client }}</td>
                            <td>{{ $b->destinationId }}</td>
                            <td>{{ $b->numeroPlace ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($b->Heur_departs)->format('H:i') }}</td>
                            <td>
                                @if ($b->status_billets === 'annule')
                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">Annulé</span>
                                @elseif ($b->validation_billets === 'valider')
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">Validé</span>
                                @else
                                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">En attente</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-success">{{ number_format((float) preg_replace('/[^\d.]/', '', (string) $b->montant_payer), 0, ',', ' ') }} F</td>
                            @if ($peutImprimer)
                                <td class="text-center">
                                    <div class="dropdown">
                                        <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item thermal-print-btn" href="#" data-id="{{ $b->idBillets }}">
                                                    <i class="fas fa-print me-2"></i>Imprimer (thermique)
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ url('/admin/Liste_du_jours/recu/' . $b->idBillets) }}" target="_blank">
                                                    <i class="fas fa-file-pdf me-2"></i>Ouvrir PDF
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('scripts')
    @if ($peutImprimer)
        <script src="{{ asset('mon_js/thermal-print.js') }}"></script>
    @endif
@endsection
