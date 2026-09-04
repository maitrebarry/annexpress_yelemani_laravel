@extends('layouts.admin')

@section('title', 'Rapport Compagnie · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-wallet me-1"></i> Caisse</span>
@endsection
@section('breadcrumb-active', 'Rapport Compagnie')

@section('breadcrumb-actions')
    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Imprimer
    </button>
@endsection

@section('content')


    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('admin.caisse.rapport-proprietaire') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Date</label>
                    <input type="date" class="form-control" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Consulter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, var(--bs-primary), #1e3a8a); color:#fff;">
                <div class="card-body">
                    <div class="small opacity-75">Chiffre d'affaires total — {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</div>
                    <div class="fs-2 fw-bold">{{ number_format($grandTotal, 0, ',', ' ') }} FCFA</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small"><i class="fas fa-ticket text-success me-1"></i>Recettes billets</div>
                    <div class="fs-4 fw-bold">{{ number_format($totalBillets, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small"><i class="fas fa-box-open text-info me-1"></i>Recettes colis</div>
                    <div class="fs-4 fw-bold">{{ number_format($totalColis, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-location-dot me-1"></i> Recettes par escale
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Escale</th>
                            <th>Caisses actives</th>
                            <th class="text-end">Billets</th>
                            <th class="text-end">Colis</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Écarts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rapport as $r)
                            <tr>
                                <td>{{ $r->localite }} <span class="text-muted">({{ $r->numeroGare }})</span></td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $r->nb_caisses }}</span>
                                    <small class="text-muted">{{ $r->caisses_ouvertes }} ouverte(s) / {{ $r->caisses_fermees }} fermée(s)</small>
                                </td>
                                <td class="text-end">{{ number_format($r->total_billets, 0, ',', ' ') }} F</td>
                                <td class="text-end">{{ number_format($r->total_colis, 0, ',', ' ') }} F</td>
                                <td class="text-end fw-bold">{{ number_format($r->grand_total, 0, ',', ' ') }} F</td>
                                <td class="text-end {{ $r->total_ecarts > 0 ? 'text-success' : ($r->total_ecarts < 0 ? 'text-danger' : 'text-muted') }}">
                                    {{ $r->total_ecarts > 0 ? '+' : '' }}{{ number_format($r->total_ecarts, 0, ',', ' ') }} F
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted fst-italic text-center py-3">Aucune donnée pour cette date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($rapport->isNotEmpty())
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2">Total</td>
                                <td class="text-end">{{ number_format($totalBillets, 0, ',', ' ') }} F</td>
                                <td class="text-end">{{ number_format($totalColis, 0, ',', ' ') }} F</td>
                                <td class="text-end">{{ number_format($grandTotal, 0, ',', ' ') }} F</td>
                                <td class="text-end {{ $totalEcarts < 0 ? 'text-danger' : 'text-success' }}">{{ $totalEcarts > 0 ? '+' : '' }}{{ number_format($totalEcarts, 0, ',', ' ') }} F</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

@endsection
