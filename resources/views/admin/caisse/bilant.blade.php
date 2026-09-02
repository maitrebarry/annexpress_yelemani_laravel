@extends('layouts.admin')

@php
    $isBillets = $type === 'billets';
    $labelActuel = $isBillets ? 'Billets' : 'Colis';
@endphp

@section('title', 'Bilan de caisse · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-wallet me-1"></i> Caisse</span>
@endsection
@section('breadcrumb-active', 'Bilan de caisse')

@section('breadcrumb-actions')
    <div class="btn-group shadow-sm" role="group">
        <a href="{{ route('admin.caisse.bilant-billets') }}" class="btn btn-sm {{ $isBillets ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="fas fa-ticket me-1"></i> Billets
        </a>
        <a href="{{ route('admin.caisse.bilant-colis') }}" class="btn btn-sm {{ ! $isBillets ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="fas fa-box-open me-1"></i> Colis
        </a>
    </div>
@endsection

@section('content')


    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="text-muted small">
            <i class="fas fa-circle-info me-1"></i>
            Cliquez sur une carte pour voir le détail des mouvements de la caisse.
        </div>
        <div class="input-group" style="max-width:280px;">
            <span class="input-group-text bg-white"><i class="fas fa-magnifying-glass"></i></span>
            <input type="text" id="filtreBilan" class="form-control" placeholder="Filtrer par gare, référence...">
        </div>
    </div>

    <div class="row g-3" id="grilleBilan">
        @forelse ($listeCaisse as $c)
            <div class="col-12 col-md-6 col-xl-4 bilan-item" data-search="{{ strtolower($c->localite.' '.$c->numeroGare.' '.$c->reference_caise) }}">
                <div class="card border-0 shadow-sm h-100 bilan-card" role="button" data-bs-toggle="modal" data-bs-target="#modalMouvements"
                    data-id="{{ $c->id_caisse }}" data-type="{{ $type }}"
                    data-localite="{{ $c->localite }}" data-numero-gare="{{ $c->numeroGare }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold">{{ $c->localite }}</div>
                                <div class="text-muted small">Gare {{ $c->numeroGare }} · {{ $c->reference_caise }}</div>
                            </div>
                            @if ((int) $c->status_caisse === 1)
                                <span class="badge bg-success">● Ouverte</span>
                            @else
                                <span class="badge bg-secondary">Fermée</span>
                            @endif
                        </div>
                        <div class="row g-2 text-center mt-2">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <div class="text-muted small">Aujourd'hui</div>
                                    <div class="fw-semibold">{{ number_format($c->total_jour, 0, ',', ' ') }} F</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <div class="text-muted small">Ce mois</div>
                                    <div class="fw-semibold">{{ number_format($c->total_mois, 0, ',', ' ') }} F</div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <span class="text-muted small">État actuel ({{ $labelActuel }})</span>
                            <span class="fw-bold text-primary">{{ number_format($isBillets ? $c->montant_billets : $c->montant_colis, 0, ',', ' ') }} F</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <span class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-eye me-1"></i> Voir les mouvements</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-wallet fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">Aucune caisse de gare pour le moment.</p>
                    </div>
                </div>
            </div>
        @endforelse
        <div class="col-12 d-none" id="aucunResultatFiltre">
            <div class="text-center text-muted py-4 fst-italic">Aucune caisse ne correspond à votre recherche.</div>
        </div>
    </div>

@endsection

@section('modals')
    <div class="modal fade" id="modalMouvements" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white mb-0">
                        <i class="fas fa-list-ul me-1"></i> Mouvements — <span id="mvtGareNom"></span>
                        <div class="small opacity-75" id="mvtPeriodeLabel"></div>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="btn-group mb-3 w-100" role="group" id="mvtPeriodeButtons">
                        <button type="button" class="btn btn-outline-primary active" data-periode="jour">Aujourd'hui</button>
                        <button type="button" class="btn btn-outline-primary" data-periode="mois">Ce mois</button>
                        <button type="button" class="btn btn-outline-primary" data-periode="tout">Tout l'historique</button>
                    </div>

                    <div id="mvtLoading" class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm me-2"></div> Chargement des mouvements...
                    </div>
                    <div id="mvtErreur" class="alert alert-danger d-none"></div>

                    <div id="mvtContenu" class="d-none">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 rounded text-center" style="background: rgba(25,135,84,.1);">
                                    <div class="text-success small">Total entrées</div>
                                    <div class="fw-bold text-success" id="mvtTotalEntrees"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded text-center" style="background: rgba(220,53,69,.1);">
                                    <div class="text-danger small">Total sorties</div>
                                    <div class="fw-bold text-danger" id="mvtTotalSorties"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="text-success"><i class="fas fa-right-to-bracket me-1"></i> Entrées</h6>
                                <div class="table-responsive" style="max-height:320px; overflow-y:auto;">
                                    <table class="table table-sm">
                                        <tbody id="mvtEntrees"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-danger"><i class="fas fa-right-from-bracket me-1"></i> Sorties</h6>
                                <div class="table-responsive" style="max-height:320px; overflow-y:auto;">
                                    <table class="table table-sm">
                                        <tbody id="mvtSorties"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Filtre client-side, instantané, sans rechargement.
            var filtre = document.getElementById('filtreBilan');
            var items = document.querySelectorAll('.bilan-item');
            var aucunResultat = document.getElementById('aucunResultatFiltre');
            if (filtre) {
                filtre.addEventListener('input', function () {
                    var q = filtre.value.trim().toLowerCase();
                    var visibles = 0;
                    items.forEach(function (item) {
                        var match = item.dataset.search.includes(q);
                        item.classList.toggle('d-none', ! match);
                        if (match) visibles++;
                    });
                    aucunResultat.classList.toggle('d-none', visibles > 0 || items.length === 0);
                });
            }

            // Modale mouvements : chargement AJAX + bascule de période.
            var idCaisseCourante = null;
            var typeCourant = 'tout';
            var periodeCourante = 'jour';

            function chargerMouvements() {
                var loading = document.getElementById('mvtLoading');
                var erreur = document.getElementById('mvtErreur');
                var contenu = document.getElementById('mvtContenu');

                loading.classList.remove('d-none');
                erreur.classList.add('d-none');
                contenu.classList.add('d-none');

                fetch('{{ url('/admin/Caisse/mouvements') }}/' + idCaisseCourante + '?periode=' + periodeCourante + '&type=' + typeCourant)
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        loading.classList.add('d-none');
                        if (data.error) {
                            erreur.textContent = data.error;
                            erreur.classList.remove('d-none');
                            return;
                        }

                        document.getElementById('mvtPeriodeLabel').textContent = 'du ' + formatDate(data.caisse.debut) + ' au ' + formatDate(data.caisse.fin);
                        document.getElementById('mvtTotalEntrees').textContent = formatMontant(data.total_entrees) + ' FCFA';
                        document.getElementById('mvtTotalSorties').textContent = formatMontant(data.total_sorties) + ' FCFA';

                        remplirTable('mvtEntrees', data.entrees, 'Aucune entrée.');
                        remplirTable('mvtSorties', data.sorties, 'Aucune sortie.');

                        contenu.classList.remove('d-none');
                    })
                    .catch(function () {
                        loading.classList.add('d-none');
                        erreur.textContent = 'Erreur lors du chargement des mouvements.';
                        erreur.classList.remove('d-none');
                    });
            }

            function remplirTable(id, lignes, texteVide) {
                var tbody = document.getElementById(id);
                tbody.innerHTML = '';
                if (! lignes.length) {
                    tbody.innerHTML = '<tr><td class="text-muted fst-italic text-center py-3">' + texteVide + '</td></tr>';
                    return;
                }
                var icones = { Billet: 'bx-ticket', Colis: 'bx-package', Versement: 'bx-send', 'Dépense': 'bx-money', 'Remboursement': 'bx-undo' };
                lignes.forEach(function (l) {
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td><i class="bx ' + (icones[l.type] || 'bx-circle') + ' me-1 text-muted"></i>' + l.type + '<br>' +
                        '<small class="text-muted">' + (l.reference || '') + (l.agent ? ' · ' + l.agent : '') + '</small></td>' +
                        '<td class="text-end fw-semibold">' + formatMontant(l.montant) + ' F</td>';
                    tbody.appendChild(tr);
                });
            }

            function formatMontant(n) {
                return Number(n || 0).toLocaleString('fr-FR');
            }
            function formatDate(d) {
                if (! d) return '?';
                var parts = d.split('-');
                return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : d;
            }

            document.querySelectorAll('.bilan-card').forEach(function (card) {
                card.addEventListener('click', function () {
                    idCaisseCourante = card.dataset.id;
                    typeCourant = card.dataset.type;
                    periodeCourante = 'jour';
                    document.getElementById('mvtGareNom').textContent = card.dataset.localite + ' (Gare ' + card.dataset.numeroGare + ')';
                    document.querySelectorAll('#mvtPeriodeButtons button').forEach(function (b) {
                        b.classList.toggle('active', b.dataset.periode === 'jour');
                    });
                    chargerMouvements();
                });
            });

            document.querySelectorAll('#mvtPeriodeButtons button').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    periodeCourante = btn.dataset.periode;
                    document.querySelectorAll('#mvtPeriodeButtons button').forEach(function (b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                    chargerMouvements();
                });
            });
        });
    </script>
@endsection
