@extends('layouts.admin')

@section('title', 'Bénéfice de la compagnie · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-money-bill-wave me-1"></i> Finances</span>
@endsection
@section('breadcrumb-active', 'Bénéfice de la compagnie')

@section('breadcrumb-actions')
    <a href="{{ route('admin.depense.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-money-bill-wave me-1"></i> Gérer les dépenses
    </a>
@endsection

@section('content')


    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex flex-wrap gap-2">
            <a href="?periode=jour" class="btn btn-sm {{ $periode === 'jour' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill">Aujourd'hui</a>
            <a href="?periode=mois" class="btn btn-sm {{ $periode === 'mois' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill">Ce mois-ci</a>
            <a href="?periode=tout" class="btn btn-sm {{ $periode === 'tout' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill">Depuis le début</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="text-muted small">Revenus billets</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($benefice['revenus_billets'], 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="text-muted small">Revenus colis</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($benefice['revenus_colis'], 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="text-muted small">Revenus location de cars</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($benefice['revenus_location'], 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                <div class="card-body">
                    <div class="text-muted small">Remboursements</div>
                    <div class="fs-4 fw-bold text-danger">-{{ number_format($benefice['remboursements'], 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body text-center py-5">
            <div class="text-secondary mb-2">Bénéfice net</div>
            <div class="display-4 fw-bold {{ $benefice['benefice'] >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($benefice['benefice'], 0, ',', ' ') }} FCFA
            </div>
            <small class="text-muted d-block mt-2">
                Revenus (billets + colis + location) − remboursements − dépenses locales − dépenses globales
            </small>
            <small class="text-muted d-block">
                <i class="fas fa-circle-info"></i> Les remboursements sont cumulés depuis l'ouverture des caisses (pas encore filtrables par période).
            </small>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="text-muted small">Dont dépenses locales (gares)</div>
                    <div class="fs-5 fw-bold text-danger">-{{ number_format($benefice['depenses_locales'], 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="text-muted small">Dont dépenses globales (compagnie)</div>
                    <div class="fs-5 fw-bold text-danger">-{{ number_format($benefice['depenses_globales'], 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>

@endsection
