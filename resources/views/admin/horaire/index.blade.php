@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Horaires · TransGest Admin')
@section('breadcrumb-title', 'Configuration')
@section('breadcrumb-active', 'Horaire')

@section('breadcrumb-actions')
    <button type="button" class="btn btn-success d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterHoraire">
        <i class="fas fa-circle-plus fs-5"></i> Ajouter
    </button>
@endsection

@section('content')
<div class="row">
    @include('admin.partials.config-nav', ['active' => 'horaire'])

    <div class="col-12 col-xxl-9">

        <div class="card config-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-clock me-2"></i>Liste des horaires</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Heure de départ</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($liste as $h)
                                <tr>
                                    <td data-label="Heure de départ">{{ \Illuminate\Support\Carbon::parse($h->heuredepart)->format('H:i') }}</td>
                                    <td data-label="Action">
                                        <a href="javascript:;" class="edit-horaire-btn me-2" title="Modifier"
                                            data-bs-toggle="modal" data-bs-target="#modalModifierHoraire"
                                            data-id="{{ $h->id_heure }}" data-heure="{{ \Illuminate\Support\Carbon::parse($h->heuredepart)->format('H:i') }}">
                                            <i class="fas fa-pen text-primary fs-4"></i>
                                        </a>
                                        <a href="{{ route('admin.horaire.destroy', $h->id_heure) }}" class="delete-button" title="Supprimer">
                                            <i class="fas fa-trash text-danger fs-4"></i>
                                        </a>
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
    <!-- Modal Ajout horaires -->
    <div class="modal fade" id="modalAjouterHoraire" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Ajouter des horaires</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formHoraires" method="post" action="{{ route('admin.horaire.store') }}">
                        @csrf
                        @if ($authUser->isSuperAdmin())
                            <div class="mb-3">
                                <label class="form-label">Compagnie <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_compagnie" required>
                                    <option value="" disabled selected>Choisissez une compagnie</option>
                                    @foreach ($listeCompagnie as $c)
                                        <option value="{{ $c->id_compagnie }}">{{ $c->nom_compagnie }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div id="horaireRows">
                            <div class="input-group mb-2 horaire-row">
                                <input type="time" class="form-control" name="heuredepart[]" required>
                                <button type="button" class="btn btn-outline-danger remove-row-btn d-none" title="Retirer cette ligne">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" id="addHoraireRow" class="btn btn-sm btn-outline-primary mb-3">
                            <i class="fas fa-plus"></i> Ajouter une ligne
                        </button>
                        <div class="modal-footer border-0 px-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-floppy-disk fs-5 me-2"></i>Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modification horaire -->
    <div class="modal fade" id="modalModifierHoraire" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Modifier l'horaire</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.horaire.update') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id_heure" id="edit_id_heure">
                        <label class="form-label fw-semibold">Heure de départ</label>
                        <input type="time" class="form-control" name="heuredepart" id="edit_heuredepart" required>
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
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.edit-horaire-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_id_heure').value = this.dataset.id;
                    document.getElementById('edit_heuredepart').value = this.dataset.heure;
                });
            });

            var rowsContainer = document.getElementById('horaireRows');
            var addBtn = document.getElementById('addHoraireRow');

            function toggleRemoveButtons() {
                var rows = rowsContainer.querySelectorAll('.horaire-row');
                rows.forEach(function(row) {
                    row.querySelector('.remove-row-btn').classList.toggle('d-none', rows.length <= 1);
                });
            }

            addBtn.addEventListener('click', function() {
                var firstRow = rowsContainer.querySelector('.horaire-row');
                var newRow = firstRow.cloneNode(true);
                newRow.querySelector('input').value = '';
                rowsContainer.appendChild(newRow);
                toggleRemoveButtons();
            });

            rowsContainer.addEventListener('click', function(e) {
                var btn = e.target.closest('.remove-row-btn');
                if (btn) {
                    btn.closest('.horaire-row').remove();
                    toggleRemoveButtons();
                }
            });
        });
    </script>
@endsection
