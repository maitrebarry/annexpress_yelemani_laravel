@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Historique des dépôts · TransHub Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="bx bx-buildings me-1"></i> Banque</span>
@endsection
@section('breadcrumb-active', 'Historique des dépôts')

@section('breadcrumb-actions')
    <a href="{{ route('admin.depot-banque.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="bx bx-left-arrow-alt me-1"></i> Retour
    </a>
@endsection

@section('content')

    @include('admin.partials.set_flash')

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, #0f3b5e, #1d6fa5); color: #fff;">
            <i class="bx bx-history fs-5"></i>
            <span class="fw-semibold">Historique des demandes de dépôt</span>
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
                        <th class="border-0">Validé par</th>
                        <th class="border-0">Statut</th>
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
                            <td>{{ $d->validateur ?? '-' }}</td>
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
                        <tr><td colspan="8" class="text-center text-muted py-4">Aucune demande enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
