@extends('layouts.admin')

@php
    $authUser = auth('staff')->user();
    $autresCars = array_filter($listeCars, fn ($car) => $car['id_car_programmer'] != $idCar);
@endphp

@section('title', 'Détails de l\'envoi · TransHub Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="bx bx-package me-1"></i> G-colis</span>
@endsection
@section('breadcrumb-active')
    Colis envoyés pour le car N° {{ $idCar }} le {{ \Illuminate\Support\Carbon::parse($dateEnvoi)->format('d/m/Y à H:i') }}
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
                                        <button type="button" class="btn btn-sm btn-outline-primary changer-car-btn"
                                            data-bs-toggle="modal" data-bs-target="#modalChangerCar"
                                            data-id-colis="{{ $colis->id_colis }}"
                                            data-nom-colis="{{ $colis->nom_colis }}">
                                            <i class="bx bx-transfer-alt me-1"></i> Changer de car
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

    <!-- Modal changer de car -->
    <div class="modal fade" id="modalChangerCar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Changer le car d'envoi</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.colis.envoi.changer-car') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <p>Colis : <strong id="modalNomColis"></strong></p>
                        <input type="hidden" name="id_colis" id="modalIdColis">
                        <input type="hidden" name="ancien_id_car" value="{{ $idCar }}">
                        <input type="hidden" name="ancienne_date" value="{{ $dateEnvoi }}">

                        @if (empty($autresCars))
                            <div class="alert alert-warning mb-0">
                                <i class="bx bx-error me-1"></i>
                                Aucun autre car programmé aujourd'hui. Activez et programmez un autre car
                                (menus <em>Cars &amp; chauffeurs</em> et <em>Programmation des voyages</em>)
                                pour pouvoir réaffecter ce colis.
                            </div>
                        @else
                            <label class="form-label fw-semibold">Nouveau car</label>
                            <select class="form-select" name="nouveau_id_car" required>
                                <option value="" disabled selected>Choisir un car</option>
                                @foreach ($autresCars as $car)
                                    <option value="{{ $car['id_car_programmer'] }}">
                                        Car N°{{ $car['id_car_programmer'] }} —
                                        Départ: {{ $car['id_horaire'] }} —
                                        Destination: {{ $car['id_trajet'] }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" {{ empty($autresCars) ? 'disabled' : '' }}>Confirmer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.changer-car-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('modalIdColis').value = this.getAttribute('data-id-colis');
                document.getElementById('modalNomColis').textContent = this.getAttribute('data-nom-colis');
            });
        });
    </script>
@endsection
