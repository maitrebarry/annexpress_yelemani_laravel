@extends('layouts.admin')

@section('title', 'État de la flotte · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-location-dot me-1"></i> G-programme</span>
@endsection
@section('breadcrumb-active', 'Où se trouvent les cars')

@section('breadcrumb-actions')
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
@endsection

@section('content')


    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-bus me-1"></i> État actuel de tous les cars
        </div>
        <div class="card-body">
            @if ($cars->isEmpty())
                <p class="text-muted mb-0">Aucun car trouvé pour votre compagnie.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm rounded">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Numéro Car</th>
                                <th>Matricule</th>
                                <th>Places</th>
                                <th>État</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @foreach ($cars as $car)
                                @php
                                    $enTransit = ! empty($car->status_car) && str_starts_with($car->status_car, 'En_transit_');

                                    if (empty($car->status_car)) {
                                        $badge = 'secondary';
                                        $etat = 'Position inconnue';
                                    } elseif (! $enTransit) {
                                        $badge = 'success';
                                        $etat = 'Disponible à '.$car->status_car;
                                    } elseif (empty($car->id_programmation)) {
                                        $badge = 'danger';
                                        $etat = 'Anomalie — voyage actif introuvable';
                                    } elseif (empty($car->decolle_le)) {
                                        $badge = 'warning text-dark';
                                        $etat = 'Embarquement en cours';
                                    } else {
                                        $badge = 'info';
                                        $etat = 'En route';
                                    }
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $car->numero_car }}</td>
                                    <td>{{ $car->matriculle }}</td>
                                    <td>{{ $car->nbr_place }}</td>
                                    <td><span class="badge bg-{{ $badge }}">{{ $etat }}</span></td>
                                    <td class="text-start">
                                        @if ($enTransit && ! empty($car->id_programmation))
                                            <div>
                                                {{ $car->origine }}
                                                <i class="fas fa-arrow-right mx-1"></i>
                                                {{ $car->destination }}
                                                @if (! empty($car->numeroGareDestination))
                                                    <span class="text-muted">(gare {{ $car->numeroGareDestination }})</span>
                                                @endif
                                            </div>
                                            @if (! empty($car->decolle_le))
                                                @php
                                                    $minutes = max(0, floor((time() - strtotime($car->decolle_le)) / 60));
                                                    $heures = intdiv($minutes, 60);
                                                    $reste = $minutes % 60;
                                                @endphp
                                                <small class="text-muted">Parti depuis {{ $heures > 0 ? $heures.'h ' : '' }}{{ $reste }}min</small>
                                            @else
                                                <small class="text-muted">Programmé le {{ \Illuminate\Support\Carbon::parse($car->date_enregistre)->format('d/m/Y') }}</small>
                                            @endif
                                        @elseif ($enTransit)
                                            <span class="text-muted">
                                                Marqué « {{ $car->status_car }} » mais aucune programmation active correspondante.
                                                Voir <a href="{{ route('admin.programmation-voyage.dashboard') }}">Cars bloqués</a> pour corriger.
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection
