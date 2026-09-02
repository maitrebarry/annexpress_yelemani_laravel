@extends('layouts.admin')

@php
    $authUser = auth('staff')->user();
    $aujourdhui = now()->toDateString();
@endphp

@section('title', 'Location des cars · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-car me-1"></i> Finances</span>
@endsection
@section('breadcrumb-active', 'Location des cars')

@section('breadcrumb-actions')
    @if ($authUser->droit !== 'PDG')
        <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNouvelleLocation">
            <i class="fas fa-plus me-1"></i> Nouvelle location
        </button>
    @endif
@endsection

@section('content')


    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-4 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;">
            <i class="fas fa-list-ul fs-4"></i>
            <span class="fw-semibold fs-5">Historique des locations</span>
        </div>
        <div class="table-responsive">
            @php
                $theadStyle = 'background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;';
            @endphp
            <table id="example" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="border-0" style="{{ $theadStyle }}">Gare départ</th>
                        <th class="border-0" style="{{ $theadStyle }}">Destination</th>
                        <th class="border-0" style="{{ $theadStyle }}">Car</th>
                        <th class="border-0" style="{{ $theadStyle }}">Client</th>
                        <th class="border-0" style="{{ $theadStyle }}">Période</th>
                        <th class="border-0" style="{{ $theadStyle }}">Frais</th>
                        <th class="border-0" style="{{ $theadStyle }}">Statut</th>
                        <th class="border-0" style="{{ $theadStyle }}">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listeLocations as $l)
                        <tr>
                            <td>{{ $l->localite ?? '-' }} ({{ $l->numeroGare ?? '-' }})</td>
                            <td>{{ $l->destination }}</td>
                            <td>N°{{ $l->numero_car ?? '-' }} <span class="text-muted small">{{ $l->matriculle ?? '' }}</span></td>
                            <td>{{ $l->prenom_client }} {{ $l->nom_client }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($l->date_depart)->format('d/m/Y') }} → {{ \Illuminate\Support\Carbon::parse($l->date_retour_prevu)->format('d/m/Y') }}</td>
                            <td class="fw-bold text-success">{{ number_format($l->frais_location, 0, ',', ' ') }} F</td>
                            <td>
                                @if ($l->statut === 'valide')
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Validée</span>
                                @elseif ($l->statut === 'en_attente')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>En attente</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-xmark me-1"></i>Rejetée</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalDetails{{ $l->id_location }}" title="Détails">
                                        <i class="fas fa-circle-info"></i>
                                    </button>
                                    <a href="{{ route('admin.location-car.facture', $l->id_location) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" title="Facture">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if ($authUser->droit === 'Admin' && $l->statut === 'en_attente')
                                        <form method="post" action="{{ route('admin.location-car.valider', $l->id_location) }}" class="location-action-form">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-success py-0 px-2 location-valider-btn"
                                                data-destination="{{ $l->destination }}" data-frais="{{ number_format($l->frais_location, 0, ',', ' ') }}" title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="post" action="{{ route('admin.location-car.rejeter', $l->id_location) }}" class="location-action-form">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-danger py-0 px-2 location-rejeter-btn"
                                                data-destination="{{ $l->destination }}" data-frais="{{ number_format($l->frais_location, 0, ',', ' ') }}" title="Rejeter">
                                                <i class="fas fa-xmark"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('modals')

    @if ($authUser->droit !== 'PDG')
        <div class="modal fade" id="modalNouvelleLocation" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                    <form method="post" action="{{ route('admin.location-car.store') }}" id="formLocation">
                        @csrf
                        <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                            <h5 class="modal-title text-white d-flex align-items-center gap-2">
                                <i class="fas fa-car"></i> Nouvelle location de car
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-4">
                                <div class="col-12 col-xl-8">
                                    <div class="row g-3">
                                        @if ($authUser->droit === 'Admin')
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Gare de départ</label>
                                                <select class="form-select" id="id_agence_depart" name="id_agence_depart" required>
                                                    <option value="" disabled selected>Choisir la gare</option>
                                                    @foreach ($listeAgences as $agence)
                                                        <option value="{{ $agence->idAgence }}">{{ $agence->localite }} ({{ $agence->numeroGare }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @else
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Gare de départ</label>
                                                <input type="text" class="form-control" disabled value="{{ ($authUser->agence->localite ?? '-').' ('.($authUser->agence->numeroGare ?? '-').')' }}">
                                            </div>
                                        @endif

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Destination</label>
                                            <input type="text" class="form-control" name="destination" id="destination" placeholder="Ville ou lieu" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Date de départ</label>
                                            <input type="date" class="form-control" id="date_depart" name="date_depart" value="{{ $aujourdhui }}" min="{{ $aujourdhui }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Date de retour prévu</label>
                                            <input type="date" class="form-control" id="date_retour_prevu" name="date_retour_prevu" value="{{ $aujourdhui }}" min="{{ $aujourdhui }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Car <span id="carsDispoBadge" class="badge bg-light text-muted"></span></label>
                                            <select class="form-select" id="id_car" name="id_car" required disabled>
                                                <option value="" selected>Choisissez d'abord la gare et les dates</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Frais de location</label>
                                            <div class="input-group">
                                                <input type="number" min="1" class="form-control" name="frais_location" id="frais_location" required>
                                                <span class="input-group-text">FCFA</span>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Nom du client</label>
                                            <input type="text" class="form-control" name="nom_client" id="nom_client" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Prénom du client</label>
                                            <input type="text" class="form-control" name="prenom_client" id="prenom_client" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Téléphone du client</label>
                                            <input type="text" class="form-control" name="telephone_client" id="telephone_client" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-xl-4">
                                    <div class="card border-0 shadow-sm rounded-4 h-100">
                                        <div class="card-header bg-white border-0 pt-3 px-4">
                                            <span class="fw-semibold text-muted text-uppercase small"><i class="fas fa-receipt me-1"></i> Résumé</span>
                                        </div>
                                        <div class="card-body px-4 pb-4">
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span class="text-muted small">Destination</span>
                                                <span class="fw-semibold" id="resumeDestination">—</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span class="text-muted small">Car</span>
                                                <span class="fw-semibold" id="resumeCar">—</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span class="text-muted small">Période</span>
                                                <span class="fw-semibold small" id="resumePeriode">—</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span class="text-muted small">Client</span>
                                                <span class="fw-semibold" id="resumeClient">—</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center pt-3">
                                                <span class="fw-bold">Frais</span>
                                                <span class="fs-4 fw-bold text-success" id="resumeFrais">0 F</span>
                                            </div>
                                            <div class="mt-3">
                                                <span class="badge {{ in_array($authUser->droit, ['Admin', 'super_admin'], true) ? 'bg-success' : 'bg-warning text-dark' }} w-100 py-2">
                                                    @if (in_array($authUser->droit, ['Admin', 'super_admin'], true))
                                                        <i class="fas fa-circle-check me-1"></i> Créditée immédiatement à la caisse
                                                    @else
                                                        <i class="fas fa-clock me-1"></i> Sera en attente de validation Admin
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success px-4"><i class="fas fa-floppy-disk me-1"></i> Enregistrer la location</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @foreach ($listeLocations as $l)
        <div class="modal fade" id="modalDetails{{ $l->id_location }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                    <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                        <h5 class="modal-title text-white d-flex align-items-center gap-2">
                            <i class="fas fa-car"></i> Location vers {{ $l->destination }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Gare de départ</span>
                            <span class="fw-semibold">{{ $l->localite ?? '-' }} ({{ $l->numeroGare ?? '-' }})</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Car</span>
                            <span class="fw-semibold">N°{{ $l->numero_car ?? '-' }} - {{ $l->matriculle ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Période</span>
                            <span class="fw-semibold">{{ \Illuminate\Support\Carbon::parse($l->date_depart)->format('d/m/Y') }} → {{ \Illuminate\Support\Carbon::parse($l->date_retour_prevu)->format('d/m/Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Client</span>
                            <span class="fw-semibold">{{ $l->prenom_client }} {{ $l->nom_client }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Téléphone</span>
                            <span class="fw-semibold">{{ $l->telephone_client }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Enregistrée par</span>
                            <span class="fw-semibold">{{ $l->agent ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-3">
                            <span class="fw-bold">Frais de location</span>
                            <span class="fs-4 fw-bold text-success">{{ number_format($l->frais_location, 0, ',', ' ') }} F</span>
                        </div>
                        <div class="mt-3 text-center">
                            @if ($l->statut === 'valide')
                                <span class="badge bg-success py-2 px-3"><i class="fas fa-check me-1"></i>Validée</span>
                            @elseif ($l->statut === 'en_attente')
                                <span class="badge bg-warning text-dark py-2 px-3"><i class="fas fa-clock me-1"></i>En attente de validation</span>
                            @else
                                <span class="badge bg-danger py-2 px-3"><i class="fas fa-xmark me-1"></i>Rejetée</span>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <a href="{{ route('admin.location-car.facture', $l->id_location) }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-print me-1"></i> Imprimer la facture
                        </a>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection

@section('scripts')
    <script>
        (function () {
            const idAgenceDepartInput = document.getElementById('id_agence_depart');
            const dateDepartInput = document.getElementById('date_depart');
            const dateRetourInput = document.getElementById('date_retour_prevu');
            const idCarSelect = document.getElementById('id_car');
            const carsDispoBadge = document.getElementById('carsDispoBadge');
            const idAgenceDepartFixe = {{ (int) ($authUser->id_agence ?? 0) }};
            const isAdmin = {{ $authUser->droit === 'Admin' ? 'true' : 'false' }};

            // La modale "Nouvelle location" n'est pas rendue pour un PDG (lecture seule) :
            // ce script s'arrête ici dans ce cas, tout le reste ci-dessous suppose la modale présente.
            if (!dateDepartInput) return;

            function rafraichirCarsDisponibles() {
                const id_agence_depart = isAdmin ? (idAgenceDepartInput ? idAgenceDepartInput.value : '') : idAgenceDepartFixe;
                const date_depart = dateDepartInput.value;
                const date_retour_prevu = dateRetourInput.value;

                idCarSelect.innerHTML = '<option value="">Chargement...</option>';
                idCarSelect.disabled = true;
                carsDispoBadge.textContent = '';
                carsDispoBadge.className = 'badge bg-light text-muted';

                if (!id_agence_depart || !date_depart || !date_retour_prevu) {
                    idCarSelect.innerHTML = '<option value="">Choisissez d\'abord la gare et les dates</option>';
                    return;
                }

                fetch('{{ route('admin.location-car.ajax-cars-disponibles') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: new URLSearchParams({ id_agence_depart, date_depart, date_retour_prevu }),
                })
                    .then(r => r.json())
                    .then(response => {
                        if (response.error) {
                            idCarSelect.innerHTML = '<option value="">' + response.error + '</option>';
                            carsDispoBadge.textContent = 'Erreur';
                            carsDispoBadge.className = 'badge bg-danger';
                            return;
                        }
                        if (!response.cars || response.cars.length === 0) {
                            idCarSelect.innerHTML = '<option value="">Aucun car disponible sur cette période</option>';
                            carsDispoBadge.textContent = '0 disponible';
                            carsDispoBadge.className = 'badge bg-danger';
                            return;
                        }
                        let options = '<option value="" disabled selected>Choisir un car</option>';
                        response.cars.forEach(function (c) {
                            options += `<option value="${c.id_car}" data-numero="${c.numero_car}" data-matriculle="${c.matriculle}">Car n°${c.numero_car} - ${c.matriculle}</option>`;
                        });
                        idCarSelect.innerHTML = options;
                        idCarSelect.disabled = false;
                        carsDispoBadge.textContent = response.cars.length + ' disponible' + (response.cars.length > 1 ? 's' : '');
                        carsDispoBadge.className = 'badge bg-success';
                    })
                    .catch(() => {
                        idCarSelect.innerHTML = '<option value="">Erreur lors du chargement des cars</option>';
                        carsDispoBadge.textContent = 'Erreur';
                        carsDispoBadge.className = 'badge bg-danger';
                    });
            }

            idAgenceDepartInput?.addEventListener('change', rafraichirCarsDisponibles);
            dateDepartInput?.addEventListener('change', function () {
                if (dateRetourInput.value < dateDepartInput.value) {
                    dateRetourInput.value = dateDepartInput.value;
                }
                dateRetourInput.min = dateDepartInput.value;
                rafraichirCarsDisponibles();
            });
            dateRetourInput?.addEventListener('change', rafraichirCarsDisponibles);

            document.getElementById('modalNouvelleLocation')?.addEventListener('shown.bs.modal', rafraichirCarsDisponibles);

            // Résumé en direct
            const resumeDestination = document.getElementById('resumeDestination');
            const resumeCar = document.getElementById('resumeCar');
            const resumePeriode = document.getElementById('resumePeriode');
            const resumeClient = document.getElementById('resumeClient');
            const resumeFrais = document.getElementById('resumeFrais');
            const destinationInput = document.getElementById('destination');
            const fraisInput = document.getElementById('frais_location');
            const nomInput = document.getElementById('nom_client');
            const prenomInput = document.getElementById('prenom_client');

            function majResume() {
                resumeDestination.textContent = destinationInput.value || '—';
                const opt = idCarSelect.options[idCarSelect.selectedIndex];
                resumeCar.textContent = (opt && opt.dataset && opt.dataset.numero) ? ('N°' + opt.dataset.numero + ' - ' + opt.dataset.matriculle) : '—';
                resumePeriode.textContent = (dateDepartInput.value && dateRetourInput.value)
                    ? (dateDepartInput.value + ' → ' + dateRetourInput.value) : '—';
                const client = [prenomInput.value, nomInput.value].filter(Boolean).join(' ');
                resumeClient.textContent = client || '—';
                const frais = parseInt(fraisInput.value, 10);
                resumeFrais.textContent = (frais > 0 ? frais.toLocaleString('fr-FR') : '0') + ' F';
            }

            [destinationInput, fraisInput, nomInput, prenomInput, dateDepartInput, dateRetourInput].forEach(el => el?.addEventListener('input', majResume));
            idCarSelect.addEventListener('change', majResume);

            // Valider / Rejeter, sweetalert2 stylé
            document.querySelectorAll('.location-valider-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const form = btn.closest('form');
                    Swal.fire({
                        title: 'Valider la location ?',
                        html: `Valider la location vers "<strong>${btn.dataset.destination}</strong>" de <strong>${btn.dataset.frais} FCFA</strong> ?<br>Elle sera créditée à la caisse.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-check"></i> Oui, valider',
                        cancelButtonText: 'Annuler',
                        customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-secondary' },
                        buttonsStyling: false,
                    }).then((result) => { if (result.isConfirmed) form.submit(); });
                });
            });

            document.querySelectorAll('.location-rejeter-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const form = btn.closest('form');
                    Swal.fire({
                        title: 'Rejeter la location ?',
                        html: `Rejeter la location vers "<strong>${btn.dataset.destination}</strong>" de <strong>${btn.dataset.frais} FCFA</strong> ?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-xmark"></i> Oui, rejeter',
                        cancelButtonText: 'Annuler',
                        customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-secondary' },
                        buttonsStyling: false,
                    }).then((result) => { if (result.isConfirmed) form.submit(); });
                });
            });
        })();
    </script>
@endsection
