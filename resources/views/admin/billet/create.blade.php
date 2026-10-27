@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Achat de ticket · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-list me-1"></i> G-réservation</span>
@endsection
@section('breadcrumb-active', 'Achat de ticket')

@section('breadcrumb-actions')
    <a href="{{ route('admin.billet.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-list-ul me-1"></i> Liste des tickets
    </a>
@endsection

@section('content')


    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow rounded-4 overflow-hidden">
                <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-2"
                     style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;">
                    <i class="fas fa-ticket fs-5"></i>
                    <span class="fw-semibold">Nouvelle réservation</span>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="{{ route('admin.billet.store') }}" id="formBillet">
                        @csrf
                        <div class="row g-3">
                            @if ($authUser->droit === 'Admin')
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Départ (gare)</label>
                                    <select class="form-select" name="idDepart" id="departSelect" required>
                                        <option value="" disabled selected>Choisissez la gare de départ</option>
                                        @foreach ($agences as $agence)
                                            <option value="{{ $agence->idAgence }}">{{ $agence->localite }} - {{ $agence->numeroGare }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">C'est la caisse de cette gare qui sera alimentée.</small>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Destination</label>
                                <select class="form-select" id="destinationSelect" required>
                                    <option value="" selected disabled>Choisissez une destination</option>
                                </select>
                                <input type="hidden" name="destinationId" id="hiddenDestinationId">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom &amp; Prénom du client</label>
                                <input type="text" class="form-control" name="Client" required placeholder="Nom &amp; Prénom">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date de voyage</label>
                                <input type="date" class="form-control" id="jourVoyage" name="jourVoyage" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Heure de départ</label>
                                <select class="form-select" name="programme" id="programmeSelect" required>
                                    <option value="" selected disabled>Sélectionnez une heure</option>
                                </select>
                                <input type="time" class="form-control mt-2" id="heureLibreInput" name="programme" disabled style="display:none;">
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" id="heureLibreCheck">
                                    <label class="form-check-label small text-muted" for="heureLibreCheck">
                                        Heure non listée (saisir librement)
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre de places</label>
                                <input type="number" min="1" class="form-control" id="nombrePassagers" name="nombrePassages" required>
                            </div>

                            <div class="col-12" id="escalesBox" style="display:none;">
                                <label class="form-label fw-semibold">Escale</label>
                                <div id="escalesList" class="d-flex flex-wrap gap-2"></div>
                                <input type="hidden" id="escaleSelected" name="escale" value="">
                            </div>

                            <div class="col-12" id="carVoyageBox" style="display:none;"></div>

                            @if ($authUser->userHasPermission('Billets_bagages'))
                                <div class="col-12">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="afficherBagages">
                                        <label class="form-check-label fw-semibold" for="afficherBagages">Bagages payants</label>
                                    </div>
                                    <div class="d-none" id="bagagesBox">
                                        <div id="bagagesRows">
                                            <div class="row align-items-end mb-2 bagage-row">
                                                <div class="col-md-5">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control" placeholder="Ex: 1 valise" name="bagage_description[]" autocomplete="off">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Poids (kg)</label>
                                                    <input type="number" step="0.1" min="0" class="form-control" placeholder="Poids" name="bagage_poids[]" autocomplete="off">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Prix (FCFA)</label>
                                                    <input type="number" min="0" class="form-control" placeholder="Prix" name="bagage_prix[]" autocomplete="off">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-outline-danger remove-bagage-btn d-none w-100" title="Retirer cette ligne">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="addBagageRow" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-plus"></i> Ajouter un bagage
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12 mt-2">
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="fas fa-circle-check me-1"></i> Enregistrer la réservation
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow rounded-4 position-sticky" style="top: 1rem;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <span class="fw-semibold text-muted text-uppercase small"><i class="fas fa-receipt me-1"></i> Résumé</span>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Destination</span>
                        <span class="fw-semibold" id="resumeDestination">—</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Heure</span>
                        <span class="fw-semibold" id="resumeHeure">—</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" id="resumeEscaleRow" style="display:none;">
                        <span class="text-muted small">Escale</span>
                        <span class="fw-semibold" id="resumeEscale">—</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Places</span>
                        <span class="fw-semibold" id="resumePlaces">—</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Prix unitaire</span>
                        <span class="fw-semibold" id="resumePrixUnitaire">—</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-3">
                        <span class="fw-bold">Total à payer</span>
                        <span class="fs-3 fw-bold text-success" id="resumeTotal">0 F</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ asset('mon_js/thermal-print.js') }}?v={{ @filemtime(public_path('mon_js/thermal-print.js')) }}"></script>
    <script>
        // Le ticket est la preuve de paiement remise au client : imprimé automatiquement
        // dès l'arrivée sur cette page après enregistrement (cf. BilletController::store(),
        // paramètre ?billetImprime=<idBillets>). Nettoie ensuite l'URL pour qu'un rechargement
        // de page ne réimprime pas.
        (function () {
            const params = new URLSearchParams(window.location.search);
            const idBillet = params.get('billetImprime');
            if (idBillet) {
                imprimerBilletThermique(idBillet, { auto: true });
                params.delete('billetImprime');
                params.delete('idDepart');
                const nouvelleUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, '', nouvelleUrl);
            }
        })();
    </script>

    <script>
        const dateInput = document.getElementById('jourVoyage');
        (function initDateLimits() {
            const today = new Date();
            const maxDate = new Date(today);
            maxDate.setDate(today.getDate() + {{ (int) config('billets.jours_reservation_avance', 6) }});
            const toISO = d => d.toISOString().slice(0, 10);
            dateInput.min = toISO(today);
            dateInput.max = toISO(maxDate);
            dateInput.value = dateInput.min;
        })();

        @if ($authUser->droit === 'Admin')
            const destinationsParGare = @json($destinationsParGare);
        @endif
        let destinations = @json($destinations);

        const destinationSelect = document.getElementById('destinationSelect');
        const programmeSelect = document.getElementById('programmeSelect');
        const escalesBox = document.getElementById('escalesBox');
        const escalesList = document.getElementById('escalesList');
        const escaleSelected = document.getElementById('escaleSelected');
        const nombrePassagersInput = document.getElementById('nombrePassagers');
        const heureLibreCheck = document.getElementById('heureLibreCheck');
        const heureLibreInput = document.getElementById('heureLibreInput');
        const carVoyageBox = document.getElementById('carVoyageBox');
        let prixUnitaireHeureLibre = null;

        // Les noms de destination/escale viennent de la config (localités/escales saisies
        // par l'Admin), mais peuvent contenir des apostrophes (ex. "Côte d'Ivoire") ou des
        // caractères spéciaux : échappés avant interpolation dans du HTML/attributs pour
        // rester robustes, pas seulement pour la sécurité.
        function escapeHtml(s) {
            return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function rebuildDestinationOptions() {
            destinationSelect.innerHTML = '<option value="" selected disabled>Choisissez une destination</option>' +
                destinations.map((dest, i) => `<option value="${i}">${escapeHtml(dest.nom)}</option>`).join('');
            document.getElementById('hiddenDestinationId').value = '';
            programmeSelect.innerHTML = '<option value="" selected disabled>Sélectionnez une heure</option>';
            escalesBox.style.display = 'none';
            escalesList.innerHTML = '';
            majResume();
        }

        @if ($authUser->droit === 'Admin')
            document.getElementById('departSelect').addEventListener('change', function () {
                destinations = destinationsParGare[this.value] || [];
                rebuildDestinationOptions();
            });
        @endif

        const dateEqToday = sel => sel === (new Date().toISOString().slice(0, 10));
        const hourIsPast = (hstr) => {
            const [h, m] = hstr.split(':').map(Number);
            const now = new Date();
            return (h < now.getHours()) || (h === now.getHours() && m <= now.getMinutes());
        };

        // Heure déjà passée aujourd'hui : reste sélectionnable (vente d'un billet pour un
        // voyage déjà en cours), juste signalée dans le libellé de l'option.
        function updateProgrammeOptions() {
            const destIdx = destinationSelect.value;
            const isToday = dateEqToday(dateInput.value);
            const programmes = (destinations[destIdx] ?? {}).programmes ?? [];
            programmeSelect.innerHTML = '<option value="" selected disabled>Sélectionnez une heure</option>' +
                programmes.map(p => {
                    const passee = isToday && hourIsPast(p.heureDepart);
                    const libelle = escapeHtml(p.heureDepart) + (passee ? ' (déjà passée)' : '');
                    return `<option value="${escapeHtml(p.heureDepart)}" data-prix="${p.prix}" data-escales="${escapeHtml(JSON.stringify(p.escales))}">${libelle}</option>`;
                }).join('');
            escalesBox.style.display = 'none';
            escalesList.innerHTML = '';
            majResume();
            rafraichirEtatCreneau();
        }

        destinationSelect.addEventListener('change', function () {
            document.getElementById('hiddenDestinationId').value = destinationSelect.options[destinationSelect.selectedIndex].textContent;
            updateProgrammeOptions();
        });
        dateInput.addEventListener('change', updateProgrammeOptions);

        // "Heure non listée" : saisie libre d'une heure absente du catalogue (programmeSelect
        // et heureLibreInput partagent name="programme" ; seul le champ actif, non désactivé,
        // est effectivement soumis).
        heureLibreCheck.addEventListener('change', function () {
            if (this.checked) {
                programmeSelect.disabled = true;
                programmeSelect.style.display = 'none';
                heureLibreInput.disabled = false;
                heureLibreInput.style.display = '';
                escalesBox.style.display = 'none';
                escalesList.innerHTML = '';
                escaleSelected.value = '';
            } else {
                heureLibreInput.disabled = true;
                heureLibreInput.style.display = 'none';
                programmeSelect.disabled = false;
                programmeSelect.style.display = '';
            }
            majResume();
            rafraichirEtatCreneau();
        });
        heureLibreInput.addEventListener('change', function () { majResume(); rafraichirEtatCreneau(); });

        function heureActuelle() {
            return heureLibreCheck.checked ? (heureLibreInput.value || '') : (programmeSelect.value || '');
        }

        // Vente d'un billet du jour sans programmation préalable : dès que date=aujourd'hui
        // et heure+destination sont connues, demande au serveur si un car est déjà
        // programmé sur ce créneau (bandeau info) ou s'il faut en choisir un parmi ceux
        // disponibles à la gare.
        function rafraichirEtatCreneau() {
            const isToday = dateEqToday(dateInput.value);
            const destIdx = destinationSelect.value;
            const destNom = destinations[destIdx]?.nom;
            const heure = heureActuelle();

            prixUnitaireHeureLibre = null;
            carVoyageBox.style.display = 'none';
            carVoyageBox.innerHTML = '';

            if (! isToday || ! destNom || ! heure) {
                majResume();
                return;
            }

            const idDepart = document.getElementById('departSelect')?.value || '';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            fetch('{{ route('admin.billet.ajax-etat-creneau') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ heureDepart: heure, destinationLocalite: destNom, idDepart }),
            })
                .then(r => r.json())
                .then(data => {
                    if (data.erreur) {
                        return;
                    }
                    prixUnitaireHeureLibre = data.prix_unitaire;
                    carVoyageBox.style.display = '';

                    if (data.existe) {
                        carVoyageBox.innerHTML = `
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-bus me-1"></i>
                                Car déjà programmé sur ce créneau : <strong>${escapeHtml(data.numero_car ?? '-')}</strong>
                                (${escapeHtml(data.matriculle ?? '-')}) — ${data.places_restantes} place(s) restante(s).
                            </div>`;
                    } else if (data.cars && data.cars.length) {
                        carVoyageBox.innerHTML = `
                            <label class="form-label fw-semibold">Car pour ce voyage</label>
                            <select class="form-select" name="idCarChoisi" id="idCarChoisiSelect" required>
                                <option value="" selected disabled>Choisissez un car disponible à la gare</option>
                                ${data.cars.map(c => `<option value="${c.id_car}">${escapeHtml(c.numero_car)} (${escapeHtml(c.matriculle)}) — ${c.places_restantes} place(s)</option>`).join('')}
                            </select>`;
                    } else {
                        carVoyageBox.innerHTML = `
                            <div class="alert alert-warning mb-0">
                                Aucun car disponible à cette gare pour programmer ce voyage.
                            </div>`;
                    }

                    majResume();
                });
        }

        // Escales en boutons pilule cliquables (au lieu de radios nus) : cliquer une
        // deuxième fois sur celle déjà choisie la décoche (revenir à "pas d'escale").
        function rebuildEscales() {
            const selectedOption = programmeSelect.options[programmeSelect.selectedIndex];
            const escales = selectedOption ? JSON.parse(selectedOption.dataset.escales || '[]') : [];
            escalesBox.style.display = escales.length ? '' : 'none';
            escalesList.innerHTML = escales.map(e => `
                <button type="button" class="btn btn-sm btn-outline-primary escale-pill" data-nom="${escapeHtml(e.escale_nom)}" data-prix="${e.prix_escale || 0}">
                    ${escapeHtml(e.escale_nom)} — ${Number(e.prix_escale || 0).toLocaleString('fr-FR')} F
                </button>
            `).join('');
            escaleSelected.value = '';
        }

        escalesList.addEventListener('click', function (event) {
            const btn = event.target.closest('.escale-pill');
            if (! btn) return;
            const dejaActive = btn.classList.contains('active');
            escalesList.querySelectorAll('.escale-pill').forEach(b => b.classList.remove('active', 'btn-primary', 'btn-outline-primary'));
            escalesList.querySelectorAll('.escale-pill').forEach(b => b.classList.add('btn-outline-primary'));
            if (! dejaActive) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('active', 'btn-primary');
                escaleSelected.value = btn.dataset.nom;
            } else {
                escaleSelected.value = '';
            }
            majResume();
        });

        programmeSelect.addEventListener('change', function () { rebuildEscales(); majResume(); rafraichirEtatCreneau(); });
        nombrePassagersInput.addEventListener('input', majResume);

        function majResume() {
            const destIdx = destinationSelect.value;
            const destNom = destinations[destIdx]?.nom ?? '—';
            const heureLibre = heureLibreCheck.checked;
            const selectedOption = programmeSelect.options[programmeSelect.selectedIndex];
            const heure = heureLibre ? (heureLibreInput.value || '—') : (selectedOption && selectedOption.value ? selectedOption.value : '—');
            const escaleBtn = escalesList.querySelector('.escale-pill.active');
            let prixUnitaire;
            let prixIntrouvable = false;
            if (heureLibre) {
                prixUnitaire = prixUnitaireHeureLibre;
                prixIntrouvable = (heure !== '—' && prixUnitaire === null);
            } else {
                const prixProgramme = selectedOption ? parseFloat(selectedOption.dataset.prix || 0) : 0;
                prixUnitaire = escaleBtn ? parseFloat(escaleBtn.dataset.prix || 0) : prixProgramme;
            }
            const nb = parseInt(nombrePassagersInput.value) || 0;

            document.getElementById('resumeDestination').textContent = destNom;
            document.getElementById('resumeHeure').textContent = heure;
            document.getElementById('resumePlaces').textContent = nb || '—';
            document.getElementById('resumePrixUnitaire').textContent = prixIntrouvable
                ? 'Tarif introuvable pour cette heure — choisissez une heure déjà listée'
                : (prixUnitaire ? Number(prixUnitaire).toLocaleString('fr-FR') + ' F' : '—');
            document.getElementById('resumeTotal').textContent = prixIntrouvable
                ? '—'
                : (Number(prixUnitaire || 0) * nb).toLocaleString('fr-FR') + ' F';

            const escaleRow = document.getElementById('resumeEscaleRow');
            if (escaleBtn) {
                escaleRow.style.display = '';
                document.getElementById('resumeEscale').textContent = escaleBtn.dataset.nom;
            } else {
                escaleRow.style.display = 'none';
            }
        }

        // Bagages payants (optionnel) : "add to row", même principe que les escales —
        // un passager peut avoir plusieurs bagages (description/poids/prix).
        (function () {
            const afficherBagages = document.getElementById('afficherBagages');
            if (! afficherBagages) {
                return;
            }
            const bagagesBox = document.getElementById('bagagesBox');
            const bagagesRows = document.getElementById('bagagesRows');
            const addBagageBtn = document.getElementById('addBagageRow');

            afficherBagages.addEventListener('change', function () {
                bagagesBox.classList.toggle('d-none', ! this.checked);
            });

            function toggleRemoveBagageButtons() {
                const rows = bagagesRows.querySelectorAll('.bagage-row');
                rows.forEach(function (row) {
                    row.querySelector('.remove-bagage-btn').classList.toggle('d-none', rows.length <= 1);
                });
            }

            addBagageBtn.addEventListener('click', function () {
                const firstRow = bagagesRows.querySelector('.bagage-row');
                const newRow = firstRow.cloneNode(true);
                newRow.querySelectorAll('input').forEach(function (input) { input.value = ''; });
                bagagesRows.appendChild(newRow);
                toggleRemoveBagageButtons();
            });

            bagagesRows.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-bagage-btn');
                if (btn) {
                    btn.closest('.bagage-row').remove();
                    toggleRemoveBagageButtons();
                }
            });
        })();

        @if ($authUser->droit !== 'Admin')
            rebuildDestinationOptions();
        @endif
    </script>
@endsection
