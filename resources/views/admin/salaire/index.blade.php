@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Salaires · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-money-bill-wave me-1"></i> Personnel</span>
@endsection
@section('breadcrumb-active', 'Salaires')

@section('breadcrumb-actions')
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.salaire.liste-bulletins') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
            <i class="fas fa-receipt me-1"></i> Bulletins générés
        </a>
        @if ($peutGerer)
            <button type="button" id="genererSelectionBtn" class="btn btn-sm btn-primary rounded-pill shadow-sm" disabled
                data-bs-toggle="modal" data-bs-target="#genererBulletinsMultiModal">
                <i class="fas fa-receipt me-1"></i> Générer pour la sélection
            </button>
            <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addEmployeModal">
                <i class="fas fa-circle-plus me-1"></i> Ajouter (hors-système)
            </button>
        @endif
    </div>
@endsection

@section('content')

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;">
            <i class="fas fa-money-bill-wave fs-5"></i>
            <span class="fw-semibold">Liste des salaires</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            @if ($peutGerer)
                                <th class="border-0"><input type="checkbox" id="selectAllEmployes"></th>
                            @endif
                            <th class="border-0">Nom & prénom</th>
                            <th class="border-0">Poste</th>
                            <th class="border-0">Gare</th>
                            <th class="border-0">Salaire de base</th>
                            <th class="border-0">Statut</th>
                            <th class="border-0">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($listeEmployes as $employe)
                            @php $horsSysteme = empty($employe->id_utilisateur) && empty($employe->id_chauffeur); @endphp
                            <tr>
                                @if ($peutGerer)
                                    <td><input type="checkbox" class="employe-checkbox" value="{{ $employe->id_employe }}"></td>
                                @endif
                                <td class="fw-semibold">{{ $employe->nom_affiche ?? '' }}</td>
                                <td>{{ $employe->poste }}</td>
                                <td>{{ $employe->localite ?? 'Compagnie entière' }}</td>
                                <td>{{ number_format((float) $employe->salaire_base, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    @if ($employe->statut === 'actif')
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($peutGerer)
                                        <div class="dropdown">
                                            <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-vertical"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <a class="dropdown-item edit-btn" href="javascript:;"
                                                        data-bs-toggle="modal" data-bs-target="#editEmployeModal"
                                                        data-id="{{ $employe->id_employe }}"
                                                        data-poste="{{ $employe->poste }}"
                                                        data-salaire="{{ $employe->salaire_base }}"
                                                        data-agence="{{ $employe->id_agence ?? '' }}"
                                                        data-statut="{{ $employe->statut }}"
                                                        data-hors-systeme="{{ $horsSysteme ? '1' : '0' }}"
                                                        data-nom="{{ $employe->nom_affiche ?? '' }}">
                                                        <i class="fas fa-pen me-2"></i>Modifier
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item generer-btn" href="javascript:;"
                                                        data-bs-toggle="modal" data-bs-target="#genererBulletinModal"
                                                        data-id="{{ $employe->id_employe }}"
                                                        data-nom="{{ $employe->nom_affiche ?? '' }}">
                                                        <i class="fas fa-receipt me-2"></i>Générer un bulletin
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@if ($peutGerer)
    @section('modals')
        <!-- Modal ajout employé hors-système -->
        <div class="modal fade" id="addEmployeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.salaire.store') }}" method="post">
                        @csrf
                        <div class="modal-header bg-primary">
                            <h5 class="modal-title text-white">Ajouter un employé hors-système</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small">Pour le personnel sans compte dans l'application (gardien, balayeur, etc.).</p>
                            @if ($errors->any())
                                <div class="alert alert-danger py-2 px-3 small">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Nom & prénom</label>
                                <input type="text" class="form-control" name="nom_prenom" value="{{ old('nom_prenom') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Poste</label>
                                <input type="text" class="form-control" name="poste" value="{{ old('poste') }}" placeholder="Ex: Gardien, Balayeur..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Salaire de base (FCFA)</label>
                                <input type="number" class="form-control" name="salaire_base" min="0" value="{{ old('salaire_base') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gare de rattachement</label>
                                <select class="form-select" name="id_agence">
                                    <option value="">-- Compagnie entière --</option>
                                    @foreach ($listeAgences as $agence)
                                        <option value="{{ $agence->idAgence }}">{{ $agence->localite }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary fw-semibold">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal édition -->
        <div class="modal fade" id="editEmployeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.salaire.update') }}" method="post">
                        @csrf
                        <div class="modal-header bg-primary">
                            <h5 class="modal-title text-white">Modifier la fiche employé</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_employe" id="edit_id_employe">

                            <div class="mb-3 d-none" id="editNomField">
                                <label class="form-label">Nom & prénom</label>
                                <input type="text" class="form-control" name="nom_prenom" id="edit_nom_employe">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Poste</label>
                                <input type="text" class="form-control" name="poste" id="edit_poste_employe" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Salaire de base (FCFA)</label>
                                <input type="number" class="form-control" name="salaire_base" id="edit_salaire_employe" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gare de rattachement</label>
                                <select class="form-select" name="id_agence" id="edit_agence_employe">
                                    <option value="">-- Compagnie entière --</option>
                                    @foreach ($listeAgences as $agence)
                                        <option value="{{ $agence->idAgence }}">{{ $agence->localite }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Statut</label>
                                <select class="form-select" name="statut" id="edit_statut_employe">
                                    <option value="actif">Actif</option>
                                    <option value="inactif">Inactif</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary fw-semibold">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal génération de bulletin (un seul employé) -->
        <div class="modal fade" id="genererBulletinModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.salaire.generer-bulletin') }}" method="post">
                        @csrf
                        <div class="modal-header bg-primary">
                            <h5 class="modal-title text-white">Générer un bulletin de paie</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_employe" id="generer_id_employe">
                            <p>Employé : <strong id="generer_nom_employe"></strong></p>
                            <div class="mb-3">
                                <label class="form-label">Période</label>
                                <input type="month" class="form-control" name="periode" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary fw-semibold">Générer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal génération groupée : les cases cochées sont injectées en champs cachés
             (ids_employes[]) par JS juste avant l'ouverture. -->
        <div class="modal fade" id="genererBulletinsMultiModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.salaire.generer-bulletin') }}" method="post" id="formGenererMulti">
                        @csrf
                        <div class="modal-header bg-primary">
                            <h5 class="modal-title text-white">Générer les bulletins sélectionnés</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong id="generer_multi_count">0</strong> employé(s) sélectionné(s).</p>
                            <div class="mb-3">
                                <label class="form-label">Période</label>
                                <input type="month" class="form-control" name="periode" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary fw-semibold">Générer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.edit-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        document.getElementById('edit_id_employe').value = this.dataset.id;
                        document.getElementById('edit_poste_employe').value = this.dataset.poste;
                        document.getElementById('edit_salaire_employe').value = this.dataset.salaire;
                        document.getElementById('edit_agence_employe').value = this.dataset.agence || '';
                        document.getElementById('edit_statut_employe').value = this.dataset.statut;

                        // Le nom n'est éditable que pour le personnel hors-système (sinon il
                        // vient du compte utilisateur ou de la fiche chauffeur associée) :
                        // "disabled" (pas juste caché) pour qu'il ne soit pas soumis du tout
                        // sinon (un champ désactivé est exclu du POST par le navigateur).
                        var editNomField = document.getElementById('editNomField');
                        var editNomInput = document.getElementById('edit_nom_employe');
                        if (this.dataset.horsSysteme === '1') {
                            editNomField.classList.remove('d-none');
                            editNomInput.disabled = false;
                            editNomInput.value = this.dataset.nom;
                        } else {
                            editNomField.classList.add('d-none');
                            editNomInput.disabled = true;
                            editNomInput.value = '';
                        }
                    });
                });

                document.querySelectorAll('.generer-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        document.getElementById('generer_id_employe').value = this.dataset.id;
                        document.getElementById('generer_nom_employe').textContent = this.dataset.nom;
                    });
                });

                // Sélection multiple (cases à cocher) pour générer plusieurs bulletins d'un
                // coup, même période pour tout le lot.
                var selectAll = document.getElementById('selectAllEmployes');
                var genererSelectionBtn = document.getElementById('genererSelectionBtn');
                var employeCheckboxes = function() { return document.querySelectorAll('.employe-checkbox'); };

                function majBoutonSelection() {
                    var nbCoches = document.querySelectorAll('.employe-checkbox:checked').length;
                    genererSelectionBtn.disabled = nbCoches === 0;
                }

                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        employeCheckboxes().forEach(function(cb) { cb.checked = selectAll.checked; });
                        majBoutonSelection();
                    });
                }
                employeCheckboxes().forEach(function(cb) {
                    cb.addEventListener('change', majBoutonSelection);
                });

                // Juste avant l'ouverture du modal de génération groupée : injecte un champ
                // caché ids_employes[] par case cochée.
                var modalMulti = document.getElementById('genererBulletinsMultiModal');
                var formMulti = document.getElementById('formGenererMulti');
                modalMulti.addEventListener('show.bs.modal', function() {
                    formMulti.querySelectorAll("input[name='ids_employes[]']").forEach(function(el) { el.remove(); });
                    var coches = document.querySelectorAll('.employe-checkbox:checked');
                    coches.forEach(function(cb) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids_employes[]';
                        input.value = cb.value;
                        formMulti.appendChild(input);
                    });
                    document.getElementById('generer_multi_count').textContent = coches.length;
                });

                @if ($errors->any())
                    new bootstrap.Modal(document.getElementById('addEmployeModal')).show();
                @endif
            });
        </script>
    @endsection
@endif
