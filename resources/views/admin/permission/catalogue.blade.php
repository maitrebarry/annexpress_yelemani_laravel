@extends('layouts.admin')

@section('title', 'Permissions · TransGest Admin')
@section('breadcrumb-title', 'Configuration')
@section('breadcrumb-active', 'Permission')

@section('breadcrumb-actions')
    <button type="button" class="btn btn-success d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterPermission">
        <i class="fas fa-circle-plus fs-5"></i> Ajouter
    </button>
@endsection

@section('content')
<div class="row">
    @include('admin.partials.config-nav', ['active' => 'permission'])

    <div class="col-12 col-xxl-9">
        <div class="card config-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-shield-halved me-2"></i>Liste des permissions</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Nom permission</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($liste as $p)
                                <tr>
                                    <td data-label="Nom permission">{{ $p->nom_permission }}</td>
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
    <div class="modal fade" id="modalAjouterPermission" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Nouvelle permission</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.permission.catalogue.store') }}">
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
                        <label class="form-label">Nom de la permission <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nom_permission" value="{{ old('nom_permission') }}" placeholder="Ex: Reservation_gestion" required>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                new bootstrap.Modal(document.getElementById('modalAjouterPermission')).show();
            @endif
        });
    </script>
@endsection
