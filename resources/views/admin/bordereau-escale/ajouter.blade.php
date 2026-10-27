@extends('layouts.admin')

@section('title', "Bordereau d'escale · Sirali Admin")

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-wallet me-1"></i> Caisse</span>
@endsection
@section('breadcrumb-active', 'Nouvelle saisie')

@section('breadcrumb-actions')
    <a href="{{ route('admin.bordereau-escale.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0 text-primary">Saisie d'un bordereau d'escale papier</h5>
                    <small class="text-muted">Reprenez les champs du bordereau imprimé dans le même ordre.</small>
                </div>
                <div class="card-body px-4 pb-4">
                    <form method="post" action="{{ route('admin.bordereau-escale.enregistrer') }}">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date_escale" id="dateEscale" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Bus N°</label>
                                <input type="text" class="form-control" name="numero_bus" placeholder="Ex: 208">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">N° Bordereau</label>
                                <input type="text" class="form-control" name="numero_bordereau" placeholder="Numéro écrit sur le papier">
                            </div>
                            @if ($estAdmin)
                                <div class="col-md-3">
                                    <label class="form-label">Gare de rattachement <span class="text-danger">*</span></label>
                                    <select class="form-select" name="id_agence" required>
                                        <option value="" selected disabled>Choisissez la gare</option>
                                        @foreach ($listeAgences as $ag)
                                            <option value="{{ $ag->idAgence }}">{{ $ag->localite }} - {{ $ag->numeroGare }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">La caisse réelle de cette gare sera créditée.</div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label">Escale</label>
                                <input type="text" class="form-control" name="escale_libelle" placeholder="Ex: Diéma, Nioro, Diboly…">
                                <div class="form-text">Nom de l'arrêt tel qu'écrit sur le papier (peut différer de la gare de rattachement si ce n'est pas une gare enregistrée).</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Chef / agent d'escale</label>
                                <input type="text" class="form-control" name="nom_agent_escale" placeholder="Nom tel qu'écrit sur le papier">
                            </div>
                        </div>

                        <h6 class="text-primary">Billets vendus</h6>
                        <div id="billetsRows">
                            <div class="row g-2 align-items-end mb-2 billet-row">
                                <div class="col-md-5">
                                    <label class="form-label">Destination</label>
                                    <input type="text" class="form-control" name="destination[]" autocomplete="off">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Nb billets</label>
                                    <input type="number" min="0" class="form-control champ-nombre" name="nombre_billets[]" autocomplete="off">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Prix unit. FCFA</label>
                                    <input type="number" min="0" class="form-control champ-prix" name="prix_unitaire[]" autocomplete="off">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Montant FCFA</label>
                                    <input type="text" class="form-control montant-ligne" readonly tabindex="-1">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger remove-row-btn d-none w-100" title="Retirer cette ligne"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addBilletRow" class="btn btn-sm btn-outline-primary mb-2">
                            <i class="fas fa-plus"></i> Ajouter une destination
                        </button>
                        <div class="text-end fw-bold mb-4">Sous-total billets : <span id="sousTotalBillets">0</span> FCFA</div>

                        <h6 class="text-primary">Bagages payants</h6>
                        <div id="bagagesRows">
                            <div class="row g-2 align-items-end mb-2 bagage-row">
                                <div class="col-md-7">
                                    <label class="form-label">Description</label>
                                    <input type="text" class="form-control" name="bagage_description[]" autocomplete="off">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Prix FCFA</label>
                                    <input type="number" min="0" class="form-control champ-prix" name="bagage_prix[]" autocomplete="off">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger remove-bagage-btn d-none w-100" title="Retirer cette ligne"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addBagageRow" class="btn btn-sm btn-outline-primary mb-2">
                            <i class="fas fa-plus"></i> Ajouter un bagage
                        </button>
                        <div class="text-end fw-bold mb-4">Sous-total bagages : <span id="sousTotalBagages">0</span> FCFA</div>

                        <h6 class="text-primary">Récapitulatif</h6>
                        <div class="row g-3 mb-2">
                            <div class="col-md-4">
                                <label class="form-label">Dépenses (FCFA)</label>
                                <input type="number" min="0" class="form-control champ-recap" name="montant_depenses" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Commission agent d'escale (FCFA)</label>
                                <input type="number" min="0" class="form-control champ-recap" name="montant_commission" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total versé <span class="text-danger">*</span></label>
                                <input type="number" min="0" class="form-control champ-recap" name="total_verse" id="totalVerse" required>
                                <div class="form-text">Le montant physiquement remis, tel qu'écrit sur le bordereau.</div>
                            </div>
                        </div>

                        <div class="alert alert-info" id="recapCalcul">
                            Billets + bagages − dépenses − commission = <strong><span id="totalCalcule">0</span> FCFA</strong>
                        </div>

                        <button type="submit" class="btn btn-primary px-4">Enregistrer le bordereau</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        (function initDateMax() {
            document.getElementById('dateEscale').max = new Date().toISOString().slice(0, 10);
        })();

        function formatMontant(n) {
            return isNaN(n) ? '0' : n.toLocaleString('fr-FR');
        }

        function recalculer() {
            let totalBillets = 0;
            document.querySelectorAll('#billetsRows .billet-row').forEach(function (row) {
                const nb = parseFloat(row.querySelector('.champ-nombre').value) || 0;
                const prix = parseFloat(row.querySelector('.champ-prix').value) || 0;
                const montant = nb * prix;
                row.querySelector('.montant-ligne').value = montant ? formatMontant(montant) : '';
                totalBillets += montant;
            });

            let totalBagages = 0;
            document.querySelectorAll('#bagagesRows .bagage-row').forEach(function (row) {
                totalBagages += parseFloat(row.querySelector('.champ-prix').value) || 0;
            });

            const depenses = parseFloat(document.querySelector('[name="montant_depenses"]').value) || 0;
            const commission = parseFloat(document.querySelector('[name="montant_commission"]').value) || 0;

            document.getElementById('sousTotalBillets').textContent = formatMontant(totalBillets);
            document.getElementById('sousTotalBagages').textContent = formatMontant(totalBagages);
            document.getElementById('totalCalcule').textContent = formatMontant(totalBillets + totalBagages - depenses - commission);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const billetsRows = document.getElementById('billetsRows');
            const bagagesRows = document.getElementById('bagagesRows');

            function toggleRemove(container, rowClass, btnClass) {
                const rows = container.querySelectorAll('.' + rowClass);
                rows.forEach(function (row) {
                    row.querySelector('.' + btnClass).classList.toggle('d-none', rows.length <= 1);
                });
            }

            document.getElementById('addBilletRow').addEventListener('click', function () {
                const firstRow = billetsRows.querySelector('.billet-row');
                const newRow = firstRow.cloneNode(true);
                newRow.querySelectorAll('input').forEach(function (input) { input.value = ''; });
                billetsRows.appendChild(newRow);
                toggleRemove(billetsRows, 'billet-row', 'remove-row-btn');
            });

            document.getElementById('addBagageRow').addEventListener('click', function () {
                const firstRow = bagagesRows.querySelector('.bagage-row');
                const newRow = firstRow.cloneNode(true);
                newRow.querySelectorAll('input').forEach(function (input) { input.value = ''; });
                bagagesRows.appendChild(newRow);
                toggleRemove(bagagesRows, 'bagage-row', 'remove-bagage-btn');
            });

            billetsRows.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-row-btn');
                if (btn) { btn.closest('.billet-row').remove(); toggleRemove(billetsRows, 'billet-row', 'remove-row-btn'); recalculer(); }
            });
            bagagesRows.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-bagage-btn');
                if (btn) { btn.closest('.bagage-row').remove(); toggleRemove(bagagesRows, 'bagage-row', 'remove-bagage-btn'); recalculer(); }
            });

            document.querySelector('.card-body').addEventListener('input', function (e) {
                if (e.target.matches('.champ-nombre, .champ-prix, .champ-recap')) {
                    recalculer();
                }
            });

            recalculer();
        });
    </script>
@endsection
