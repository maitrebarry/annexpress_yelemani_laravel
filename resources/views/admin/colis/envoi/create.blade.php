@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Ajouter des colis à envoyer · TransHub Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="bx bx-package me-1"></i> G-colis</span>
@endsection
@section('breadcrumb-active', 'Ajouter des colis à envoyer')

@section('breadcrumb-actions')
    <a href="{{ route('admin.colis.envoi.index') }}" class="btn btn-sm btn-success rounded-pill shadow-sm">
        <i class="bx bx-list-ul me-1"></i> Voir la liste
    </a>
@endsection

@section('content')

    @include('admin.partials.set_flash')

    <div class="card shadow-sm rounded-3">
        <form action="{{ route('admin.colis.envoi.store') }}" method="post">
            @csrf
            <div class="card-body border-top border-4 border-primary">

                <!-- Sélection du car -->
                <div class="mb-4 col-12 col-md-4">
                    <label for="id_car_selectionner" class="form-label fw-bold">Sélectionner un car</label>
                    <select id="id_car_selectionner" name="id_car_selectionner" class="form-select shadow-sm">
                        <option value="">-- Choisir un car --</option>
                        @foreach ($listeCars as $car)
                            <option value="{{ $car['id_car_programmer'] }}"
                                {{ $carSelectionne && $car['id_car_programmer'] == $carSelectionne->id_car_programmer ? 'selected' : '' }}>
                                Car N°{{ $car['id_car_programmer'] }} —
                                Départ: {{ $car['id_horaire'] }} —
                                Destination: {{ $car['id_trajet'] }}
                            </option>
                        @endforeach
                    </select>
                    @if ($carSelectionne)
                        <div class="form-text text-success">
                            <i class="bx bx-check-circle"></i> Car N°{{ $carSelectionne->id_car_programmer }} présélectionné (départ {{ $carSelectionne->id_horaire }} vers {{ $carSelectionne->id_trajet }}).
                        </div>
                    @endif
                </div>

                <!-- Table des colis -->
                <table class="table table-hover table-striped table-bordered align-middle shadow-sm">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>@if ($authUser->droit !== 'PDG')<input type="checkbox" id="selectAll">@endif</th>
                            <th>Nom colis</th>
                            <th>Nature</th>
                            <th>Valeur</th>
                            <th>Fraix de transaction</th>
                            <th>Destination</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach ($listeColis as $colis)
                            @continue($colis->status !== 'enregistre')
                            <tr>
                                <td>@if ($authUser->droit !== 'PDG')<input type="checkbox" name="selected_colis[]" class="checkbox-car" value="{{ $colis->id_colis }}">@endif</td>
                                <td>{{ $colis->nom_colis }}</td>
                                <td>{{ $colis->nature }}</td>
                                <td>{{ $colis->valeur }}</td>
                                <td>{{ $colis->fraix_transaction }}</td>
                                <td>{{ $colis->destination }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Bouton enregistrer -->
                @if ($authUser->droit !== 'PDG')
                    <div class="mt-4 text-end">
                        <button class="btn btn-success rounded-pill shadow-sm px-4" type="submit" name="submit">
                            <i class="bx bx-save me-1"></i> Enregistrer
                        </button>
                    </div>
                @endif
            </div>
        </form>
    </div>

@endsection

@section('scripts')
    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.checkbox-car').forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
        });
    </script>
@endsection
