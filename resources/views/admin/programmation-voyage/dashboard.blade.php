@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Programmer un voyage · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-calendar-check me-1"></i> G-programme</span>
@endsection
@section('breadcrumb-active', 'Programmer un voyage')

@section('breadcrumb-actions')
    <a href="{{ route('admin.programmation-voyage.liste-journaliere') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-list-ul me-1"></i> Voir la liste
    </a>
@endsection

@section('content')


    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-bus me-1"></i> Programmation des voyages
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.programmation-voyage.store') }}">
                @csrf

                <div class="row mb-4 align-items-center g-2">
                    <label for="jourVoyage" class="col-sm-2 col-form-label fw-semibold">Jour du voyage</label>
                    <div class="col-sm-4">
                        <input type="date" class="form-control shadow-sm" id="jourVoyage" disabled>
                    </div>
                    @if ($derniereDate && ! $authUser->estLectureSeule())
                        <div class="col-sm-6">
                            <button type="button" id="btnReproduireHier" class="btn btn-outline-primary shadow-sm">
                                <i class="fas fa-repeat me-1"></i> Reproduire la programmation du {{ \Illuminate\Support\Carbon::parse($derniereDate)->format('d/m/Y') }}
                            </button>
                        </div>
                    @endif
                </div>
                <p class="small text-muted mb-3">La programmation créée aujourd'hui s'applique à la date du jour.</p>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm rounded">
                        <thead class="table-light text-center">
                            <tr>
                                <th>@unless ($authUser->estLectureSeule())<input type="checkbox" id="selectAll">@endunless</th>
                                <th>Numéro Car</th>
                                <th>Horaire</th>
                                @if ($authUser->droit === 'Admin')
                                    <th>Départ</th>
                                @endif
                                <th>Destination</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($listeCarDisponible as $index => $c)
                                <tr data-id-car="{{ $c->id_car }}">
                                    <td>
                                        @unless ($authUser->estLectureSeule())
                                            <input type="checkbox" name="select_car[]" value="{{ $index }}" class="form-check-input checkbox-car">
                                        @endunless
                                    </td>
                                    <td>
                                        <input type="text" class="form-control text-center shadow-sm" value="{{ $c->numero_car }}" readonly>
                                        <input type="hidden" name="id_care[{{ $index }}]" value="{{ $c->id_car }}">
                                    </td>
                                    <td>
                                        <select class="form-select shadow-sm" name="id_horaire[{{ $index }}]">
                                            <option value="" selected>Choisir d'abord une destination</option>
                                        </select>
                                    </td>
                                    @if ($authUser->droit === 'Admin')
                                        <td>
                                            <input type="text" name="id_depart[{{ $index }}]" class="form-control text-center shadow-sm champ-depart" readonly placeholder="—">
                                            <input type="hidden" name="id_depart_agence[{{ $index }}]" class="champ-depart-agence">
                                        </td>
                                    @endif
                                    <td>
                                        <select class="form-select shadow-sm" name="id_destination[{{ $index }}]">
                                            <option selected value="">Choisir une destination</option>
                                            @foreach ($tousLesTrajets as $t)
                                                <option value="{{ $t->destinationLocalite }}"
                                                    data-depart="{{ $t->departLocalite }}"
                                                    data-depart-agence="{{ $t->idDepart }}"
                                                    data-destination-agence="{{ $t->idDestination }}"
                                                    data-heure="{{ $t->heureDepart }}">
                                                    {{ $t->departLocalite }} (Gare {{ $t->numeroGareDepart }}) -&gt; {{ $t->destinationLocalite }} (Gare {{ $t->numeroGareDestination }}) ({{ substr($t->heureDepart, 0, 5) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="id_destination_agence[{{ $index }}]" class="champ-destination-agence">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $authUser->droit === 'Admin' ? 5 : 4 }}" class="text-muted">🚫 Aucun car disponible</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @unless ($authUser->estLectureSeule())
                    <button class="btn btn-success shadow-sm" type="submit">
                        <i class="fas fa-floppy-disk me-1"></i> Enregistrer
                    </button>
                @endunless
            </form>
        </div>
    </div>

    @if ($carsEnTransit->isNotEmpty())
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-success text-white fw-bold">
                <i class="fas fa-shield-halved me-1"></i> Véhicules en approche (validation d'arrivée)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm rounded">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Numéro Car</th>
                                <th>Places totales</th>
                                <th>Destination prévue</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @foreach ($carsEnTransit as $c)
                                <tr>
                                    <td class="fw-bold">{{ $c->numero_car }}</td>
                                    <td>{{ $c->nbr_place }}</td>
                                    <td>
                                        {{ substr($c->status_car, 11) }}
                                        @if ($c->numeroGareDestination)
                                            <span class="text-muted">(Gare {{ $c->numeroGareDestination }})</span>
                                        @endif
                                    </td>
                                    <td>
                                        @unless ($authUser->estLectureSeule())
                                            <form method="post" action="{{ route('admin.programmation-voyage.valider-arrivee') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="id_car_arrivee" value="{{ $c->id_car }}">
                                                <button type="submit" class="btn btn-sm btn-success shadow-sm rounded-pill px-3">
                                                    <i class="fas fa-check-double me-1"></i> Valider l'arrivée
                                                </button>
                                            </form>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($carsBloques->isNotEmpty())
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-warning text-dark fw-bold">
                <i class="fas fa-triangle-exclamation me-1"></i> Cars bloqués (anomalie à résoudre)
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Ces cars sont marqués « en transit » en base, mais leur décollage n'a jamais été
                    enregistré. Ils n'apparaissent ni comme disponibles, ni comme « en approche ».
                    Indiquez leur état réel pour les débloquer.
                </p>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm rounded">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Numéro Car</th>
                                <th>Départ prévu</th>
                                <th>Destination prévue</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @foreach ($carsBloques as $c)
                                <tr>
                                    <td class="fw-bold">{{ $c->numero_car }}</td>
                                    <td>{{ $c->origine }}</td>
                                    <td>{{ $c->destination }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($c->date_enregistre)->format('d/m/Y') }}</td>
                                    <td>
                                        <form method="post" action="{{ route('admin.programmation-voyage.debloquer-arrive') }}" class="d-inline confirm-arrivee-form"
                                            data-confirm-text="Confirmer : ce car est bien arrivé à {{ $c->destination }} ?">
                                            @csrf
                                            <input type="hidden" name="id_programmation_bloque" value="{{ $c->id_programmation }}">
                                            <button type="submit" class="btn btn-sm btn-success shadow-sm rounded-pill px-2 mb-1">
                                                <i class="fas fa-check-double me-1"></i> Arrivé à destination
                                            </button>
                                        </form>
                                        <form method="post" action="{{ route('admin.programmation-voyage.debloquer-jamais-parti') }}" class="d-inline confirm-arrivee-form"
                                            data-confirm-text="Confirmer : ce car n'a jamais quitté {{ $c->origine }} ?">
                                            @csrf
                                            <input type="hidden" name="id_programmation_bloque" value="{{ $c->id_programmation }}">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary shadow-sm rounded-pill px-2 mb-1">
                                                <i class="fas fa-rotate-left me-1"></i> Jamais parti
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    <script>
        // Confirmation SweetAlert (au lieu du confirm() natif du navigateur) pour les 2
        // actions de déblocage d'anomalie "Cars bloqués" — même style que le reste de
        // l'admin (voir mon_js/alert_delete.js).
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.confirm-arrivee-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Confirmation',
                        text: form.dataset.confirmText,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Oui, confirmer',
                        cancelButtonText: 'Annuler',
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-light',
                        },
                        buttonsStyling: false,
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            var dateInput = document.getElementById('jourVoyage');
            var today = new Date().toISOString().slice(0, 10);
            dateInput.value = today;

            function syncDestinationRequired(tr) {
                var checkbox = tr.querySelector('.checkbox-car');
                var selectDestination = tr.querySelector('select[name^="id_destination["]');
                if (checkbox && selectDestination) {
                    selectDestination.required = checkbox.checked;
                }
            }

            document.querySelectorAll('tbody tr[data-id-car]').forEach(function (tr) {
                var checkbox = tr.querySelector('.checkbox-car');
                if (checkbox) {
                    checkbox.addEventListener('change', function () { syncDestinationRequired(tr); });
                }
            });

            var selectAll = document.getElementById('selectAll');
            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    var checked = this.checked;
                    document.querySelectorAll('.checkbox-car').forEach(function (cb) {
                        cb.checked = checked;
                        syncDestinationRequired(cb.closest('tr'));
                    });
                });
            }

            // Remplit automatiquement, selon le trajet choisi : le champ "Départ" (Admin) et
            // le select "Horaire" (jamais une saisie libre, toujours l'heure réelle de ce trajet).
            document.querySelectorAll('select[name^="id_destination["]').forEach(function (select) {
                select.addEventListener('change', function () {
                    var tr = this.closest('tr');
                    var opt = this.options[this.selectedIndex];

                    var champDepart = tr.querySelector('.champ-depart');
                    if (champDepart) {
                        champDepart.value = opt?.getAttribute('data-depart') || '';
                    }
                    var champDepartAgence = tr.querySelector('.champ-depart-agence');
                    if (champDepartAgence) {
                        champDepartAgence.value = opt?.getAttribute('data-depart-agence') || '';
                    }
                    var champDestinationAgence = tr.querySelector('.champ-destination-agence');
                    if (champDestinationAgence) {
                        champDestinationAgence.value = opt?.getAttribute('data-destination-agence') || '';
                    }

                    var selectHoraire = tr.querySelector('select[name^="id_horaire["]');
                    if (selectHoraire) {
                        var heure = opt?.getAttribute('data-heure') || '';
                        selectHoraire.innerHTML = heure
                            ? '<option value="' + heure + '" selected>' + heure.slice(0, 5) + '</option>'
                            : '<option value="" selected>Choisir d\'abord une destination</option>';
                    }
                });
            });

            @if ($programmationVeille->isNotEmpty())
                var programmationVeille = @json($programmationVeille);
                var btnReproduire = document.getElementById('btnReproduireHier');
                if (btnReproduire) {
                    btnReproduire.addEventListener('click', function () {
                        var appliques = 0, ignores = 0;

                        document.querySelectorAll('tbody tr[data-id-car]').forEach(function (tr) {
                            var idCar = tr.getAttribute('data-id-car');
                            var prev = programmationVeille[idCar];
                            if (!prev) return;

                            var selectHoraire = tr.querySelector('select[name^="id_horaire["]');
                            var selectDestination = tr.querySelector('select[name^="id_destination["]');
                            var checkbox = tr.querySelector('.checkbox-car');

                            var existe = selectDestination && Array.from(selectDestination.options).some(function (o) { return o.value === prev.id_trajet; });
                            if (!existe) { ignores++; return; }

                            selectDestination.value = prev.id_trajet;
                            selectDestination.dispatchEvent(new Event('change'));

                            if (!selectHoraire || selectHoraire.value !== prev.id_horaire) { ignores++; return; }

                            if (checkbox) checkbox.checked = true;
                            syncDestinationRequired(tr);
                            appliques++;
                        });

                        var message = appliques + ' car(s) pré-rempli(s).';
                        if (ignores > 0) {
                            message += ' ' + ignores + ' car(s) non repris (horaire/destination indisponible aujourd\'hui) — à programmer manuellement.';
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Reproduction terminée', message, 'info');
                        } else {
                            alert(message);
                        }
                    });
                }
            @endif
        });
    </script>
@endsection
