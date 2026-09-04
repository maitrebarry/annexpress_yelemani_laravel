@extends('layouts.admin')

@section('title', 'Place limite · Sirali Admin')
@section('breadcrumb-title', 'Configuration')
@section('breadcrumb-active', 'Place limite')

@section('content')
<div class="row">
    @include('admin.partials.config-nav', ['active' => 'place_limite'])

    <div class="col-12 col-xxl-9">
        <div class="card config-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-chair me-2"></i>Limites des places</h5>
            </div>
            <div class="card-body p-4">
                @forelse ($listePlace as $p)
                    <div class="d-flex align-items-center flex-wrap gap-3 mb-3 pb-3 border-bottom">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-secondary">{{ $p->nom_compagnie }}</p>
                            <h4 class="my-1">{{ $p->place_minumale }}</h4>
                        </div>
                        <button type="button" class="edit-place-btn btn btn-link ms-auto p-0" data-bs-toggle="modal"
                            data-bs-target="#modalModifierPlace"
                            data-place="{{ $p->place_minumale }}"
                            data-id="{{ $p->id_place_minumale }}" title="Modifier">
                            <i class='fas fa-pen-to-square fs-1'></i>
                        </button>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucune limite de places configurée.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
    <div class="modal fade" id="modalModifierPlace" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h6 class="modal-title text-white">Modifier la place limite</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.place-limite.update') }}">
                    @csrf
                    <div class="modal-body">
                        <label class="form-label">N° de place minimale <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_place_minumale" name="place_minumale" required>
                        <input type="hidden" id="edit_id_place_minumale" name="id_place_minumale">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary fw-semibold">Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        tgReady(function () {
            document.querySelectorAll('.edit-place-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_place_minumale').value = this.dataset.place;
                    document.getElementById('edit_id_place_minumale').value = this.dataset.id;
                });
            });
        });
    </script>
@endsection
