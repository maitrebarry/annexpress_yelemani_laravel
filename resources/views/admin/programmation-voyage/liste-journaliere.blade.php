@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Trajets programmés · TransHub Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="bx bx-calendar-check me-1"></i> G-programme</span>
@endsection
@section('breadcrumb-active', 'Trajets programmés')

@section('breadcrumb-actions')
    @unless ($authUser->estLectureSeule())
        <a href="{{ route('admin.programmation-voyage.dashboard') }}" class="btn btn-sm btn-success rounded-pill shadow-sm">
            <i class="bx bx-plus-circle me-1"></i> Ajouter
        </a>
    @endunless
@endsection

@section('content')

    @include('admin.partials.set_flash')

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="bx bx-bus me-1"></i> Liste des programmations du jour
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Numéro de car</th>
                            @if ($authUser->droit === 'Admin')
                                <th>Gare</th>
                            @endif
                            <th>Horaire</th>
                            <th>Destination</th>
                            <th>Place disponible</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($liste as $p)
                            <tr>
                                <td data-label="Numéro de car">{{ $p->numero_car }}</td>
                                @if ($authUser->droit === 'Admin')
                                    <td data-label="Gare">{{ $p->localite_user }}</td>
                                @endif
                                <td data-label="Horaire">{{ substr($p->id_horaire, 0, 5) }}</td>
                                <td data-label="Destination">
                                    {{ $p->id_trajet }}
                                    @if ($p->numeroGareDestination)
                                        <span class="text-muted">(Gare {{ $p->numeroGareDestination }})</span>
                                    @endif
                                </td>
                                <td data-label="Place disponible" class="fw-bold text-success">{{ $p->place_disponible }}</td>
                                <td data-label="Action">
                                    @unless ($authUser->estLectureSeule())
                                        <div class="dropup text-center">
                                            <a href="#" class="text-dark text-decoration-none fs-4" data-bs-toggle="dropdown" aria-expanded="false">&#8943;</a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.programmation-voyage.edit', $p->id_programmation) }}">
                                                    <i class="bx bx-edit me-2"></i>Modifier
                                                </a>
                                                @if ((int) $p->place_disponible > 0)
                                                    <a class="dropdown-item transfer-btn" href="javascript:;"
                                                        data-bs-toggle="modal" data-bs-target="#modalTransfert"
                                                        data-id-programmation="{{ $p->id_programmation }}">
                                                        <i class="bx bx-transfer me-2"></i>Transférer les passagers
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $authUser->droit === 'Admin' ? 6 : 5 }}" class="text-muted fst-italic">Aucune programmation aujourd'hui.</td>
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
        <div class="modal fade" id="modalTransfert" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">Transférer les passagers</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="transfertLoading" class="text-center text-muted py-3">Recherche des gares compatibles...</div>
                        <div id="transfertAucune" class="alert alert-warning" style="display:none;">
                            Aucune gare compatible n'est disponible pour l'instant (même destination, même heure, même jour, même localité).
                        </div>
                        <div id="transfertContenu" style="display:none;">
                            <p class="text-muted">Le transfert n'a lieu que si la gare choisie peut accueillir <strong>la totalité</strong> des passagers de votre gare. Votre voyage sera alors annulé et la recette transférée vers la caisse de la gare choisie.</p>
                            <div id="transfertListeGares"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endunless
@endsection

@section('scripts')
    @unless ($authUser->estLectureSeule())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var csrfToken = @json(csrf_token());

                document.querySelectorAll('.transfer-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var idProgrammation = this.dataset.idProgrammation;

                        var loading = document.getElementById('transfertLoading');
                        var aucune = document.getElementById('transfertAucune');
                        var contenu = document.getElementById('transfertContenu');
                        var liste = document.getElementById('transfertListeGares');

                        loading.style.display = '';
                        contenu.style.display = 'none';
                        aucune.style.display = 'none';
                        liste.innerHTML = '';

                        fetch('{{ url('/admin/Transferts_gares/candidats') }}/' + idProgrammation)
                            .then(function (r) { return r.json(); })
                            .then(function (response) {
                                loading.style.display = 'none';

                                if (response.error || !response.gares || response.gares.length === 0) {
                                    aucune.style.display = '';
                                    return;
                                }

                                response.gares.forEach(function (g) {
                                    var a = g.apercu;
                                    var possible = a && a.possible;
                                    var montant = a ? Number(a.montant_total).toLocaleString('fr-FR') : '0';

                                    var card = document.createElement('div');
                                    card.className = 'card mb-2';
                                    card.innerHTML =
                                        '<div class="card-body d-flex justify-content-between align-items-center">' +
                                        '<div>' +
                                        '<strong>' + g.numeroGare + '</strong><br>' +
                                        '<small>Places libres : ' + g.places_libres + '</small><br>' +
                                        (a ? '<small>' + a.nombre_passagers + ' passager(s) à transférer — ' + montant + ' FCFA</small>' : '') +
                                        (a && !possible ? '<br><small class="text-danger">Transfert impossible (places insuffisantes ou aucun passager à transférer)</small>' : '') +
                                        '</div>' +
                                        '<form method="post" action="{{ route('admin.transfert-gare.executer') }}">' +
                                        '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                                        '<input type="hidden" name="id_programmation_source" value="' + idProgrammation + '">' +
                                        '<input type="hidden" name="id_programmation_destination" value="' + g.id_programmation + '">' +
                                        '<button type="submit" class="btn btn-sm btn-success"' + (possible ? '' : ' disabled') + '>Confirmer</button>' +
                                        '</form>' +
                                        '</div>';
                                    liste.appendChild(card);
                                });

                                contenu.style.display = '';
                            })
                            .catch(function () {
                                loading.style.display = 'none';
                                aucune.textContent = 'Erreur lors de la recherche des gares compatibles.';
                                aucune.style.display = '';
                            });
                    });
                });
            });
        </script>
    @endunless
@endsection
