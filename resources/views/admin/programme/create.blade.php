@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Programmer un voyage · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-calendar-check me-1"></i> G-programme</span>
@endsection
@section('breadcrumb-active', 'Programmer un voyage')

@section('breadcrumb-actions')
    <a href="{{ route('admin.programme.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-list-ul me-1"></i> Voir la liste
    </a>
@endsection

@section('content')


    @if ($choixCompagnieRequis)
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <p class="mb-3">En tant que super administrateur, choisissez d'abord la compagnie pour laquelle programmer ce voyage :</p>
                <form method="get" action="{{ route('admin.programme.create') }}" class="row g-3">
                    <div class="col-md-6">
                        <select class="form-select" name="id_compagnie" required>
                            <option value="" disabled selected>Choisissez une compagnie</option>
                            @foreach ($listeCompagnie as $c)
                                <option value="{{ $c->id_compagnie }}">{{ $c->nom_compagnie }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">Continuer <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-xxl-12">
                <div class="col-xl-12 mx-auto">
                    <div id="stepper1" class="bs-stepper">
                        <div class="card border-top border-primary border-3">
                            <div class="card-header">
                                <div class="d-lg-flex flex-lg-row align-items-lg-center justify-content-lg-between" role="tablist">
                                    <div class="step" data-target="#step-itineraire">
                                        <div class="step-trigger" role="tab" id="stepper1trigger1" aria-controls="step-itineraire">
                                            <div class="bs-stepper-circle bg-primary text-white"><i class="fas fa-location-dot"></i></div>
                                            <div>
                                                <h5 class="mb-0 steper-title">Itinéraire</h5>
                                                <p class="mb-0 steper-sub-title small text-muted">Départ, escales, destination</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bs-stepper-line"></div>
                                    <div class="step" data-target="#step-horaire">
                                        <div class="step-trigger" role="tab" id="stepper1trigger2" aria-controls="step-horaire">
                                            <div class="bs-stepper-circle bg-primary text-white"><i class="fas fa-clock"></i></div>
                                            <div>
                                                <h5 class="mb-0 steper-title">Horaire</h5>
                                                <p class="mb-0 steper-sub-title small text-muted">Heure de départ, RDV</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bs-stepper-line"></div>
                                    <div class="step" data-target="#step-tarif">
                                        <div class="step-trigger" role="tab" id="stepper1trigger3" aria-controls="step-tarif">
                                            <div class="bs-stepper-circle bg-primary text-white"><i class="fas fa-money-bill-wave"></i></div>
                                            <div>
                                                <h5 class="mb-0 steper-title">Tarification</h5>
                                                <p class="mb-0 steper-sub-title small text-muted">Prix transport et escales</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="bs-stepper-content">
                                    <form method="post" action="{{ route('admin.programme.store') }}">
                                        @csrf
                                        @if ($authUser->isSuperAdmin())
                                            <input type="hidden" name="id_compagnie" value="{{ $idCompagnie }}">
                                        @endif

                                        <!-- Étape 1 : Itinéraire -->
                                        <div id="step-itineraire" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger1">
                                            <div class="row g-3">
                                                <div class="col-12 col-lg-4">
                                                    <label for="choixAgence" class="form-label">
                                                        <i class="fas fa-location-crosshairs text-primary me-1"></i>Départ<span class="text-danger ms-1">*</span>
                                                    </label>
                                                    <select id="choixAgence" name="idDepart" class="form-select shadow-sm" required>
                                                        <option value="">Sélectionner le départ</option>
                                                        @foreach ($listeAgenceDepart as $a)
                                                            <option value="{{ $a->idAgence }}" data-localite="{{ $a->localite }}">{{ $a->localite.' ( '.$a->numeroGare.' )' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12 col-lg-4">
                                                    <label class="form-label"><i class="fas fa-map text-primary me-1"></i>Escale(s) <small class="text-muted">(optionnel)</small></label>
                                                    <div class="border rounded p-2 shadow-sm" style="max-height: 180px; overflow-y: auto;">
                                                        @forelse ($listeEscale as $e)
                                                            <div class="form-check">
                                                                <input class="form-check-input escale-checkbox" type="checkbox" name="idEscale[]"
                                                                    value="{{ $e->id_escale }}" id="escale{{ $e->id_escale }}" data-nom="{{ $e->escales }}">
                                                                <label class="form-check-label" for="escale{{ $e->id_escale }}">{{ $e->escales }}</label>
                                                            </div>
                                                        @empty
                                                            <p class="text-muted small mb-0">Aucune escale disponible.</p>
                                                        @endforelse
                                                    </div>
                                                </div>

                                                <div class="col-12 col-lg-4">
                                                    <label for="choixAgences" class="form-label">
                                                        <i class="fas fa-flag text-primary me-1"></i>Destination<span class="text-danger ms-1">*</span>
                                                    </label>
                                                    <select id="choixAgences" name="idDestination" class="form-select shadow-sm" required>
                                                        <option value="">Sélectionner la destination</option>
                                                        @foreach ($listeAgence as $a)
                                                            <option value="{{ $a->idAgence }}" data-localite="{{ $a->localite }}">{{ $a->localite.' ( '.$a->numeroGare.' )' }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small class="form-text text-muted">
                                                        <i class="fas fa-circle-info"></i> Les gares de la même localité que le départ sont masquées (voyage interne impossible).
                                                    </small>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" data-bs-toggle="collapse" data-bs-target="#trajetsExistants">
                                                        <i class="fas fa-list-ul"></i> Voir tous les trajets déjà programmés ({{ count($tousLesTrajets) }})
                                                    </button>
                                                </div>

                                                <div class="col-12">
                                                    <div class="collapse" id="trajetsExistants">
                                                        <div class="card card-body shadow-sm" style="max-height: 260px; overflow-y: auto;">
                                                            @if ($tousLesTrajets->isEmpty())
                                                                <p class="text-muted small mb-0">Aucun trajet programmé pour le moment.</p>
                                                            @else
                                                                <table class="table table-sm table-striped mb-0">
                                                                    <thead><tr><th>Départ</th><th>Destination</th><th>Heure</th><th>Prix</th></tr></thead>
                                                                    <tbody>
                                                                        @foreach ($tousLesTrajets as $t)
                                                                            <tr>
                                                                                <td>{{ $t->departLocalite.' ('.$t->departNumeroGare.')' }}</td>
                                                                                <td>{{ $t->destinationLocalite.' ('.$t->destinationNumeroGare.')' }}</td>
                                                                                <td>{{ $t->heureDepart }}</td>
                                                                                <td>{{ number_format((float) $t->prix, 0, ',', ' ') }} FCFA</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <button type="button" class="btn btn-primary px-4" onclick="stepper1.next()">Suivant <i class="fas fa-arrow-right ms-2"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Étape 2 : Horaire -->
                                        <div id="step-horaire" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger2">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label"><i class="fas fa-clock text-primary me-1"></i>Heure(s) de départ<span class="text-danger ms-1">*</span></label>
                                                    <p class="small text-muted mb-2">
                                                        Cochez une ou plusieurs heures : un voyage sera programmé pour chacune, avec le même itinéraire et les mêmes tarifs saisis aux autres étapes. Le RDV est calculé automatiquement, 45 min avant chaque départ.
                                                    </p>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @forelse ($listeHoraire as $h)
                                                            @php $idHoraire = 'horaire'.str_replace(':', '', $h->heuredepart); @endphp
                                                            <div class="form-check form-check-inline border rounded px-3 py-2 m-0">
                                                                <input class="form-check-input horaire-checkbox" type="checkbox" name="heureDepart[]" value="{{ $h->heuredepart }}" id="{{ $idHoraire }}">
                                                                <label class="form-check-label" for="{{ $idHoraire }}">{{ $h->heuredepart }}</label>
                                                            </div>
                                                        @empty
                                                            <p class="text-muted small mb-0">Aucun horaire disponible pour votre compagnie.</p>
                                                        @endforelse
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <button type="button" class="btn btn-outline-secondary px-4" onclick="stepper1.previous()"><i class="fas fa-arrow-left me-2"></i>Précédent</button>
                                                        <button type="button" class="btn btn-primary px-4" onclick="stepper1.next()">Suivant <i class="fas fa-arrow-right ms-2"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Étape 3 : Tarification -->
                                        <div id="step-tarif" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger3">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label for="prix" class="form-label"><i class="fas fa-money-bill-wave text-primary me-1"></i>Frais de transport<span class="text-danger ms-1">*</span></label>
                                                    <div class="input-group shadow-sm">
                                                        <input type="number" class="form-control" id="prix" name="prix" required>
                                                        <span class="input-group-text">FCFA</span>
                                                    </div>
                                                </div>

                                                <div class="col-12" id="fraixEscaleField" style="display: none;">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="form-label fw-semibold mb-0"><i class="fas fa-map me-1"></i>Frais des escales</label>
                                                        <button type="button" id="btnAppliquerTous" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-copy"></i> Appliquer le tarif de base à toutes les escales
                                                        </button>
                                                    </div>
                                                    <div id="fraixEscaleContainer"></div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <button type="button" class="btn btn-outline-secondary px-4" onclick="stepper1.previous()"><i class="fas fa-arrow-left me-2"></i>Précédent</button>
                                                        @unless ($authUser->estLectureSeule())
                                                            <button type="submit" class="btn btn-success rounded-pill shadow-sm px-4" name="enregistre">
                                                                <i class="fas fa-check me-1"></i> Enregistrer
                                                            </button>
                                                        @endunless
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @unless ($choixCompagnieRequis)
        <script>
            // Initialise le stepper (bs-stepper.min.js est chargé dans foot.blade.php mais
            // ne s'auto-initialise jamais tout seul) — sans ceci, les 3 étapes
            // (Itinéraire/Horaire/Tarification) restent toutes masquées puisqu'aucune
            // n'obtient jamais la classe "active", et les boutons Suivant/Précédent
            // échouent (stepper1 non défini) : la page semblait ne "rien" afficher.
            tgReady(function () {
                window.stepper1 = new Stepper(document.querySelector('#stepper1'));
            });

            // Synchronise les champs de tarif par escale avec les cases cochées.
            tgReady(function () {
                const prixInput = document.getElementById('prix');
                const fraixEscaleField = document.getElementById('fraixEscaleField');
                const fraixEscaleContainer = document.getElementById('fraixEscaleContainer');
                const escaleCheckboxes = document.querySelectorAll('.escale-checkbox');
                const btnAppliquerTous = document.getElementById('btnAppliquerTous');

                function syncEscalePriceFields() {
                    const cochees = Array.from(escaleCheckboxes).filter(cb => cb.checked);

                    fraixEscaleContainer.querySelectorAll('[data-escale-id]').forEach(function (group) {
                        if (!cochees.some(cb => cb.value === group.dataset.escaleId)) {
                            group.remove();
                        }
                    });

                    cochees.forEach(function (cb) {
                        if (fraixEscaleContainer.querySelector('[data-escale-id="' + cb.value + '"]')) return;
                        const group = document.createElement('div');
                        group.className = 'input-group mb-2';
                        group.dataset.escaleId = cb.value;
                        group.innerHTML = '<span class="input-group-text">' + cb.dataset.nom + '</span>' +
                            '<input type="number" class="form-control" name="prix_escale[' + cb.value + ']" placeholder="Frais pour ' + cb.dataset.nom + '">' +
                            '<span class="input-group-text">FCFA</span>';
                        group.querySelector('input').value = prixInput.value || '';
                        fraixEscaleContainer.appendChild(group);
                    });

                    fraixEscaleField.style.display = cochees.length > 0 ? '' : 'none';
                }

                escaleCheckboxes.forEach(cb => cb.addEventListener('change', syncEscalePriceFields));

                if (btnAppliquerTous) {
                    btnAppliquerTous.addEventListener('click', function () {
                        fraixEscaleContainer.querySelectorAll('input[type="number"]').forEach(function (input) {
                            input.value = prixInput.value || '';
                        });
                    });
                }
            });
        </script>

        <script>
            // Filtre la destination selon le départ choisi : masque les gares de la même
            // localité que le départ, pour éviter d'enregistrer un voyage interne.
            tgReady(function () {
                const departSelect = document.getElementById('choixAgence');
                const destinationSelect = document.getElementById('choixAgences');
                if (!departSelect || !destinationSelect) return;

                const destinationOptionsOriginal = Array.from(destinationSelect.options).map(function (opt) {
                    return { value: opt.value, text: opt.textContent, localite: opt.dataset.localite || null };
                });

                departSelect.addEventListener('change', function () {
                    const departOption = departSelect.options[departSelect.selectedIndex];
                    const departLocalite = departOption ? departOption.dataset.localite : null;
                    const currentDestination = destinationSelect.value;

                    destinationSelect.innerHTML = '';
                    destinationOptionsOriginal.forEach(function (opt) {
                        if (opt.localite && departLocalite && opt.localite === departLocalite) return;
                        const optionEl = document.createElement('option');
                        optionEl.value = opt.value;
                        optionEl.textContent = opt.text;
                        if (opt.localite) optionEl.dataset.localite = opt.localite;
                        destinationSelect.appendChild(optionEl);
                    });

                    if (Array.from(destinationSelect.options).some(o => o.value === currentDestination)) {
                        destinationSelect.value = currentDestination;
                    }
                });
            });
        </script>
    @endunless
@endsection
