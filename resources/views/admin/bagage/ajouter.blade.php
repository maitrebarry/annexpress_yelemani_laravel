@extends('layouts.admin')

@section('title', 'Bagages · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-suitcase me-1"></i> Bagages</span>
@endsection
@section('breadcrumb-active', 'Ajouter des bagages')

@section('breadcrumb-actions')
    <a href="{{ route('admin.billet.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0 text-primary">Billet n°{{ $billet->numeroBillets }}</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <p class="mb-1"><strong>Client :</strong> {{ $billet->Client }}</p>
                    <p class="mb-1"><strong>Trajet :</strong> {{ $billet->departId }} &rarr; {{ $billet->destinationId }}</p>
                    <p class="mb-0"><strong>Voyage :</strong> {{ \Illuminate\Support\Carbon::parse($billet->jourVoyage)->format('d/m/Y') }} à {{ \Illuminate\Support\Carbon::parse($billet->Heur_departs)->format('H:i') }}</p>
                </div>
            </div>

            @if ($bagages->isNotEmpty())
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 pt-3 px-4">
                        <h5 class="mb-0 text-primary">Bagages déjà enregistrés</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <table class="table mb-0">
                            <thead>
                                <tr><th>Description</th><th>Poids (kg)</th><th>Prix (FCFA)</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($bagages as $b)
                                    <tr>
                                        <td>{{ $b->description ?? '-' }}</td>
                                        <td>{{ $b->poids !== null ? $b->poids : '-' }}</td>
                                        <td>{{ number_format((float) $b->prix, 0, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr><th colspan="2">Total bagages</th><th>{{ number_format($totalBagages, 0, ',', ' ') }} FCFA</th></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0 text-primary">Ajouter un ou plusieurs bagages</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <form method="post" action="{{ route('admin.bagage.enregistrer', $billet->idBillets) }}">
                        @csrf
                        <div id="bagagesRows">
                            <div class="row align-items-end mb-2 bagage-row">
                                <div class="col-md-5">
                                    <label class="form-label">Description</label>
                                    <input type="text" class="form-control" placeholder="Ex: 1 valise" name="bagage_description[]" autocomplete="off">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Poids (kg)</label>
                                    <input type="number" step="0.1" min="0" class="form-control" placeholder="Poids" name="bagage_poids[]" autocomplete="off">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                                    <input type="number" min="0" class="form-control" placeholder="Prix" name="bagage_prix[]" autocomplete="off">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger remove-bagage-btn d-none w-100" title="Retirer cette ligne">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addBagageRow" class="btn btn-sm btn-outline-primary mb-3">
                            <i class="fas fa-plus"></i> Ajouter une ligne
                        </button>
                        <div>
                            <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rowsContainer = document.getElementById('bagagesRows');
            const addBtn = document.getElementById('addBagageRow');

            function toggleRemoveButtons() {
                const rows = rowsContainer.querySelectorAll('.bagage-row');
                rows.forEach(function (row) {
                    row.querySelector('.remove-bagage-btn').classList.toggle('d-none', rows.length <= 1);
                });
            }

            addBtn.addEventListener('click', function () {
                const firstRow = rowsContainer.querySelector('.bagage-row');
                const newRow = firstRow.cloneNode(true);
                newRow.querySelectorAll('input').forEach(function (input) { input.value = ''; });
                rowsContainer.appendChild(newRow);
                toggleRemoveButtons();
            });

            rowsContainer.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-bagage-btn');
                if (btn) {
                    btn.closest('.bagage-row').remove();
                    toggleRemoveButtons();
                }
            });
        });
    </script>
@endsection
