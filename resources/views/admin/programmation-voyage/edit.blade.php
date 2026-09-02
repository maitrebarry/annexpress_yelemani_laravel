@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Modifier une programmation · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-calendar-check me-1"></i> G-programme</span>
@endsection
@section('breadcrumb-active', 'Modifier une programmation')

@section('breadcrumb-actions')
    <a href="{{ route('admin.programmation-voyage.liste-journaliere') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-list-ul me-1"></i> Voir la liste
    </a>
@endsection

@section('content')


    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-bus me-1"></i> Modification de la programmation — Car {{ $programmation->numero_car }}
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.programmation-voyage.update', $programmation->id_programmation) }}">
                @csrf

                @if (! empty($besoinChoix))
                    <div class="alert alert-warning shadow-sm">
                        <strong>{{ $besoinChoix['count'] }} réservation(s)</strong> existent déjà sur le créneau
                        actuel de ce car ({{ $programmation->localite_user }} → {{ $programmation->id_trajet }} à
                        {{ substr($programmation->id_horaire, 0, 5) }}). Que voulez-vous faire de ces réservations ?
                        <div class="mt-2">
                            @unless ($besoinChoix['destination_change'])
                                <label class="d-block">
                                    <input type="radio" name="action_reservations" value="suivre" required>
                                    Faire suivre ces billets vers le nouveau créneau (mêmes clients, même car, nouvelle heure)
                                </label>
                            @endunless
                            <label class="d-block">
                                <input type="radio" name="action_reservations" value="nouveau_car" {{ $besoinChoix['destination_change'] ? 'checked' : '' }} required>
                                Garder ces clients sur le créneau actuel : un autre car le reprend
                            </label>
                        </div>
                        <div class="mt-2" id="carRemplacementBox" style="display:none;">
                            <label class="form-label mb-1">Car de remplacement pour l'ancien créneau</label>
                            <select class="form-select" name="id_car_remplacement" style="max-width:280px;">
                                <option value="">Choisir un car</option>
                                @foreach (($carsRemplacement ?? []) as $c)
                                    <option value="{{ $c->id_car }}">{{ $c->numero_car }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm rounded">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Numéro Car</th>
                                <th>Horaire</th>
                                <th>Destination</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <tr>
                                <td>
                                    <input type="text" class="form-control text-center shadow-sm" value="{{ $programmation->numero_car }}" readonly>
                                </td>
                                <td>
                                    <select class="form-select shadow-sm" id="selectHoraireEdit" name="id_horaire" required>
                                        <option value="" disabled selected>—</option>
                                    </select>
                                </td>
                                <td>
                                    @php
                                        $destinationActuelle = $destinationSoumise ?? $programmation->id_trajet;
                                        $horaireActuel = $horaireSoumis ?? $programmation->id_horaire;
                                    @endphp
                                    <select class="form-select shadow-sm" id="selectDestinationEdit" name="id_destination" required>
                                        <option value="" disabled>Choisir une destination</option>
                                        @foreach ($destinations as $d)
                                            <option value="{{ $d->destinationLocalite }}" data-heure="{{ $d->heureDepart }}"
                                                {{ ($d->destinationLocalite == $destinationActuelle && $d->heureDepart == $horaireActuel) ? 'selected' : '' }}>
                                                {{ $d->departLocalite }} -&gt; {{ $d->destinationLocalite }} ({{ substr($d->heureDepart, 0, 5) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
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

@endsection

@section('scripts')
    <script>
        (function () {
            var selectDestination = document.getElementById('selectDestinationEdit');
            var selectHoraire = document.getElementById('selectHoraireEdit');
            if (!selectDestination || !selectHoraire) return;

            function majHoraire() {
                var opt = selectDestination.options[selectDestination.selectedIndex];
                var heure = opt?.getAttribute('data-heure') || '';
                selectHoraire.innerHTML = heure
                    ? '<option value="' + heure + '" selected>' + heure.slice(0, 5) + '</option>'
                    : '<option value="" disabled selected>—</option>';
            }

            selectDestination.addEventListener('change', majHoraire);
            majHoraire();
        })();

        (function () {
            var radios = document.querySelectorAll('input[name="action_reservations"]');
            var box = document.getElementById('carRemplacementBox');
            if (!radios.length || !box) return;

            function majAffichage() {
                var choisi = document.querySelector('input[name="action_reservations"]:checked');
                box.style.display = (choisi && choisi.value === 'nouveau_car') ? 'block' : 'none';
            }

            radios.forEach(function (r) { r.addEventListener('change', majAffichage); });
            majAffichage();
        })();
    </script>
@endsection
