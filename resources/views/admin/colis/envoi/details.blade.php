@extends('layouts.admin')

@php
    $authUser = auth('staff')->user();
    $estCamion = $typeVehicule === 'camion';
    // Les deux listes n'ont pas la même forme de clé d'id selon le type (voir
    // EnvoiColisService::getCarsDisponiblesAujourdhui() vs getCamionsActifs()) — voir
    // GESTION_CAMIONS_COLIS.md.
    $autresVehicules = array_filter($listeVehicules, fn ($v) => $estCamion
        ? $v['id_camion'] != $idVehicule
        : $v['id_car_programmer'] != $idVehicule);
@endphp

@section('title', 'Détails de l\'envoi · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-box-open me-1"></i> G-colis</span>
@endsection
@section('breadcrumb-active')
    Colis envoyés pour {{ $estCamion ? 'le camion' : 'le car' }} N° {{ $idVehicule }} le {{ \Illuminate\Support\Carbon::parse($dateEnvoi)->format('d/m/Y à H:i') }}
@endsection

@section('content')

    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-hover table-bordered align-middle shadow-sm">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Nom du colis</th>
                            <th>Nature</th>
                            <th>Valeur</th>
                            <th>Frais de transaction</th>
                            <th>Date d'envoi</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse ($listeColis as $colis)
                            <tr>
                                <td>{{ $colis->nom_colis }}</td>
                                <td>{{ $colis->nature }}</td>
                                <td>{{ $colis->valeur }}</td>
                                <td>{{ $colis->fraix_transaction }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($colis->date_enregistre)->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if ($authUser->droit !== 'PDG')
                                        <button type="button" class="btn btn-sm btn-outline-primary changer-vehicule-btn"
                                            data-bs-toggle="modal" data-bs-target="#modalChangerVehicule"
                                            data-id-colis="{{ $colis->id_colis }}"
                                            data-nom-colis="{{ $colis->nom_colis }}">
                                            <i class="fas fa-arrow-right-arrow-left me-1"></i> Changer de {{ $estCamion ? 'camion' : 'car' }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted py-4">Aucun colis dans cet envoi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal changer de véhicule (intra-type : car -> car, ou camion -> camion) -->
    <div class="modal fade" id="modalChangerVehicule" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Changer le {{ $estCamion ? 'camion' : 'car' }} d'envoi</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ $estCamion ? route('admin.colis.envoi.changer-camion') : route('admin.colis.envoi.changer-car') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <p>Colis : <strong id="modalNomColis"></strong></p>
                        <input type="hidden" name="id_colis" id="modalIdColis">
                        @if ($estCamion)
                            <input type="hidden" name="ancien_id_camion" value="{{ $idVehicule }}">
                        @else
                            <input type="hidden" name="ancien_id_car" value="{{ $idVehicule }}">
                        @endif
                        <input type="hidden" name="ancienne_date" value="{{ $dateEnvoi }}">

                        @if (empty($autresVehicules))
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-triangle-exclamation me-1"></i>
                                @if ($estCamion)
                                    Aucun autre camion actif. Activez un autre camion (menu <em>Cars &amp; camions &amp; chauffeurs</em>)
                                    pour pouvoir réaffecter ce colis.
                                @else
                                    Aucun autre car programmé aujourd'hui. Activez et programmez un autre car
                                    (menus <em>Cars &amp; camions &amp; chauffeurs</em> et <em>Trajets programmés</em>)
                                    pour pouvoir réaffecter ce colis.
                                @endif
                            </div>
                        @else
                            <label class="form-label fw-semibold">Nouveau {{ $estCamion ? 'camion' : 'car' }}</label>
                            @if ($estCamion)
                                <select class="form-select" name="nouveau_id_camion" required>
                                    <option value="" disabled selected>Choisir un camion</option>
                                    @foreach ($autresVehicules as $camion)
                                        <option value="{{ $camion['id_camion'] }}">
                                            Camion N°{{ $camion['numero_camion'] }} — {{ $camion['matriculle'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <select class="form-select" name="nouveau_id_car" required>
                                    <option value="" disabled selected>Choisir un car</option>
                                    @foreach ($autresVehicules as $car)
                                        <option value="{{ $car['id_car_programmer'] }}">
                                            Car N°{{ $car['id_car_programmer'] }} —
                                            Départ: {{ $car['id_horaire'] }} —
                                            Destination: {{ $car['id_trajet'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" {{ empty($autresVehicules) ? 'disabled' : '' }}>Confirmer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.changer-vehicule-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('modalIdColis').value = this.getAttribute('data-id-colis');
                document.getElementById('modalNomColis').textContent = this.getAttribute('data-nom-colis');
            });
        });
    </script>
@endsection
