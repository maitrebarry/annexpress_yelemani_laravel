@extends('layouts.admin')

@section('title', "Bordereaux d'escale · Sirali Admin")

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-wallet me-1"></i> Caisse</span>
@endsection
@section('breadcrumb-active', "Bordereaux d'escale")

@section('breadcrumb-actions')
    <a href="{{ route('admin.bordereau-escale.telecharger-modele') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-download me-1"></i> Télécharger le modèle
    </a>
    <a href="{{ route('admin.bordereau-escale.ajouter') }}" class="btn btn-sm btn-success rounded-pill shadow-sm">
        <i class="fas fa-plus me-1"></i> Nouveau bordereau
    </a>
@endsection

@section('content')

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('admin.bordereau-escale.index') }}" class="row g-3 align-items-end">
                @if ($estAdmin)
                    <div class="col-md-5">
                        <label class="form-label mb-1">Gare</label>
                        <select class="form-select" name="id_agence" onchange="this.form.submit()">
                            <option value="" {{ ! $idAgenceSelectionnee ? 'selected' : '' }}>Toutes les gares</option>
                            @foreach ($listeAgences as $a)
                                <option value="{{ $a->idAgence }}" {{ $idAgenceSelectionnee == $a->idAgence ? 'selected' : '' }}>{{ $a->localite.' ('.$a->numeroGare.')' }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-4">
                    <label class="form-label mb-1">Date</label>
                    <input type="date" class="form-control" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-file-lines me-1"></i> Bordereaux d'escale saisis
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Gare</th>
                            <th>Escale</th>
                            <th>N° Bordereau</th>
                            <th>Bus</th>
                            <th class="text-end">Billets + bagages</th>
                            <th class="text-end">Écart</th>
                            <th>Saisi par</th>
                            <th>Caisse</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bordereaux as $b)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($b->date_escale)->format('d/m/Y') }}</td>
                                <td>{{ ($b->localite ?? '-').' ('.($b->numeroGare ?? '-').')' }}</td>
                                <td>{{ $b->escale_libelle ?? '-' }}</td>
                                <td>{{ $b->numero_bordereau ?? '-' }}</td>
                                <td>{{ $b->numero_bus ?? '-' }}</td>
                                <td class="text-end">{{ number_format((float) $b->total_billets + (float) $b->total_bagages, 0, ',', ' ') }} F</td>
                                <td class="text-end {{ (float) ($b->ecart ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ isset($b->ecart) ? number_format((float) $b->ecart, 0, ',', ' ').' F' : '-' }}
                                </td>
                                <td>{{ $b->nom_saisi_par ?? '-' }}</td>
                                <td>
                                    @if (($b->statut_caisse ?? null) === 'versee')
                                        <span class="badge bg-success">Versée</span>
                                    @elseif (($b->statut_caisse ?? null) === 'fermee')
                                        <span class="badge bg-warning text-dark">À verser</span>
                                    @else
                                        <span class="badge bg-secondary">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.bordereau-escale.detail', $b->id_bordereau) }}" class="btn btn-sm btn-outline-primary">Détail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-muted fst-italic text-center py-4">Aucun bordereau saisi pour ces critères.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
