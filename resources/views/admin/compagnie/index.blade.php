@extends('layouts.admin')

@section('title', 'Compagnies · TransHub Admin')
@section('breadcrumb-title', 'Configuration')
@section('breadcrumb-active', 'Compagnies')

@section('breadcrumb-actions')
    <button type="button" class="btn btn-success d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterCompagnie">
        <i class="bx bx-plus-circle fs-5"></i> Ajouter
    </button>
@endsection

@section('content')
<div class="row">
    @include('admin.partials.config-nav', ['active' => 'compagnie'])

    <div class="col-12 col-xxl-9">
        @include('admin.partials.set_flash')

        <div class="card config-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="bx bx-buildings me-2"></i>Liste des compagnies</h5>
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
                                                <i class="bx bx-bus fs-4"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="fw-semibold" data-label="Nom compagnie">{{ $c->nom_compagnie }}</td>
                                    <td data-label="Libellé">{{ $c->libele }}</td>
                                    <td data-label="Slogan">{{ $c->slogant }}</td>
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
                                                        data-logofilename="{{ $c->logo }}">
                                                        <i class="bx bx-edit me-2"></i>Modifier
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger delete-button" href="{{ route('admin.compagnie.destroy', $c->id_compagnie) }}">
                                                        <i class="bx bx-trash me-2"></i>Supprimer
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
@endsection

@section('scripts')
    <script src="{{ asset('mon_js/scrip_compagnie.js') }}"></script>
    <script src="{{ asset('mon_js/alert_delete.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                new bootstrap.Modal(document.getElementById('modalAjouterCompagnie')).show();
            @endif
        });
    </script>
@endsection
