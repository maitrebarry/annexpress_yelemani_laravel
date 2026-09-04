@extends('layouts.admin')

@section('title', 'Compagnies · Sirali Admin')
@section('breadcrumb-title', 'Configuration')
@section('breadcrumb-active', 'Compagnies')

@section('breadcrumb-actions')
    <button type="button" class="btn btn-success d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterCompagnie">
        <i class="fas fa-circle-plus fs-5"></i> Ajouter
    </button>
@endsection

@section('content')
<div class="row">
    @include('admin.partials.config-nav', ['active' => 'compagnie'])

    <div class="col-12 col-xxl-9">

        <div class="card config-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-building-columns me-2"></i>Liste des compagnies</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Logo</th>
                                <th>Nom compagnie</th>
                                <th>Libellé</th>
                                <th>Slogan</th>
                                <th>Photos</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($liste as $c)
                                <tr>
                                    <td data-label="Logo">
                                        @if ($c->logo)
                                            <img src="{{ asset('images/logos/'.$c->logo) }}" alt="Logo" style="width:45px;height:45px;object-fit:contain;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,.1);background:#fff;padding:2px;">
                                        @else
                                            <div style="width:45px;height:45px;background:rgba(245,158,11,.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#ea580c;margin:0 auto;">
                                                <i class="fas fa-bus fs-4"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="fw-semibold" data-label="Nom compagnie">{{ $c->nom_compagnie }}</td>
                                    <td data-label="Libellé">{{ $c->libele }}</td>
                                    <td data-label="Slogan">{{ $c->slogant }}</td>
                                    <td data-label="Photos">
                                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalPhotos{{ $c->id_compagnie }}">
                                            <i class="fas fa-images"></i> {{ $c->photos->count() }}
                                        </button>
                                    </td>
                                    <td data-label="Action">
                                        <div class="dropdown">
                                            <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown" aria-expanded="false">&#8943;</a>
                                            <ul class="dropdown-menu shadow-sm">
                                                <li>
                                                    <a class="dropdown-item edit-btn" href="#"
                                                        data-bs-toggle="modal" data-bs-target="#exampleDangerModal1"
                                                        data-id_compagnie="{{ $c->id_compagnie }}"
                                                        data-nom_compagnie="{{ $c->nom_compagnie }}"
                                                        data-libele="{{ $c->libele }}"
                                                        data-slogant="{{ $c->slogant }}"
                                                        data-logo="{{ $c->logo ? asset('images/logos/'.$c->logo) : '' }}"
                                                        data-logofilename="{{ $c->logo }}"
                                                        data-telephone="{{ $c->telephone }}"
                                                        data-email="{{ $c->email }}"
                                                        data-whatsapp="{{ $c->whatsapp }}"
                                                        data-adresse="{{ $c->adresse }}"
                                                        data-facebook="{{ $c->facebook }}"
                                                        data-instagram="{{ $c->instagram }}">
                                                        <i class="fas fa-pen me-2"></i>Modifier
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger delete-button" href="{{ route('admin.compagnie.destroy', $c->id_compagnie) }}">
                                                        <i class="fas fa-trash me-2"></i>Supprimer
                                                    </a>
                                                </li>
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
@endsection

@section('modals')
    <!-- Modal Ajout compagnie -->
    <div class="modal fade" id="modalAjouterCompagnie" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Enregistrement d'une compagnie</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.compagnie.store') }}" enctype="multipart/form-data">
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
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nom de la compagnie <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nom_compagnie" value="{{ old('nom_compagnie') }}" placeholder="Nom de la compagnie" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="libele" value="{{ old('libele') }}" placeholder="Libellé" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Slogan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="slogant" value="{{ old('slogant') }}" placeholder="Slogan" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Logo</label>
                                <input type="file" class="form-control" name="logo" accept="image/png, image/jpeg, image/webp">
                            </div>
                        </div>

                        <hr class="my-3">

                        <p class="text-muted small mb-2"><i class="fas fa-address-card me-1"></i> Coordonnées affichées sur le site public (toutes facultatives)</p>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" class="form-control" name="telephone" value="{{ old('telephone') }}" placeholder="Ex : 77 41 37 57">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="contact@compagnie.com">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" class="form-control" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="Ex : 77 41 37 57">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Adresse</label>
                                <input type="text" class="form-control" name="adresse" value="{{ old('adresse') }}" placeholder="Ville, quartier...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Facebook</label>
                                <input type="url" class="form-control" name="facebook" value="{{ old('facebook') }}" placeholder="https://facebook.com/...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Instagram</label>
                                <input type="url" class="form-control" name="instagram" value="{{ old('instagram') }}" placeholder="https://instagram.com/...">
                            </div>
                        </div>

                        <hr class="my-3">

                        <label class="form-label d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-images me-1"></i> Photos des cars (carrousel connexion &amp; site public)</span>
                            <button type="button" class="btn btn-sm btn-outline-primary tg-add-photo-row" data-target="photoRowsCreate">
                                <i class="fas fa-plus"></i> Ajouter une photo
                            </button>
                        </label>
                        <div id="photoRowsCreate" class="tg-photo-rows"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary fw-semibold" name="save">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modification compagnie -->
    <div class="modal fade" id="exampleDangerModal1" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Modification de la compagnie</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.compagnie.update') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Nom de la compagnie</label>
                                <input type="text" class="form-control" name="nom_compagnie" id="inputnomCompagnie" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Libellé</label>
                                <input type="text" class="form-control" name="libele" id="inputlibele" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Slogan</label>
                                <input type="text" class="form-control" name="slogant" id="inputslogant" required>
                            </div>
                            <div class="col-md-6">
                                <img id="logoPreview" src="" alt="Logo" width="120" class="mb-2 border" onerror="this.style.display='none'">
                                <input type="hidden" name="ancien_logo" id="ancienLogo">
                                <label class="form-label mt-2">Changer le logo</label>
                                <input type="file" class="form-control" name="logo" accept="image/png, image/jpeg, image/webp">
                            </div>
                        </div>

                        <hr class="my-3">

                        <p class="text-muted small mb-2"><i class="fas fa-address-card me-1"></i> Coordonnées affichées sur le site public (toutes facultatives)</p>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" class="form-control" name="telephone" id="inputtelephone" placeholder="Ex : 77 41 37 57">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="inputemail" placeholder="contact@compagnie.com">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" class="form-control" name="whatsapp" id="inputwhatsapp" placeholder="Ex : 77 41 37 57">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Adresse</label>
                                <input type="text" class="form-control" name="adresse" id="inputadresse" placeholder="Ville, quartier...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Facebook</label>
                                <input type="url" class="form-control" name="facebook" id="inputfacebook" placeholder="https://facebook.com/...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Instagram</label>
                                <input type="url" class="form-control" name="instagram" id="inputinstagram" placeholder="https://instagram.com/...">
                            </div>
                        </div>

                        <input type="hidden" name="id_compagnie" id="inputidCompagnie">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" name="edit">Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals Gestion des photos (un par compagnie) -->
    @foreach ($liste as $c)
        <div class="modal fade" id="modalPhotos{{ $c->id_compagnie }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white"><i class="fas fa-images me-2"></i>Photos — {{ $c->nom_compagnie }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Ces photos de cars alimentent le carrousel de la page de connexion et de la
                            vitrine publique de cette compagnie.
                        </p>

                        @if ($c->photos->isNotEmpty())
                            <div class="row g-2 mb-4">
                                @foreach ($c->photos as $photo)
                                    <div class="col-4 col-md-3">
                                        <div class="position-relative">
                                            <img src="{{ asset('images/compagnies_photos/'.$photo->chemin) }}" class="img-fluid rounded border" style="aspect-ratio:4/3;object-fit:cover;width:100%;">
                                            <a href="{{ route('admin.compagnie.photos.destroy', $photo->id) }}"
                                               class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 delete-button"
                                               style="padding:.15rem .4rem;line-height:1;"
                                               title="Supprimer cette photo">
                                                <i class="fas fa-xmark"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-3 mb-3 border rounded">
                                <i class="fas fa-images fs-3 d-block mb-2"></i>
                                Aucune photo pour le moment.
                            </div>
                        @endif

                        <form action="{{ route('admin.compagnie.photos.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id_compagnie" value="{{ $c->id_compagnie }}">

                            <label class="form-label d-flex align-items-center justify-content-between">
                                <span>Ajouter des photos</span>
                                <button type="button" class="btn btn-sm btn-outline-primary tg-add-photo-row" data-target="photoRows{{ $c->id_compagnie }}">
                                    <i class="fas fa-plus"></i> Ajouter une photo
                                </button>
                            </label>
                            <div id="photoRows{{ $c->id_compagnie }}" class="tg-photo-rows"></div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-1"></i> Envoyer les photos
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    <script src="{{ asset('mon_js/scrip_compagnie.js') }}"></script>
    <script src="{{ asset('mon_js/alert_delete.js') }}"></script>
    <style>
        .tg-photo-row { display: flex; align-items: center; gap: .5rem; margin-bottom: .5rem; }
        .tg-photo-row input[type="file"] { flex: 1; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                new bootstrap.Modal(document.getElementById('modalAjouterCompagnie')).show();
            @endif

            // "Ajouter une photo" : chaque clic ajoute une nouvelle ligne (input file +
            // bouton de retrait), toutes soumises ensemble dans un seul formulaire —
            // permet d'envoyer plusieurs photos en une fois tout en gardant un contrôle
            // ligne par ligne (comme un "add row" de tableau).
            document.querySelectorAll('.tg-add-photo-row').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var container = document.getElementById(btn.dataset.target);
                    if (!container) return;

                    var row = document.createElement('div');
                    row.className = 'tg-photo-row';
                    row.innerHTML =
                        '<input type="file" class="form-control form-control-sm" name="photos[]" accept="image/png, image/jpeg, image/webp" required>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger tg-remove-photo-row" title="Retirer cette ligne"><i class="fas fa-xmark"></i></button>';
                    container.appendChild(row);

                    row.querySelector('.tg-remove-photo-row').addEventListener('click', function () {
                        row.remove();
                    });
                });

                // Une première ligne par défaut, pour ne pas partir d'une liste vide.
                btn.click();
            });
        });
    </script>
@endsection
