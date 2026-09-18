@extends('layouts.admin')

@section('title', 'Anomalies de caisse · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-wallet me-1"></i> Caisse</span>
@endsection
@section('breadcrumb-active', 'Anomalies de caisse')

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <p class="text-muted small mb-0">Caisses jamais fermées ou fermées-non-versées d'un jour précédent, toutes gares confondues.</p>
        <span class="badge bg-danger rounded-pill px-3 py-2">{{ $anomalies->count() }} anomalie(s)</span>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($anomalies->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-shield-halved fs-1"></i>
                    <p class="mt-2 mb-0">Aucune anomalie : toutes les caisses des jours précédents ont été fermées et versées.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Gare</th>
                                <th>Opérateur</th>
                                <th>Rôle</th>
                                <th>Date</th>
                                <th>Ancienneté</th>
                                <th class="text-end">Billets</th>
                                <th class="text-end">Colis</th>
                                <th>Statut</th>
                                <th>Détail</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @foreach ($anomalies as $a)
                                <tr>
                                    <td>{{ $a->localite }} ({{ $a->numeroGare }})</td>
                                    <td class="fw-semibold">{{ $a->nom_operateur }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $a->droit }}</span></td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($a->date_service)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge {{ $a->jours_ecoules > 3 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                            {{ (int) $a->jours_ecoules }} jour{{ $a->jours_ecoules > 1 ? 's' : '' }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ number_format($a->total_billets, 0, ',', ' ') }} F</td>
                                    <td class="text-end">{{ number_format($a->total_colis, 0, ',', ' ') }} F</td>
                                    <td>
                                        @if ($a->statut === 'ouverte')
                                            <span class="badge bg-success">Jamais fermée</span>
                                        @elseif (($a->statut_versement ?? null) === 'en_attente')
                                            <span class="badge bg-info-subtle text-info-emphasis">Versement en attente</span>
                                        @else
                                            <span class="badge bg-secondary">Fermée, non versée</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.caisse.caisses-escale', ['id_agence' => $a->id_agence, 'date' => $a->date_service]) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-magnifying-glass me-1"></i>Voir la gare ce jour-là
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <p class="text-muted small mt-3">
        <i class="fas fa-circle-info me-1"></i>Vue en lecture seule : seul le titulaire d'une caisse peut la fermer ou la verser
        (depuis son propre écran "Ma Caisse" &gt; Caisses anciennes).
    </p>

@endsection
