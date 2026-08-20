@extends('layouts.admin')

@php
    $authUser = auth('staff')->user();
    $nbActifs = $listeBanques->where('statut', 'active')->count();
    $soldeTotal = $listeBanques->sum('solde');
@endphp

@section('title', 'Comptes banque · TransHub Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="bx bx-buildings me-1"></i> Banque</span>
@endsection
@section('breadcrumb-active', 'Comptes banque')

@section('breadcrumb-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.depot-banque.en-attente') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
            <i class="bx bx-time me-1"></i> Demandes en attente
        </a>
        @if ($authUser->droit === 'Admin')
            <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNouvelleBanque">
                <i class="bx bx-plus me-1"></i> Nouveau compte
            </button>
        @endif
    </div>
@endsection

@section('content')

    @include('admin.partials.set_flash')

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="text-muted small"><i class="bx bx-buildings me-1"></i> Comptes banque</div>
                    <div class="fs-4 fw-bold">{{ $listeBanques->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="text-muted small"><i class="bx bx-check-circle me-1"></i> Comptes actifs</div>
                    <div class="fs-4 fw-bold">{{ $nbActifs }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card bg-primary text-white border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small opacity-75"><i class="bx bx-wallet me-1"></i> Solde cumulé</div>
                    <div class="fs-4 fw-bold">{{ number_format($soldeTotal, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, #0f3b5e, #1d6fa5); color: #fff;">
            <i class="bx bx-list-ul fs-5"></i>
            <span class="fw-semibold">Comptes banque de la compagnie</span>
        </div>
        <div class="table-responsive p-2">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th class="border-0">Nom</th>
                        <th class="border-0">N° de compte</th>
                        <th class="border-0">Solde</th>
                        <th class="border-0">Statut</th>
                        <th class="border-0">Ajouté le</th>
                        <th class="border-0">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($listeBanques as $b)
                        <tr>
                            <td class="fw-semibold"><i class="bx bx-buildings text-primary me-1"></i> {{ $b->nom }}</td>
                            <td>{{ $b->numero_compte ?? '-' }}</td>
                            <td class="fw-bold text-success">{{ number_format($b->solde, 0, ',', ' ') }} F</td>
                            <td>
                                @if ($b->statut === 'active')
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">● Actif</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">● Inactif</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ optional($b->date_creation)->format('d/m/Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item btn-voir-mouvements" href="#" data-bs-toggle="modal" data-bs-target="#modalMouvementsBanque" data-id="{{ $b->id_banque }}">
                                                <i class="bx bx-transfer-alt me-2"></i>Mouvements
                                            </a>
                                        </li>
                                        @if ($authUser->droit === 'Admin')
                                            <li>
                                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalEditBanque"
                                                   data-id="{{ $b->id_banque }}" data-nom="{{ $b->nom }}"
                                                   data-numero="{{ $b->numero_compte }}" data-statut="{{ $b->statut }}">
                                                    <i class="bx bx-edit me-2"></i>Modifier
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun compte banque enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('modals')
    @if ($authUser->droit === 'Admin')
        <div class="modal fade" id="modalNouvelleBanque" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="{{ route('admin.banque.store') }}">
                        @csrf
                        <div class="modal-header bg-success">
                            <h5 class="modal-title text-white">Nouveau compte banque</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nom du compte</label>
                                <input type="text" class="form-control" name="nom" required placeholder="ex: BDM agence Bamako">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">N° de compte (optionnel)</label>
                                <input type="text" class="form-control" name="numero_compte">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEditBanque" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="" id="formEditBanque">
                        @csrf
                        <div class="modal-header bg-primary">
                            <h5 class="modal-title text-white">Modifier le compte banque</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nom du compte</label>
                                <input type="text" class="form-control" name="nom" id="editNom" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">N° de compte (optionnel)</label>
                                <input type="text" class="form-control" name="numero_compte" id="editNumero">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Statut</label>
                                <select class="form-select" name="statut" id="editStatut">
                                    <option value="active">Actif</option>
                                    <option value="inactive">Inactif</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="modalMouvementsBanque" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white mb-0"><i class="bx bx-transfer-alt me-1"></i> Mouvements — <span id="mvtBanqueNom"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="mvtBanqueLoading" class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm me-2"></div> Chargement des mouvements...
                    </div>
                    <div id="mvtBanqueErreur" class="alert alert-danger d-none"></div>

                    <div id="mvtBanqueContenu" class="d-none">
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 rounded text-center" style="background: rgba(13,110,253,.1);">
                                    <div class="text-primary small">Solde actuel</div>
                                    <div class="fw-bold" id="mvtBanqueSolde"></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded text-center" style="background: rgba(25,135,84,.1);">
                                    <div class="text-success small">Entrées</div>
                                    <div class="fw-bold text-success" id="mvtBanqueNbEntrees"></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded text-center">
                                    <div class="text-muted small">N° compte</div>
                                    <div class="fw-bold" id="mvtBanqueNumero"></div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height:360px; overflow-y:auto;">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr class="text-muted small text-uppercase">
                                        <th>Date</th>
                                        <th>Gare</th>
                                        <th class="text-end">Montant</th>
                                        <th>Référence</th>
                                        <th>Demandé par</th>
                                        <th>Validé par</th>
                                    </tr>
                                </thead>
                                <tbody id="mvtBanqueLignes"></tbody>
                            </table>
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
        document.getElementById('modalEditBanque')?.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            document.getElementById('formEditBanque').action = '{{ url('/admin/Banques') }}/' + btn.dataset.id;
            document.getElementById('editNom').value = btn.dataset.nom;
            document.getElementById('editNumero').value = btn.dataset.numero;
            document.getElementById('editStatut').value = btn.dataset.statut;
        });

        function formatMontant(n) { return Number(n).toLocaleString('fr-FR'); }

        function escapeHtml(s) {
            const div = document.createElement('div');
            div.textContent = s ?? '';
            return div.innerHTML;
        }

        document.getElementById('modalMouvementsBanque')?.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const loading = document.getElementById('mvtBanqueLoading');
            const erreur = document.getElementById('mvtBanqueErreur');
            const contenu = document.getElementById('mvtBanqueContenu');

            loading.classList.remove('d-none');
            erreur.classList.add('d-none');
            contenu.classList.add('d-none');

            fetch('{{ url('/admin/Banques/mouvement') }}/' + btn.dataset.id)
                .then(r => r.json())
                .then(data => {
                    loading.classList.add('d-none');
                    if (data.error) {
                        erreur.textContent = data.error;
                        erreur.classList.remove('d-none');
                        return;
                    }

                    document.getElementById('mvtBanqueNom').textContent = data.banque.nom;
                    document.getElementById('mvtBanqueSolde').textContent = formatMontant(data.banque.solde) + ' F';
                    document.getElementById('mvtBanqueNumero').textContent = data.banque.numero_compte || '-';
                    document.getElementById('mvtBanqueNbEntrees').textContent = data.mouvements.length;

                    const tbody = document.getElementById('mvtBanqueLignes');
                    tbody.innerHTML = '';
                    if (! data.mouvements.length) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-muted fst-italic text-center py-3">Aucun mouvement enregistré sur ce compte.</td></tr>';
                    } else {
                        data.mouvements.forEach(m => {
                            tbody.innerHTML += '<tr>'
                                + '<td>' + escapeHtml(m.date || '-') + '</td>'
                                + '<td>' + escapeHtml(m.localite) + ' (' + escapeHtml(m.numeroGare) + ')</td>'
                                + '<td class="text-end fw-bold text-success">+' + formatMontant(m.montant) + ' F</td>'
                                + '<td>' + escapeHtml(m.reference || '-') + '</td>'
                                + '<td>' + escapeHtml(m.demandeur) + '</td>'
                                + '<td>' + escapeHtml(m.validateur || '-') + '</td>'
                                + '</tr>';
                        });
                    }

                    contenu.classList.remove('d-none');
                })
                .catch(() => {
                    loading.classList.add('d-none');
                    erreur.textContent = 'Erreur lors du chargement des mouvements.';
                    erreur.classList.remove('d-none');
                });
        });
    </script>
@endsection
