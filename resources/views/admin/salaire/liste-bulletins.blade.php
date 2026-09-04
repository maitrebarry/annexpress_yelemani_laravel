@extends('layouts.admin')

@section('title', 'Bulletins de paie · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-receipt me-1"></i> Personnel</span>
@endsection
@section('breadcrumb-active', 'Bulletins générés')

@section('breadcrumb-actions')
    <a href="{{ route('admin.salaire.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour aux salaires
    </a>
@endsection

@section('content')

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;">
            <i class="fas fa-receipt fs-5"></i>
            <span class="fw-semibold">Bulletins de paie générés</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th class="border-0">Employé</th>
                            <th class="border-0">Poste</th>
                            <th class="border-0">Période</th>
                            <th class="border-0 text-end">Montant</th>
                            <th class="border-0">Généré le</th>
                            <th class="border-0">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($listeBulletins as $bulletin)
                            <tr>
                                <td class="fw-semibold">{{ $bulletin->nom_affiche ?? '' }}</td>
                                <td>{{ $bulletin->poste ?? '' }}</td>
                                <td>{{ $bulletin->periode }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format((float) $bulletin->salaire_verse, 0, ',', ' ') }} F</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($bulletin->date_generation)->format('d/m/Y à H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.salaire.telecharger-bulletin', $bulletin->id_bulletin) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download me-1"></i> Télécharger
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted text-center py-4">Aucun bulletin généré pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
