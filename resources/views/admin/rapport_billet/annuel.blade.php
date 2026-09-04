@extends('layouts.admin')

@php
    $badgeType = function (string $type) {
        return match ($type) {
            'repporte' => '<span class="badge bg-warning text-dark">Billets reportés</span>',
            'presentiel' => '<span class="badge bg-primary">Présentiel</span>',
            default => '<span class="badge bg-success">En ligne</span>',
        };
    };
@endphp

@section('title', 'Rapport annuel des billets · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-chart-column me-1"></i> Rapport billets</span>
@endsection
@section('breadcrumb-active', 'Rapport annuel')

@section('breadcrumb-actions')
    <a href="{{ route('admin.rapport-billet.mensuel') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-calendar-days me-1"></i> Rapport mensuel
    </a>
@endsection

@section('content')


    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="card border-start border-4 border-primary shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted fw-medium">Présentiel</p>
                        <h3 class="mb-0 text-primary">{{ $totalPresentiel }}</h3>
                    </div>
                    <i class="fas fa-box fs-1 text-primary"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-start border-4 border-success shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted fw-medium">En ligne</p>
                        <h3 class="mb-0 text-success">{{ $totalEnLigne }}</h3>
                    </div>
                    <i class="fas fa-paper-plane fs-1 text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-start border-4 border-warning shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted fw-medium">Reportés</p>
                        <h3 class="mb-0 text-warning">{{ $totalRepporte }}</h3>
                    </div>
                    <i class="fas fa-hourglass-half fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Statistiques annuelles pour {{ $annee }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Type de réservation</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($statsParAnnee as $row)
                                    <tr>
                                        <td>{!! $badgeType($row->type_reservation) !!}</td>
                                        <td>{{ $row->total }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">Aucune donnée disponible pour cette année</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <h4 class="mb-3">Situation des caisses et billets pour l'année {{ $annee }}</h4>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle text-center mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Localité</th>
                                    <th>Gare</th>
                                    <th>Type de réservation</th>
                                    <th>Total payé (FCFA)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($billetsParGare as $row)
                                    <tr>
                                        <td>{{ $row->localite }}</td>
                                        <td>{{ $row->num_gare }}</td>
                                        <td>{!! $badgeType($row->status_reservation) !!}</td>
                                        <td class="fw-bold text-end">{{ number_format($row->total, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Aucune donnée disponible pour cette année</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
