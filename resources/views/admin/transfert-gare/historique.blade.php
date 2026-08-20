@extends('layouts.admin')

@section('title', 'Transferts · TransHub Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="bx bx-calendar-check me-1"></i> G-programme</span>
@endsection
@section('breadcrumb-active', 'Transferts')

@section('breadcrumb-actions')
    <a href="{{ route('admin.programmation-voyage.liste-journaliere') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="bx bx-left-arrow-alt me-1"></i> Retour aux programmations
    </a>
@endsection

@section('content')

    @include('admin.partials.set_flash')

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="bx bx-transfer me-1"></i> Historique des transferts de passagers
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Date</th>
                            <th>Gare source</th>
                            <th>Gare destination</th>
                            <th>Passagers</th>
                            <th>Billets</th>
                            <th>Montant transféré</th>
                            <th>Effectué par</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($liste as $t)
                            <tr>
                                <td data-label="Date">{{ \Illuminate\Support\Carbon::parse($t->date_transfert)->format('d/m/Y H:i') }}</td>
                                <td data-label="Gare source">{{ ($t->agenceSource->localite ?? '-').' ('.($t->agenceSource->numeroGare ?? '-').')' }}</td>
                                <td data-label="Gare destination">{{ ($t->agenceDestination->localite ?? '-').' ('.($t->agenceDestination->numeroGare ?? '-').')' }}</td>
                                <td data-label="Passagers">{{ $t->nombre_passagers }}</td>
                                <td data-label="Billets">{{ $t->nombre_billets }}</td>
                                <td data-label="Montant transféré" class="fw-bold text-success">{{ number_format($t->montant_total, 0, ',', ' ') }} FCFA</td>
                                <td data-label="Effectué par">{{ $t->agent->utilisateurs ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted fst-italic">Aucun transfert enregistré.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
