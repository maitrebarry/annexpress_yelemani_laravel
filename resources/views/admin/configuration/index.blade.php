@extends('layouts.admin')

@php
    $authUser = auth('staff')->user();
    $labelDroit = fn (string $droit) => match ($droit) {
        'chef_d_escale' => "Chef d'escale",
        'PDG' => 'PDG (superviseur, lecture seule)',
        'secretaire' => 'Secrétaire Général',
        default => $droit,
    };
@endphp

@section('title', 'Utilisateurs · Sirali Admin')
@section('breadcrumb-title', 'Configuration')
@section('breadcrumb-active', 'Utilisateur')

@section('breadcrumb-actions')
    <button type="button" class="btn btn-success d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterUtilisateur">
        <i class="fas fa-circle-plus fs-5"></i> Ajouter
    </button>
@endsection

@section('content')
<div class="row">
    @include('admin.partials.config-nav', ['active' => 'utilisateur'])

    <div class="col-12 col-xxl-9">

        <div class="card config-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i>Liste des utilisateurs</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Photo</th>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Gare</th>
                                <th>Droit</th>
                                <th>Service</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($liste as $u)
                                <tr>
                                    <td data-label="Photo">
                                        @if ($u->photo)
                                            <img src="{{ asset('storage/profiles/'.$u->photo) }}" alt="Photo" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user fs-5"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td data-label="Utilisateur">{{ $u->utilisateurs }}</td>
                                    <td data-label="Email">{{ $u->emailUser }}</td>
                                    <td data-label="Téléphone">{{ $u->telephone }}</td>
                                    <td data-label="Gare">{{ $u->numeroGare }}</td>
                                    <td data-label="Droit">{{ $u->droit }}</td>
                                    <td data-label="Service">
                                        @if ($u->droit === 'Utilisateur' && $u->profile)
                                            {{ $u->profile === 'billet' ? 'Billetterie' : ($u->profile === 'colis' ? 'Colis / Courrier' : $u->profile) }}
                                        @else
                                            <span class="text-muted">&mdash;</span>
                                        @endif
                                    </td>
                                    <td data-label="Statut">
                                        @if ($u->status == 1)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td data-label="Action">
                                        <div class="dropup text-center">
                                            <a href="#" class="text-dark text-decoration-none fs-4" data-bs-toggle="dropdown" aria-expanded="false">&#8943;</a>
                                            <div class="dropdown-menu">
                                                @unless ($authUser->estLectureSeule())
                                                    <a class="dropdown-item" href="{{ route('admin.permission.assigner', $u->idUser) }}">
                                                        <i class="fas fa-lock-open me-2"></i>Permissions
                                                    </a>
                                                    <a class="dropdown-item edit-utilisateur-btn" href="javascript:;"
                                                        data-bs-toggle="modal" data-bs-target="#modalModification"
                                                        data-id="{{ $u->idUser }}"
                                                        data-utilisateurs="{{ $u->utilisateurs }}"
                                                        data-email="{{ $u->emailUser }}"
                                                        data-telephone="{{ $u->telephone }}"
                                                        data-droit="{{ $u->droit }}"
                                                        data-profile="{{ $u->profile }}"
                                                        data-id_agence="{{ $u->id_agence }}"
                                                        data-photo="{{ $u->photo ? asset('storage/profiles/'.$u->photo) : '' }}">
                                                        <i class="fas fa-pen me-2"></i>Modifier
                                                    </a>
                                                    <a class="dropdown-item" href="javascript:;"
                                                        data-bs-toggle="modal" data-bs-target="#modalStatut{{ $u->idUser }}">
                                                        <i class="bx {{ $u->status == 1 ? 'bx-user-x' : 'bx-user-check' }} me-2"></i>
                                                        {{ $u->status == 1 ? 'Désactiver' : 'Activer' }}
                                                    </a>
                                                @endunless
                                                @if ($authUser->isSuperAdmin())
                                                    <a class="dropdown-item text-danger" href="javascript:;"
                                                        data-bs-toggle="modal" data-bs-target="#modalSuppression{{ $u->idUser }}">
                                                        <i class="fas fa-trash me-2"></i>Supprimer
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
    <!-- Modal Ajout utilisateur -->
    <div class="modal fade" id="modalAjouterUtilisateur" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Ajouter un utilisateur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.configuration.store') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger py-2 px-3 small">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom &amp; prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="utilisateurs" value="{{ old('utilisateurs') }}" required autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="emailUser" value="{{ old('emailUser') }}" required autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <input type="tel" class="form-control" name="telephone" value="{{ old('telephone') }}" pattern="[0-9+\s.\-]{6,20}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Droit <span class="text-danger">*</span></label>
                                <select class="form-select" name="droit" id="add_droit" required>
                                    <option value="" disabled selected>Choisissez un droit</option>
                                    @foreach ($droitsAutorises as $droit)
                                        <option value="{{ $droit }}" {{ old('droit') === $droit ? 'selected' : '' }}>
                                            {{ $labelDroit($droit) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" id="add_gareField">
                                <label class="form-label fw-semibold">Gare <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_agence">
                                    <option value="" disabled selected>Choisissez une gare</option>
                                    @foreach ($listeGares as $gare)
                                        <option value="{{ $gare->idAgence }}" {{ (string) old('id_agence') === (string) $gare->idAgence ? 'selected' : '' }}>
                                            {{ $gare->localite }} — {{ $gare->numeroGare }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($authUser->isSuperAdmin())
                                <div class="col-md-6 d-none" id="add_compagnieField">
                                    <label class="form-label fw-semibold">Compagnie</label>
                                    <select class="form-select" name="id_compagnie">
                                        <option value="" disabled selected>Choisissez une compagnie</option>
                                        @foreach ($listeCompagnie as $compagnie)
                                            <option value="{{ $compagnie->id_compagnie }}" {{ (string) old('id_compagnie') === (string) $compagnie->id_compagnie ? 'selected' : '' }}>
                                                {{ $compagnie->nom_compagnie }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-6 d-none" id="add_serviceField">
                                <label class="form-label fw-semibold">Service</label>
                                <select class="form-select" name="profile">
                                    <option value="" disabled selected>Choisissez un service</option>
                                    <option value="billet" {{ old('profile') === 'billet' ? 'selected' : '' }}>Billetterie</option>
                                    <option value="colis" {{ old('profile') === 'colis' ? 'selected' : '' }}>Colis / Courrier</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Photo</label>
                                <input type="file" class="form-control" name="photo" accept="image/*">
                            </div>
                        </div>
                        <p class="text-muted small mt-3 mb-0">
                            Un mot de passe par défaut (<strong>123456</strong>) sera attribué au compte ; l'utilisateur pourra le modifier après sa première connexion.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-floppy-disk fs-5 me-2"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modification utilisateur -->
    <div class="modal fade" id="modalModification" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Modifier l'utilisateur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.configuration.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="idUser" id="edit_idUser">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom &amp; prénom</label>
                                <input type="text" class="form-control" id="edit_utilisateurs" name="utilisateurs" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" id="edit_emailUser" name="emailUser" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <input type="tel" class="form-control" id="edit_telephone" name="telephone" pattern="[0-9+\s.\-]{6,20}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Droit</label>
                                <select class="form-select" id="edit_droit" name="droit" required>
                                    @foreach ($droitsAutorises as $droit)
                                        <option value="{{ $droit }}">
                                            {{ $labelDroit($droit) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 d-none" id="edit_serviceField">
                                <label class="form-label fw-semibold">Service</label>
                                <select class="form-select" id="edit_profile" name="profile">
                                    <option value="" disabled selected>Choisissez un service</option>
                                    <option value="billet">Billetterie</option>
                                    <option value="colis">Colis / Courrier</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="edit_gareField">
                                <label class="form-label fw-semibold">Gare <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_id_agence" name="id_agence">
                                    <option value="">Choisissez une gare</option>
                                    @foreach ($listeGares as $gare)
                                        <option value="{{ $gare->idAgence }}">{{ $gare->localite }} — {{ $gare->numeroGare }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="edit_motPasse" name="motPasse" placeholder="Laisser vide pour ne pas modifier" minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Photo</label>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <img id="edit_photo_preview" src="" alt="Photo actuelle" class="rounded-circle d-none" width="40" height="40" style="object-fit: cover;">
                                </div>
                                <input type="file" class="form-control" id="edit_photo" name="photo" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-floppy-disk fs-5 me-2"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals par utilisateur (statut, suppression) : rendues ici, hors du tableau, sinon
         un modal Bootstrap (position: fixed) imbriqué dans .table-responsive (overflow-x: auto)
         se retrouve piégé/mal positionné dans la card au lieu de s'afficher par-dessus la page. -->
    @foreach ($liste as $u)
        <!-- Modal Activation/Désactivation -->
        <div class="modal fade" id="modalStatut{{ $u->idUser }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="{{ route('admin.configuration.status') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold text-primary">
                                Confirmation de {{ $u->status == 1 ? 'désactivation' : 'réactivation' }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <i class="fas fa-circle-exclamation text-danger" style="font-size: 60px;"></i>
                            <p class="mt-3">
                                Voulez-vous vraiment
                                <strong class="text-danger">{{ $u->status == 1 ? 'désactiver' : 'activer' }}</strong>
                                le compte <br><strong>{{ $u->utilisateurs }}</strong> ?
                            </p>
                            <input type="hidden" name="idUser" value="{{ $u->idUser }}">
                            <input type="hidden" name="newStatut" value="{{ $u->status == 1 ? 0 : 1 }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Oui, {{ $u->status == 1 ? 'désactiver' : 'activer' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($authUser->isSuperAdmin())
            <!-- Modal Suppression définitive -->
            <div class="modal fade" id="modalSuppression{{ $u->idUser }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="post" action="{{ route('admin.configuration.destroy') }}">
                            @csrf
                            <div class="modal-header bg-danger">
                                <h5 class="modal-title text-white">Supprimer définitivement ce compte</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>
                                    Cette action est <strong class="text-danger">irréversible</strong>. Le compte
                                    <strong>{{ $u->utilisateurs }}</strong> sera supprimé ainsi que ses données
                                    propres (permissions, historique de connexion). Ses billets/colis/dépenses déjà
                                    enregistrés sont conservés mais détachés de son compte.
                                </p>
                                <p class="mb-1">Pour confirmer, saisissez l'email exact de ce compte :</p>
                                <p class="fw-bold mb-2">{{ $u->emailUser }}</p>
                                <input type="text" class="form-control confirm-delete-input" name="confirmation"
                                    data-expected="{{ $u->emailUser }}" autocomplete="off" placeholder="Saisir l'email pour confirmer">
                                <input type="hidden" name="idUser" value="{{ $u->idUser }}">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-danger delete-submit-btn" disabled>Supprimer définitivement</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection

@section('scripts')
    <script>
        tgReady(function () {
            // --- Modal Ajout : afficher/masquer gare, compagnie, service selon le droit choisi
            var addDroit = document.getElementById('add_droit');
            var addGareField = document.getElementById('add_gareField');
            var addCompagnieField = document.getElementById('add_compagnieField');
            var addServiceField = document.getElementById('add_serviceField');

            function toggleAddFields() {
                var estAdminOuPdg = ['Admin', 'PDG', 'secretaire'].includes(addDroit.value);
                addGareField.classList.toggle('d-none', estAdminOuPdg);
                addGareField.querySelector('select').required = !estAdminOuPdg;
                if (addCompagnieField) {
                    addCompagnieField.classList.toggle('d-none', !estAdminOuPdg);
                }
                addServiceField.classList.toggle('d-none', addDroit.value !== 'Utilisateur');
            }
            if (addDroit) {
                addDroit.addEventListener('change', toggleAddFields);
                toggleAddFields();
            }

            // --- Modal Modification : préremplissage + toggle des champs service/gare
            var editDroit = document.getElementById('edit_droit');
            var editServiceField = document.getElementById('edit_serviceField');
            var editProfile = document.getElementById('edit_profile');
            var editGareField = document.getElementById('edit_gareField');
            var editIdAgence = document.getElementById('edit_id_agence');

            function toggleEditService() {
                editServiceField.classList.toggle('d-none', editDroit.value !== 'Utilisateur');
                // Même règle que pour la création : Admin/PDG/secrétaire général sont
                // rattachés à la compagnie entière, pas à une gare précise.
                var estAdminOuPdg = ['Admin', 'PDG', 'secretaire'].includes(editDroit.value);
                editGareField.classList.toggle('d-none', estAdminOuPdg);
                editIdAgence.required = !estAdminOuPdg;
            }
            if (editDroit) {
                editDroit.addEventListener('change', toggleEditService);
            }

            document.querySelectorAll('.edit-utilisateur-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_idUser').value = this.dataset.id;
                    document.getElementById('edit_utilisateurs').value = this.dataset.utilisateurs;
                    document.getElementById('edit_emailUser').value = this.dataset.email;
                    document.getElementById('edit_telephone').value = this.dataset.telephone || '';
                    editDroit.value = this.dataset.droit;
                    document.getElementById('edit_motPasse').value = '';
                    editIdAgence.value = this.dataset.id_agence || '';
                    toggleEditService();
                    if (this.dataset.profile) {
                        editProfile.value = this.dataset.profile;
                    }
                    document.getElementById('edit_photo').value = '';
                    var preview = document.getElementById('edit_photo_preview');
                    if (this.dataset.photo) {
                        preview.src = this.dataset.photo;
                        preview.classList.remove('d-none');
                    } else {
                        preview.src = '';
                        preview.classList.add('d-none');
                    }
                });
            });
            if (editDroit) {
                editDroit.addEventListener('change', toggleEditService);
            }

            // --- Suppression : le bouton ne s'active que si l'email saisi correspond exactement
            document.querySelectorAll('.confirm-delete-input').forEach(function(input) {
                input.addEventListener('input', function() {
                    var form = input.closest('form');
                    var btn = form.querySelector('.delete-submit-btn');
                    btn.disabled = input.value !== input.dataset.expected;
                });
            });

            @if ($errors->any())
                var modalAjout = new bootstrap.Modal(document.getElementById('modalAjouterUtilisateur'));
                modalAjout.show();
            @endif
        });
    </script>
@endsection
