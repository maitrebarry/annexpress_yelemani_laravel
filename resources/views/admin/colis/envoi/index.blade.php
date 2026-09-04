@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Liste des colis envoyés · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-box-open me-1"></i> G-colis</span>
@endsection
@section('breadcrumb-active', 'Liste des colis envoyés')

@section('breadcrumb-actions')
    <a href="{{ route('admin.colis.envoi.create') }}" class="btn btn-sm btn-success rounded-pill shadow-sm">
        <i class="fas fa-paper-plane me-1"></i> Envoyer un colis
    </a>
@endsection

@section('content')


    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-hover table-striped table-bordered align-middle shadow-sm">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Date d'envoi</th>
                            <th>Véhicule</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach ($listeColisEnvoyer as $colis)
                            @php
                                $estCamion = $colis->type_vehicule === 'camion';
                            @endphp
                            <tr>
                                <td>{{ $colis->dates }}</td>
                                <td>
                                    @if ($estCamion)
                                        <span class="badge bg-info-subtle text-info-emphasis"><i class="fas fa-truck me-1"></i>Camion n°{{ $colis->id_vehicule }}</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary-emphasis"><i class="fas fa-bus me-1"></i>Car n°{{ $colis->id_vehicule }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            @if ($authUser->droit !== 'PDG' && ! $estCamion)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.colis.envoi.create', ['id_car' => $colis->id_vehicule]) }}">
                                                        <i class="fas fa-plus me-2"></i> Ajouter
                                                    </a>
                                                </li>
                                            @endif
                                            @if ($authUser->droit !== 'PDG' && $estCamion)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.colis.envoi.create', ['id_camion' => $colis->id_vehicule]) }}">
                                                        <i class="fas fa-plus me-2"></i> Ajouter
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                {{-- route(..., [...]) URL-encode chaque valeur (nécessaire : $colis->dates
                                                     contient un espace, ex. "2026-09-04 13:43:46"). --}}
                                                <a class="dropdown-item" href="{{ route('admin.colis.envoi.details', array_filter(['id_vehicule' => $colis->id_vehicule, 'date' => $colis->dates, 'type' => $estCamion ? 'camion' : null])) }}">
                                                    <i class="fas fa-circle-info me-2"></i> Détails / Changer de véhicule
                                                </a>
                                            </li>
                                            @if ($authUser->droit !== 'PDG')
                                                <li>
                                                    <a class="dropdown-item text-danger annuler-envoi-btn"
                                                        href="{{ route('admin.colis.envoi.annuler', array_filter(['id_vehicule' => $colis->id_vehicule, 'date' => $colis->dates, 'type' => $estCamion ? 'camion' : null])) }}">
                                                        <i class="fas fa-trash me-2"></i> Annuler l'envoi
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.annuler-envoi-btn').forEach(function(btn) {
            btn.addEventListener('click', function(event) {
                event.preventDefault();
                const url = this.getAttribute('href');
                Swal.fire({
                    title: "Annuler cet envoi ?",
                    text: "Les colis de ce lot redeviendront disponibles pour un nouvel envoi.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, annuler',
                    cancelButtonText: 'Retour',
                    customClass: {
                        confirmButton: 'btn btn-danger me-2',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
@endsection
