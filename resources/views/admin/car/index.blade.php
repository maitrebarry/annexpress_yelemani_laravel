@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Cars & Camions & Chauffeurs · Sirali Admin')
@section('breadcrumb-title', 'Configuration')
@section('breadcrumb-active', 'Cars & Camions & Chauffeurs')

@section('breadcrumb-actions')
    @unless ($authUser->estLectureSeule())
        <button type="button" class="btn btn-success d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterCar">
            <i class="fas fa-circle-plus fs-5"></i> Ajouter un car
        </button>
        <button type="button" class="btn btn-success d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterCamion">
            <i class="fas fa-circle-plus fs-5"></i> Ajouter un camion
        </button>
        <button type="button" class="btn btn-success d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterChauffeur">
            <i class="fas fa-circle-plus fs-5"></i> Ajouter un chauffeur
        </button>
    @endunless
@endsection

@section('content')
<div class="row">
    @include('admin.partials.config-nav', ['active' => 'cars'])

    <div class="col-12 col-xxl-9">

        <div class="card config-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-bus me-2"></i>Cars & Camions & Chauffeurs</h5>
            </div>
            <div class="card-body p-4">
                <ul class="nav nav-pills nav-pills-primary mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tabCars" role="tab" aria-selected="true">
                            <div class="d-flex align-items-center">
                                <div class="tab-icon"><i class='fas fa-bus font-18 me-1'></i></div>
                                <div class="tab-title">Cars</div>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#tabCamions" role="tab" aria-selected="false">
                            <div class="d-flex align-items-center">
                                <div class="tab-icon"><i class='fas fa-truck font-18 me-1'></i></div>
                                <div class="tab-title">Camions</div>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#tabChauffeurs" role="tab" aria-selected="false">
                            <div class="d-flex align-items-center">
                                <div class="tab-icon"><i class='fas fa-user-tag font-18 me-1'></i></div>
                                <div class="tab-title">Chauffeurs</div>
                            </div>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tabCars" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>Numéro du car</th>
                                        <th>Matricule</th>
                                        <th>Nombre de places</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($listeCar as $c)
                                        <tr>
                                            <td data-label="Numéro du car">{{ $c->numero_car }}</td>
                                            <td data-label="Matricule">{{ $c->matriculle }}</td>
                                            <td data-label="Nombre de places">{{ $c->nbr_place }}</td>
                                            <td data-label="Action">
                                                <div class="dropdown">
                                                    <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown" aria-expanded="false">&#8943;</a>
                                                    <ul class="dropdown-menu shadow-sm">
                                                        @unless ($authUser->estLectureSeule())
                                                            <li>
                                                                <a class="dropdown-item edit-car-btn" href="javascript:;"
                                                                    data-bs-toggle="modal" data-bs-target="#modalModifierCar"
                                                                    data-id="{{ $c->id_car }}"
                                                                    data-numero="{{ $c->numero_car }}"
                                                                    data-matricule="{{ $c->matriculle }}"
                                                                    data-places="{{ $c->nbr_place }}">
                                                                    <i class="fas fa-pen me-2"></i>Modifier
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item text-danger delete-button" href="{{ route('admin.car.destroy', $c->id_car) }}">
                                                                    <i class="fas fa-trash me-2"></i>Supprimer
                                                                </a>
                                                            </li>
                                                        @endunless
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tabCamions" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>Numéro du camion</th>
                                        <th>Matricule</th>
                                        <th>Statut</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($listeCamion as $cam)
                                        <tr>
                                            <td data-label="Numéro du camion">{{ $cam->numero_camion }}</td>
                                            <td data-label="Matricule">{{ $cam->matriculle }}</td>
                                            <td data-label="Statut">
                                                @if (($cam->actif ?? 'on') === 'on')
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactif</span>
                                                @endif
                                            </td>
                                            <td data-label="Action">
                                                <div class="dropdown">
                                                    <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown" aria-expanded="false">&#8943;</a>
                                                    <ul class="dropdown-menu shadow-sm">
                                                        @unless ($authUser->estLectureSeule())
                                                            <li>
                                                                <a class="dropdown-item edit-camion-btn" href="javascript:;"
                                                                    data-bs-toggle="modal" data-bs-target="#modalModifierCamion"
                                                                    data-id="{{ $cam->id_camion }}"
                                                                    data-numero="{{ $cam->numero_camion }}"
                                                                    data-matricule="{{ $cam->matriculle }}"
                                                                    data-actif="{{ $cam->actif ?? 'on' }}">
                                                                    <i class="fas fa-pen me-2"></i>Modifier
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item text-danger delete-button" href="{{ route('admin.camion.destroy', $cam->id_camion) }}">
                                                                    <i class="fas fa-trash me-2"></i>Supprimer
                                                                </a>
                                                            </li>
                                                        @endunless
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tabChauffeurs" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>Photo</th>
                                        <th>Nom & prénom</th>
                                        <th>Téléphone</th>
                                        <th>Véhicule</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($listeChauffeur as $ch)
                                        <tr>
                                            <td data-label="Photo">
                                                @if ($ch->photo)
                                                    <img src="{{ asset('storage/profiles/'.$ch->photo) }}" alt="Photo" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user fs-5"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td data-label="Nom & prénom">{{ $ch->nom_prenom }}</td>
                                            <td data-label="Téléphone">{{ $ch->numero }}</td>
                                            <td data-label="Véhicule">
                                                @if ($ch->type_vehicule === 'camion')
                                                    <span class="badge bg-info-subtle text-info-emphasis"><i class="fas fa-truck me-1"></i>Camion {{ $ch->numero_camion }}</span>
                                                @else
                                                    <span class="badge bg-primary-subtle text-primary-emphasis"><i class="fas fa-bus me-1"></i>Car {{ $ch->numero_car }}</span>
                                                @endif
                                            </td>
                                            <td data-label="Action">
                                                <div class="dropdown">
                                                    <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown" aria-expanded="false">&#8943;</a>
                                                    <ul class="dropdown-menu shadow-sm">
                                                        @unless ($authUser->estLectureSeule())
                                                            <li>
                                                                <a class="dropdown-item edit-chauffeur-btn" href="javascript:;"
                                                                    data-bs-toggle="modal" data-bs-target="#modalModifierChauffeur"
                                                                    data-id="{{ $ch->id_chauffeur }}"
                                                                    data-nom="{{ $ch->nom_prenom }}"
                                                                    data-numero="{{ $ch->numero }}"
                                                                    data-idcar="{{ $ch->id_car }}"
                                                                    data-idcamion="{{ $ch->id_camion }}"
                                                                    data-type="{{ $ch->type_vehicule }}"
                                                                    data-photo="{{ $ch->photo ? asset('storage/profiles/'.$ch->photo) : '' }}">
                                                                    <i class="fas fa-pen me-2"></i>Modifier
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item text-danger delete-button" href="{{ route('admin.chauffeur.destroy', $ch->id_chauffeur) }}">
                                                                    <i class="fas fa-trash me-2"></i>Supprimer
                                                                </a>
                                                            </li>
                                                        @endunless
                                                    </ul>
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
    </div>
</div>
@endsection

@section('modals')
    <!-- Modal Ajout cars ("add to row" : plusieurs lignes ajoutées dynamiquement, comme
         Projets_licence — voir CarController::store()) -->
    <div class="modal fade" id="modalAjouterCar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Ajout de cars</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.car.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div id="carsRows">
                            <div class="row align-items-end mb-2 car-row gx-2">
                                <div class="col-4">
                                    <label class="form-label">Numéro de car</label>
                                    <input type="number" class="form-control" name="numero_car[]" placeholder="Ex: 101">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Matricule</label>
                                    <input type="text" class="form-control" name="matriculle[]" placeholder="Ex: AB-1234">
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Places</label>
                                    <input type="number" class="form-control" name="nbr_place[]" placeholder="Ex: 30">
                                </div>
                                <div class="col-1">
                                    <button type="button" class="btn btn-outline-danger remove-row-btn d-none w-100" title="Retirer cette ligne">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addCarRow" class="btn btn-sm btn-outline-primary mb-3">
                            <i class="fas fa-plus"></i> Ajouter une ligne
                        </button>
                        @if ($authUser->isSuperAdmin())
                            <div class="mb-3">
                                <label class="form-label">Compagnie</label>
                                <select class="form-select" name="id_compagnie" required>
                                    <option value="" disabled selected>Choisissez une compagnie</option>
                                    @foreach ($listeCompagnie as $c)
                                        <option value="{{ $c->id_compagnie }}">{{ $c->nom_compagnie }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary fw-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modification car -->
    <div class="modal fade" id="modalModifierCar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Modifier le véhicule</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.car.update') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id_car" id="edit_id_car">
                        <div class="mb-3">
                            <label class="form-label">Numéro</label>
                            <input type="text" class="form-control" name="numero_car" id="edit_numero_car">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Matricule</label>
                            <input type="text" class="form-control" name="matricule" id="edit_matricule">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre de places</label>
                            <input type="number" class="form-control" name="nbr_place" id="edit_nbr_place">
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

    <!-- Modal Ajout camion -->
    <div class="modal fade" id="modalAjouterCamion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Ajout de camions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.camion.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div id="camionsRows">
                            <div class="row align-items-end mb-2 camion-row gx-2">
                                <div class="col-5">
                                    <label class="form-label">Numéro de camion</label>
                                    <input type="number" class="form-control" name="numero_camion[]" placeholder="Ex: 1">
                                </div>
                                <div class="col-5">
                                    <label class="form-label">Matricule</label>
                                    <input type="text" class="form-control" name="matriculle_camion[]" placeholder="Ex: AB-1234">
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-outline-danger remove-row-btn d-none w-100" title="Retirer cette ligne">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addCamionRow" class="btn btn-sm btn-outline-primary mb-3">
                            <i class="fas fa-plus"></i> Ajouter une ligne
                        </button>
                        @if ($authUser->isSuperAdmin())
                            <div class="mb-3">
                                <label class="form-label">Compagnie</label>
                                <select class="form-select" name="id_compagnie" required>
                                    <option value="" disabled selected>Choisissez une compagnie</option>
                                    @foreach ($listeCompagnie as $c)
                                        <option value="{{ $c->id_compagnie }}">{{ $c->nom_compagnie }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary fw-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modification camion -->
    <div class="modal fade" id="modalModifierCamion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Modifier le camion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.camion.update') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id_camion" id="edit_id_camion">
                        <div class="mb-3">
                            <label class="form-label">Numéro</label>
                            <input type="text" class="form-control" name="numero_camion" id="edit_numero_camion">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Matricule</label>
                            <input type="text" class="form-control" name="matriculle" id="edit_matricule_camion">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <select class="form-select" name="actif" id="edit_actif_camion">
                                <option value="on">Actif</option>
                                <option value="off">Inactif</option>
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

    <!-- Modal Ajout chauffeur -->
    <div class="modal fade" id="modalAjouterChauffeur" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Ajouter un chauffeur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.chauffeur.store') }}" enctype="multipart/form-data">
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
                        <div class="mb-3">
                            <label class="form-label">Nom & prénom</label>
                            <input type="text" class="form-control" name="nom_prenom" value="{{ old('nom_prenom') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="numero" maxlength="8" value="{{ old('numero') }}" placeholder="Ex: 78907812" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="estCamionCheck" name="est_camion" value="1">
                            <label class="form-check-label" for="estCamionCheck">Chauffeur de camion (au lieu d'un car)</label>
                        </div>
                        <div class="mb-3" id="carField">
                            <label class="form-label">Car</label>
                            <select class="form-select" name="id_car" id="selectCar" required>
                                <option value="" disabled selected>Choisissez un car</option>
                                @foreach ($listeCar as $c)
                                    <option value="{{ $c->id_car }}" {{ (string) old('id_car') === (string) $c->id_car ? 'selected' : '' }}>{{ $c->numero_car }} — {{ $c->matriculle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="camionField">
                            <label class="form-label">Camion</label>
                            <select class="form-select" name="id_camion" id="selectCamion">
                                <option value="" disabled selected>Choisissez un camion</option>
                                @foreach ($listeCamion as $cam)
                                    <option value="{{ $cam->id_camion }}" {{ (string) old('id_camion') === (string) $cam->id_camion ? 'selected' : '' }}>{{ $cam->numero_camion }} — {{ $cam->matriculle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
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

    <!-- Modal Modification chauffeur -->
    <div class="modal fade" id="modalModifierChauffeur" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Modifier le chauffeur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.chauffeur.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id_chauffeur" id="edit_id_chauffeur">
                        <div class="mb-3 text-center">
                            <img id="edit_photo_preview" src="" alt="Photo" class="rounded-circle d-none" width="72" height="72" style="object-fit: cover;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nom & prénom</label>
                            <input type="text" class="form-control" name="nom_prenom" id="edit_nom_prenom">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="numero" id="edit_numero_chauffeur" maxlength="8">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="editEstCamionCheck" name="est_camion" value="1">
                            <label class="form-check-label" for="editEstCamionCheck">Chauffeur de camion (au lieu d'un car)</label>
                        </div>
                        <div class="mb-3" id="editCarField">
                            <label class="form-label">Car attribué</label>
                            <select class="form-select" name="id_car" id="edit_id_car_chauffeur">
                                <option value=""></option>
                                @foreach ($listeCar as $c)
                                    <option value="{{ $c->id_car }}">{{ $c->numero_car }} — {{ $c->matriculle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="editCamionField">
                            <label class="form-label">Camion attribué</label>
                            <select class="form-select" name="id_camion" id="edit_id_camion_chauffeur">
                                <option value=""></option>
                                @foreach ($listeCamion as $cam)
                                    <option value="{{ $cam->id_camion }}">{{ $cam->numero_camion }} — {{ $cam->matriculle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Changer la photo</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
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
@endsection

@section('scripts')
    <script src="{{ asset('mon_js/alert_delete.js') }}?v={{ @filemtime(public_path('mon_js/alert_delete.js')) }}"></script>
    <script>
        // "Add to row" : permet de saisir plusieurs cars/camions (numéro/matricule/places)
        // d'un coup, comme Projets_licence — voir CarController::store()/CamionController::store().
        function setupAddToRow(rowsContainerId, addBtnId, rowClass) {
            const rowsContainer = document.getElementById(rowsContainerId);
            const addBtn = document.getElementById(addBtnId);
            if (! rowsContainer || ! addBtn) return;

            function toggleRemoveButtons() {
                const rows = rowsContainer.querySelectorAll('.' + rowClass);
                rows.forEach(function(row) {
                    row.querySelector('.remove-row-btn').classList.toggle('d-none', rows.length <= 1);
                });
            }

            addBtn.addEventListener('click', function() {
                const firstRow = rowsContainer.querySelector('.' + rowClass);
                const newRow = firstRow.cloneNode(true);
                newRow.querySelectorAll('input').forEach(function(input) { input.value = ''; });
                rowsContainer.appendChild(newRow);
                toggleRemoveButtons();
            });

            rowsContainer.addEventListener('click', function(e) {
                const btn = e.target.closest('.remove-row-btn');
                if (btn) {
                    btn.closest('.' + rowClass).remove();
                    toggleRemoveButtons();
                }
            });
        }
        setupAddToRow('carsRows', 'addCarRow', 'car-row');
        setupAddToRow('camionsRows', 'addCamionRow', 'camion-row');

        tgReady(function () {
            document.querySelectorAll('.edit-car-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_id_car').value = this.dataset.id;
                    document.getElementById('edit_numero_car').value = this.dataset.numero;
                    document.getElementById('edit_matricule').value = this.dataset.matricule;
                    document.getElementById('edit_nbr_place').value = this.dataset.places;
                });
            });

            document.querySelectorAll('.edit-camion-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_id_camion').value = this.dataset.id;
                    document.getElementById('edit_numero_camion').value = this.dataset.numero;
                    document.getElementById('edit_matricule_camion').value = this.dataset.matricule;
                    document.getElementById('edit_actif_camion').value = this.dataset.actif;
                });
            });

            // Bascule entre le select Car et le select Camion selon la checkbox "Chauffeur
            // de camion" — même principe pour le modal d'ajout et d'édition (voir
            // GESTION_CAMIONS_COLIS.md).
            function toggleVehiculeFields(checkbox, carField, camionField, carSelect, camionSelect) {
                var estCamion = checkbox.checked;
                carField.classList.toggle('d-none', estCamion);
                camionField.classList.toggle('d-none', !estCamion);
                carSelect.required = !estCamion;
                camionSelect.required = estCamion;
                if (estCamion) {
                    carSelect.value = '';
                } else {
                    camionSelect.value = '';
                }
            }

            var estCamionCheck = document.getElementById('estCamionCheck');
            var carField = document.getElementById('carField');
            var camionField = document.getElementById('camionField');
            var selectCar = document.getElementById('selectCar');
            var selectCamion = document.getElementById('selectCamion');
            estCamionCheck.addEventListener('change', function() {
                toggleVehiculeFields(this, carField, camionField, selectCar, selectCamion);
            });

            var editEstCamionCheck = document.getElementById('editEstCamionCheck');
            var editCarField = document.getElementById('editCarField');
            var editCamionField = document.getElementById('editCamionField');
            var editCar = document.getElementById('edit_id_car_chauffeur');
            var editCamion = document.getElementById('edit_id_camion_chauffeur');
            editEstCamionCheck.addEventListener('change', function() {
                toggleVehiculeFields(this, editCarField, editCamionField, editCar, editCamion);
            });

            document.querySelectorAll('.edit-chauffeur-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_id_chauffeur').value = this.dataset.id;
                    document.getElementById('edit_nom_prenom').value = this.dataset.nom;
                    document.getElementById('edit_numero_chauffeur').value = this.dataset.numero;
                    document.getElementById('edit_id_car_chauffeur').value = this.dataset.idcar || '';
                    document.getElementById('edit_id_camion_chauffeur').value = this.dataset.idcamion || '';

                    var estCamion = this.dataset.type === 'camion';
                    editEstCamionCheck.checked = estCamion;
                    toggleVehiculeFields(editEstCamionCheck, editCarField, editCamionField, editCar, editCamion);
                    // toggleVehiculeFields() vide le select inutilisé : on rétablit la bonne
                    // valeur juste après selon le type réel du chauffeur.
                    if (estCamion) {
                        editCamion.value = this.dataset.idcamion || '';
                    } else {
                        editCar.value = this.dataset.idcar || '';
                    }

                    var preview = document.getElementById('edit_photo_preview');
                    if (this.dataset.photo) {
                        preview.src = this.dataset.photo;
                        preview.classList.remove('d-none');
                    } else {
                        preview.classList.add('d-none');
                    }
                });
            });

            @if ($errors->any())
                new bootstrap.Modal(document.getElementById(@json(old('nom_prenom') !== null ? 'modalAjouterChauffeur' : 'modalAjouterCar'))).show();
            @endif
        });
    </script>
@endsection
