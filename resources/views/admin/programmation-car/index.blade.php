@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Cars · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-calendar-check me-1"></i> G-programme</span>
@endsection
@section('breadcrumb-active', 'Cars')

@section('breadcrumb-actions')
    @unless ($authUser->estLectureSeule())
        <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalProgrammerCar">
            <i class="fas fa-bus me-1"></i> Programmer un car
        </button>
    @endunless
@endsection

@section('content')


    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-bus me-1"></i> Liste des cars programmés
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Numéro de car</th>
                            <th>Nombre de places</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($listeCarProgramme as $c)
                            <tr>
                                <td data-label="Numéro de car">Car : {{ $c->numero_car }}</td>
                                <td data-label="Nombre de places">{{ $c->nbr_place }}</td>
                                <td data-label="Action">
                                    <div class="dropup text-center">
                                        <a href="#" class="text-dark text-decoration-none fs-4" data-bs-toggle="dropdown" aria-expanded="false">&#8943;</a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            @unless ($authUser->estLectureSeule())
                                                <a class="dropdown-item ajouter-trajet-btn" href="javascript:;"
                                                    data-bs-toggle="modal" data-bs-target="#modalAjouterTrajet"
                                                    data-id-car="{{ $c->id_car }}"
                                                    data-numero-car="{{ $c->numero_car }}"
                                                    data-trajets-existants="{{ ($trajetsParCar[$c->id_car] ?? collect())->toJson() }}">
                                                    <i class="fas fa-plus me-2"></i>Ajouter un trajet
                                                </a>
                                                <a class="dropdown-item text-danger delete-button" href="{{ route('admin.programmation-car.destroy', $c->id_car) }}"
                                                    title="La programmation de ce car et ses trajets affectés seront supprimés.">
                                                    <i class="fas fa-trash me-2"></i>Supprimer
                                                </a>
                                            @endunless
                                            <a class="dropdown-item" href="javascript:;" data-bs-toggle="modal" data-bs-target="#modalDetails{{ $c->id_car }}">
                                                <i class="fas fa-circle-info me-2"></i>Détails
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-muted fst-italic">Aucun car programmé pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('modals')

    @unless ($authUser->estLectureSeule())
        <!-- Modal Programmer un car -->
        <div class="modal fade" id="modalProgrammerCar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white">Programmation du car</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="{{ route('admin.programmation-car.store') }}">
                        @csrf
                        <div class="modal-body">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Car(s) <span class="text-danger">*</span></label>
                                    @if ($listeCarDisponible->isNotEmpty())
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" id="toutCocherCars">
                                            <label class="form-check-label small" for="toutCocherCars">Tout cocher</label>
                                        </div>
                                    @endif
                                </div>
                                <p class="small text-muted mb-2">Cochez un ou plusieurs cars (ou « Tout cocher ») : les mêmes trajets choisis ci-dessous leur seront affectés à tous, en une seule fois.</p>
                                <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
                                    @forelse ($listeCarDisponible as $c)
                                        <div class="form-check">
                                            <input class="form-check-input car-checkbox" type="checkbox" name="id_car[]" value="{{ $c->id_car }}" id="carProg{{ $c->id_car }}">
                                            <label class="form-check-label" for="carProg{{ $c->id_car }}">Car : {{ $c->numero_car }}</label>
                                        </div>
                                    @empty
                                        <p class="text-muted small mb-0">Aucun car disponible à programmer.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div class="mb-3 col-12 mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Trajet(s) à parcourir <span class="text-danger">*</span></label>
                                    @if ($listeTrajet->isNotEmpty())
                                        <button type="button" id="toutSelectionnerTrajets" class="btn btn-sm btn-outline-primary">Tout sélectionner</button>
                                    @endif
                                </div>
                                <select class="form-control select-trajet-programmer" multiple="multiple" name="idTrajet[]">
                                    @foreach ($listeTrajet as $t)
                                        <option value="{{ $t->idProgrammer }}">{{ $t->depart.' ('.$t->gareDepart.') → '.$t->destination.' ('.$t->gareDestination.') — '.substr($t->heureDepart, 0, 5) }}</option>
                                    @endforeach
                                </select>
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

        <!-- Modal Ajouter un trajet à un car déjà programmé -->
        <div class="modal fade" id="modalAjouterTrajet" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white">Ajouter un trajet <span id="modalAjouterNumeroCar"></span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="{{ route('admin.programmation-car.ajouter-trajet') }}">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="id_car" id="modalAjouterIdCar">
                            <div class="mb-3 col-12">
                                <label class="form-label">Trajet(s) à parcourir</label>
                                <select class="form-control select-trajet-ajouter" multiple="multiple" name="idTrajet[]">
                                    @foreach ($listeTrajet as $t)
                                        <option value="{{ $t->idProgrammer }}">{{ $t->depart.' ('.$t->gareDepart.') → '.$t->destination.' ('.$t->gareDestination.') — '.substr($t->heureDepart, 0, 5) }}</option>
                                    @endforeach
                                </select>
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
    @endunless

    <!-- Modales Détails (une par car programmé) -->
    @foreach ($listeCarProgramme as $c)
        <div class="modal fade" id="modalDetails{{ $c->id_car }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">Détails de la programmation — Car {{ $c->numero_car }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            @forelse (($detailsParCar[$c->id_car] ?? collect()) as $t)
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <p class="mb-1"><strong>Itinéraire :</strong><br>{{ $t->depart.' ('.$t->gareDepart.')' }} → {{ $t->destination.' ('.$t->gareDestination.')' }}</p>
                                            <p class="mb-0"><strong>Horaire :</strong>
                                                <span class="badge bg-primary-subtle text-primary"><i class="fas fa-clock"></i> {{ substr($t->heureDepart, 0, 5) }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">Aucun trajet affecté à ce car.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && $.fn.select2) {
                $('.select-trajet-programmer').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#modalProgrammerCar'), placeholder: 'Choisissez un ou plusieurs trajets' });
                $('.select-trajet-ajouter').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#modalAjouterTrajet'), placeholder: 'Choisissez un ou plusieurs trajets' });
            }

            var toutCocher = document.getElementById('toutCocherCars');
            if (toutCocher) {
                toutCocher.addEventListener('change', function () {
                    document.querySelectorAll('.car-checkbox').forEach(function (cb) { cb.checked = toutCocher.checked; });
                });
                document.querySelectorAll('.car-checkbox').forEach(function (cb) {
                    cb.addEventListener('change', function () {
                        var total = document.querySelectorAll('.car-checkbox').length;
                        var cochees = document.querySelectorAll('.car-checkbox:checked').length;
                        toutCocher.checked = total > 0 && cochees === total;
                    });
                });
            }

            var toutSelectionner = document.getElementById('toutSelectionnerTrajets');
            if (toutSelectionner) {
                toutSelectionner.addEventListener('click', function () {
                    $('.select-trajet-programmer option').prop('selected', true);
                    $('.select-trajet-programmer').trigger('change');
                });
            }

            // Remplit le modal "Ajouter un trajet" avec le car cliqué, et grise les
            // trajets déjà assignés à ce car (empêche le doublon directement dans l'UI).
            document.querySelectorAll('.ajouter-trajet-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var idCar = this.dataset.idCar;
                    var numeroCar = this.dataset.numeroCar;
                    var trajetsExistants = JSON.parse(this.dataset.trajetsExistants || '[]').map(Number);

                    document.getElementById('modalAjouterIdCar').value = idCar;
                    document.getElementById('modalAjouterNumeroCar').textContent = '(Car : ' + numeroCar + ')';

                    var $select = $('.select-trajet-ajouter');
                    $select.val(null);
                    $select.find('option').each(function () {
                        var $option = $(this);
                        var dejaAssigne = trajetsExistants.includes(parseInt($option.val(), 10));
                        var texteBase = $option.data('texte-base');
                        if (texteBase === undefined) {
                            texteBase = $option.text();
                            $option.data('texte-base', texteBase);
                        }
                        $option.prop('disabled', dejaAssigne);
                        $option.text(dejaAssigne ? texteBase + ' (déjà assigné à ce car)' : texteBase);
                    });
                    $select.trigger('change');
                });
            });
        });
    </script>
@endsection
