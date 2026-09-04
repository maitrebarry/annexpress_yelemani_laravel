@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Voyages · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-calendar-check me-1"></i> G-programme</span>
@endsection
@section('breadcrumb-active', 'Voyages')

@section('breadcrumb-actions')
    @unless ($authUser->estLectureSeule())
        <a href="{{ route('admin.programme.create') }}" class="btn btn-sm btn-success rounded-pill shadow-sm">
            <i class="fas fa-circle-plus me-1"></i> Ajouter
        </a>
    @endunless
@endsection

@section('content')


    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-calendar-check me-1"></i> Liste des voyages programmés
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Départ</th>
                            <th>Destination</th>
                            <th>RDV</th>
                            <th>Heure de départ</th>
                            <th>Prix</th>
                            <th>Escale(s)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($liste as $p)
                            @php
                                $escalesLabel = $p->escales->map(fn ($l) => ($l->escale->escales ?? '?').' ('.number_format((float) $l->prix_escale, 0, ',', ' ').' FCFA)')->implode(', ');
                                $escalesData = $p->escales->map(fn ($l) => ['id_escale' => $l->id_escales, 'escales' => $l->escale->escales ?? '', 'prix_escale' => $l->prix_escale])->values();
                            @endphp
                            <tr class="{{ $p->estDoublon ? 'table-warning' : '' }}">
                                <td data-label="Départ">
                                    {{ ($p->depart->localite ?? '?').' ('.($p->depart->numeroGare ?? '?').')' }}
                                    @if ($p->estDoublon)
                                        <br><span class="badge bg-warning text-dark" title="Un autre trajet identique (même départ/destination/heure) existe déjà — vérifiez les deux et supprimez celui en trop.">
                                            <i class="fas fa-triangle-exclamation"></i> Doublon
                                        </span>
                                    @endif
                                </td>
                                <td data-label="Destination">{{ ($p->destination->localite ?? '?').' ('.($p->destination->numeroGare ?? '?').')' }}</td>
                                <td data-label="RDV">{{ $p->rdv }}</td>
                                <td data-label="Heure de départ">{{ $p->heureDepart }}</td>
                                <td data-label="Prix"><strong class="text-danger">{{ number_format($p->prix, 0, ',', ' ') }} FCFA</strong></td>
                                <td data-label="Escale(s)">
                                    @if ($escalesLabel !== '')
                                        {{ $escalesLabel }}
                                    @else
                                        <span class="text-muted fst-italic">Aucune escale</span>
                                    @endif
                                </td>
                                <td data-label="Action">
                                    <div class="dropup text-center">
                                        <a href="#" class="text-dark text-decoration-none fs-4" data-bs-toggle="dropdown" aria-expanded="false">&#8943;</a>
                                        <div class="dropdown-menu">
                                            @unless ($authUser->estLectureSeule())
                                                <a class="dropdown-item edit-programme-btn" href="javascript:;"
                                                    data-bs-toggle="modal" data-bs-target="#modalModifierProgramme"
                                                    data-id="{{ $p->idProgrammer }}"
                                                    data-prix="{{ $p->prix }}"
                                                    data-heuredepart="{{ $p->heureDepart }}"
                                                    data-rdv="{{ $p->rdv }}"
                                                    data-route="{{ ($p->depart->localite ?? '?').' → '.($p->destination->localite ?? '?') }}"
                                                    data-escales="{{ $escalesData->toJson() }}">
                                                    <i class="fas fa-pen me-2"></i>Modifier
                                                </a>
                                                <a class="dropdown-item text-danger delete-button" href="{{ route('admin.programme.destroy', $p->idProgrammer) }}"
                                                    title="Ce voyage et ses éventuelles escales/affectations de car seront également supprimés.">
                                                    <i class="fas fa-trash me-2"></i>Supprimer
                                                </a>
                                            @endunless
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted fst-italic">Aucun voyage programmé pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('modals')
    <div class="modal fade" id="modalModifierProgramme" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-pen me-1"></i> Modifier le trajet : <span id="editProgrammeRoute"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.programme.update') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="idProgrammer" id="editProgrammeId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Heure de départ</label>
                                <select class="form-select" name="heureDepart" id="editProgrammeHeureDepart">
                                    @foreach ($listeHoraire as $horaire)
                                        <option value="{{ $horaire->heuredepart }}">{{ $horaire->heuredepart }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">RDV</label>
                                <input type="time" class="form-control" name="rdv" id="editProgrammeRdv">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Prix du trajet</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="prix" id="editProgrammePrix" required>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                            </div>
                        </div>
                        <div id="editProgrammeEscales"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-floppy-disk fs-5 me-2"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        tgReady(function () {
            var heureSelect = document.getElementById('editProgrammeHeureDepart');
            var rdvInput = document.getElementById('editProgrammeRdv');

            function calculerRdv() {
                if (!heureSelect.value) return;
                var parts = heureSelect.value.split(':').map(Number);
                var heures = parts[0], minutes = parts[1] - 45;
                if (minutes < 0) { minutes += 60; heures -= 1; }
                if (heures < 0) { heures += 24; }
                rdvInput.value = (heures < 10 ? '0' : '') + heures + ':' + (minutes < 10 ? '0' : '') + minutes;
            }
            heureSelect.addEventListener('change', calculerRdv);

            document.querySelectorAll('.edit-programme-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('editProgrammeId').value = this.dataset.id;
                    document.getElementById('editProgrammePrix').value = this.dataset.prix;
                    heureSelect.value = this.dataset.heuredepart;
                    rdvInput.value = this.dataset.rdv;
                    document.getElementById('editProgrammeRoute').textContent = this.dataset.route;

                    var container = document.getElementById('editProgrammeEscales');
                    container.innerHTML = '';
                    var escales = JSON.parse(this.dataset.escales || '[]');
                    if (escales.length > 0) {
                        var label = document.createElement('label');
                        label.className = 'form-label fw-semibold mt-3';
                        label.innerHTML = '<i class="fas fa-map me-1"></i>Frais des escales';
                        container.appendChild(label);
                        escales.forEach(function (e) {
                            var group = document.createElement('div');
                            group.className = 'input-group mb-2';
                            group.innerHTML = '<span class="input-group-text"></span>' +
                                '<input type="number" class="form-control" name="prix_escale[' + e.id_escale + ']">' +
                                '<span class="input-group-text">FCFA</span>';
                            group.querySelector('.input-group-text').textContent = e.escales;
                            group.querySelector('input').value = e.prix_escale ?? 0;
                            container.appendChild(group);
                        });
                    }
                });
            });
        });
    </script>
@endsection
