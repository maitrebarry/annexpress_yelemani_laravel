@extends('layouts.admin')

@section('title', 'Actualités · TransGest Admin')
@section('breadcrumb-title', 'Configuration')
@section('breadcrumb-active', 'Actualités')

@section('breadcrumb-actions')
    <button type="button" class="btn btn-success d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterActualite">
        <i class="fas fa-circle-plus fs-5"></i> Publier
    </button>
@endsection

@section('content')
<div class="row">
    @include('admin.partials.config-nav', ['active' => 'actualite'])

    <div class="col-12 col-xxl-9">
        <div class="card config-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-newspaper me-2"></i>Actualités publiées sur le site public</h5>
            </div>
            <div class="card-body p-4">
                @if ($liste->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-newspaper fs-3 d-block mb-2"></i>
                        Aucune actualité publiée pour le moment.
                    </div>
                @else
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Image</th>
                                <th>Titre</th>
                                <th>Date de publication</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($liste as $a)
                                <tr>
                                    <td data-label="Image">
                                        @if ($a->image)
                                            <img src="{{ asset('images/actualites/'.$a->image) }}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
                                        @else
                                            <div style="width:56px;height:56px;background:rgba(15,59,94,.08);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--primary-color);margin:0 auto;">
                                                <i class="fas fa-newspaper fs-4"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="fw-semibold text-start" data-label="Titre">
                                        {{ $a->titre }}
                                        @if ($a->date_publication->isFuture())
                                            <span class="badge bg-warning text-dark ms-1">Programmée</span>
                                        @endif
                                    </td>
                                    <td data-label="Date">{{ $a->date_publication->translatedFormat('d M Y') }}</td>
                                    <td data-label="Action">
                                        <div class="dropdown">
                                            <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown" aria-expanded="false">&#8943;</a>
                                            <ul class="dropdown-menu shadow-sm">
                                                <li>
                                                    <a class="dropdown-item edit-actualite-btn" href="#"
                                                        data-bs-toggle="modal" data-bs-target="#modalModifierActualite"
                                                        data-id="{{ $a->id }}"
                                                        data-titre="{{ $a->titre }}"
                                                        data-contenu="{{ $a->contenu }}"
                                                        data-date="{{ $a->date_publication->format('Y-m-d') }}"
                                                        data-image="{{ $a->image ? asset('images/actualites/'.$a->image) : '' }}">
                                                        <i class="fas fa-pen me-2"></i>Modifier
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger delete-button" href="{{ route('admin.actualite.destroy', $a->id) }}">
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
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
    <!-- Modal Publication -->
    <div class="modal fade" id="modalAjouterActualite" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Publier une actualité</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.actualite.store') }}" enctype="multipart/form-data">
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
                            <div class="col-md-8">
                                <label class="form-label">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="titre" value="{{ old('titre') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date de publication <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date_publication" value="{{ old('date_publication', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Contenu <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="contenu" rows="5" required>{{ old('contenu') }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Image (facultative)</label>
                                <input type="file" class="form-control" name="image" accept="image/png, image/jpeg, image/webp">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary fw-semibold">Publier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modification -->
    <div class="modal fade" id="modalModifierActualite" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Modifier l'actualité</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.actualite.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editActualiteId">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Titre</label>
                                <input type="text" class="form-control" name="titre" id="editActualiteTitre" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date de publication</label>
                                <input type="date" class="form-control" name="date_publication" id="editActualiteDate" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Contenu</label>
                                <textarea class="form-control" name="contenu" id="editActualiteContenu" rows="5" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <img id="editActualiteImagePreview" src="" alt="" width="100" class="mb-2 border rounded" style="display:none;">
                                <label class="form-label d-block">Changer l'image</label>
                                <input type="file" class="form-control" name="image" accept="image/png, image/jpeg, image/webp">
                            </div>
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
    <script src="{{ asset('mon_js/alert_delete.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if ($errors->any())
                new bootstrap.Modal(document.getElementById('modalAjouterActualite')).show();
            @endif

            document.querySelectorAll('.edit-actualite-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('editActualiteId').value = btn.dataset.id;
                    document.getElementById('editActualiteTitre').value = btn.dataset.titre;
                    document.getElementById('editActualiteContenu').value = btn.dataset.contenu;
                    document.getElementById('editActualiteDate').value = btn.dataset.date;
                    var preview = document.getElementById('editActualiteImagePreview');
                    if (btn.dataset.image) {
                        preview.src = btn.dataset.image;
                        preview.style.display = 'block';
                    } else {
                        preview.style.display = 'none';
                    }
                });
            });
        });
    </script>
@endsection
