{{--
    Modal de réservation en ligne, partagé par recherche.blade.php et
    compagnie-trajets.blade.php — ouvert au clic sur "Réserver" sans quitter la page
    (formulaire plein écran retiré au profit de ce modal, à la demande de l'utilisateur :
    "je veux que le formulaire de reservation soit modal pour que ça soit rapide").
    Les données du trajet sont chargées via `site.reservation.donnees` (AJAX), la
    réservation soumise via `site.reservation.store` (AJAX), succès -> redirection vers
    la fiche billet imprimable (`site.billet`).
--}}
<div class="resa-modal-overlay" id="resaModalOverlay">
    <div class="resa-modal" role="dialog" aria-modal="true" aria-labelledby="resaModalTitle">
        <button type="button" class="resa-modal-close" id="resaModalClose" aria-label="Fermer"><i class="fas fa-times"></i></button>

        <div class="resa-modal-head">
            <h3 id="resaModalTitle"><i class="fas fa-ticket-alt"></i> Réserver ce trajet</h3>
            <p id="resaModalRoute">&nbsp;</p>
        </div>

        <div class="resa-modal-body">
            <div class="resa-modal-loading" id="resaModalLoading"><i class="fas fa-spinner fa-spin"></i> Chargement du trajet...</div>

            <form id="resaForm" style="display:none;">
                <input type="hidden" name="id_programme" id="resaIdProgramme">
                <input type="hidden" name="id_compagnie" id="resaIdCompagnie">
                <input type="hidden" name="numeroGare" id="resaNumeroGare">
                <input type="hidden" name="departId" id="resaDepartId">
                <input type="hidden" name="destinationId" id="resaDestinationId">
                <input type="hidden" name="Heur_departs" id="resaHeureDepart">

                <div class="resa-row">
                    <div class="resa-group">
                        <label><i class="fas fa-map-marker-alt"></i> Départ</label>
                        <input type="text" id="resaDepartDisplay" class="resa-control" readonly>
                    </div>
                    <div class="resa-group">
                        <label><i class="fas fa-flag-checkered"></i> Destination</label>
                        <input type="text" id="resaDestinationDisplay" class="resa-control" readonly>
                    </div>
                </div>

                <div class="resa-group" id="resaEscaleGroup" style="display:none;">
                    <label><i class="fas fa-code-branch"></i> Escale optionnelle</label>
                    <div id="resaEscaleList"></div>
                </div>

                <div class="resa-row">
                    <div class="resa-group">
                        <label><i class="fas fa-calendar-alt"></i> Date du voyage</label>
                        <input type="date" name="jourVoyage" id="resaJourVoyage" class="resa-control" required>
                        <p class="resa-hint" id="resaAvisHeurePassee" style="display:none;">
                            <i class="fas fa-info-circle"></i> Départ d'aujourd'hui déjà passé — date ajustée au prochain jour disponible.
                        </p>
                    </div>
                    <div class="resa-group">
                        <label><i class="fas fa-clock"></i> Heure de départ</label>
                        <input type="text" id="resaHeureDisplay" class="resa-control" readonly>
                    </div>
                </div>

                <div class="resa-row">
                    <div class="resa-group">
                        <label><i class="fas fa-user"></i> Nom complet</label>
                        <input type="text" name="Client" class="resa-control" placeholder="Ex: Diallo Aminata" required>
                    </div>
                    <div class="resa-group">
                        <label><i class="fas fa-users"></i> Passagers</label>
                        <input type="number" name="nombrePassages" id="resaNbPassagers" class="resa-control" min="1" value="1" required>
                    </div>
                </div>

                <div class="resa-row">
                    <div class="resa-group">
                        <label><i class="fas fa-phone-alt"></i> Téléphone</label>
                        <input type="tel" name="numeroClient" id="resaNumeroClient" class="resa-control" placeholder="77 78 88 99" required>
                    </div>
                    <div class="resa-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="emailClient" class="resa-control" placeholder="exemple@mail.com">
                    </div>
                </div>

                <div class="resa-group">
                    <label><i class="fas fa-credit-card"></i> Numéro de paiement (Orange Money)</label>
                    <input type="text" name="numeroPaiement" id="resaNumeroPaiement" class="resa-control" placeholder="66 77 88 99" required>
                </div>

                <div class="resa-price-box">
                    <span><i class="fas fa-calculator"></i> Total à payer</span>
                    <strong><span id="resaTotalPrice">0</span> FCFA</strong>
                </div>

                <button type="submit" class="resa-submit" id="resaSubmitBtn">
                    <i class="fas fa-check-circle"></i> Réserver maintenant
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .resa-modal-overlay {
        position: fixed; inset: 0; background: rgba(15, 23, 42, .6); z-index: 3000;
        display: none; align-items: center; justify-content: center; padding: 20px;
        backdrop-filter: blur(2px);
    }
    .resa-modal-overlay.is-open { display: flex; }
    .resa-modal {
        background: white; width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto;
        border-radius: var(--radius-xl, 16px); box-shadow: 0 25px 60px -15px rgba(0,0,0,.4);
        position: relative; animation: resaPop .2s ease;
    }
    @keyframes resaPop { from { transform: scale(.96); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .resa-modal-close {
        position: absolute; top: 16px; right: 16px; background: rgba(255,255,255,.15); border: none;
        width: 34px; height: 34px; border-radius: 50%; color: white; font-size: 1rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center; z-index: 2;
    }
    .resa-modal-close:hover { background: rgba(255,255,255,.3); }
    .resa-modal-head {
        background: linear-gradient(135deg, var(--primary, #0f3b5e), var(--primary-dark, #0a2a44));
        color: white; padding: 26px 30px; border-radius: var(--radius-xl, 16px) var(--radius-xl, 16px) 0 0;
    }
    .resa-modal-head h3 { font-size: 1.25rem; margin-bottom: 4px; }
    .resa-modal-head p { font-size: .85rem; opacity: .85; margin: 0; }
    .resa-modal-body { padding: 26px 30px 30px; }
    .resa-modal-loading { text-align: center; padding: 40px 0; color: var(--gray, #7f8c8d); }
    .resa-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .resa-group { margin-bottom: 16px; }
    .resa-group label { display: block; font-weight: 600; font-size: .8rem; margin-bottom: 6px; color: var(--dark, #2c3e50); }
    .resa-group label i { color: var(--secondary, #e67e22); margin-right: 5px; }
    .resa-hint { display: flex; align-items: center; gap: 6px; font-size: .74rem; color: var(--secondary-dark, #c0392b); margin: 6px 2px 0; }
    .resa-control {
        width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: var(--radius, 8px);
        font-size: .88rem; font-family: inherit; background: #f8fafc; transition: all .2s;
    }
    .resa-control:focus { outline: none; border-color: var(--secondary, #e67e22); background: white; box-shadow: 0 0 0 3px rgba(230,126,34,.1); }
    .resa-control[readonly] { background: #f1f5f9; cursor: not-allowed; }
    .resa-escale-item { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: .85rem; }
    .resa-price-box {
        background: linear-gradient(135deg, #fef3e8, #fff5eb); border-radius: var(--radius-lg, 12px);
        padding: 14px 18px; margin: 6px 0 18px; display: flex; justify-content: space-between; align-items: center;
        font-size: .95rem; color: var(--dark, #2c3e50);
    }
    .resa-price-box strong { font-size: 1.3rem; color: var(--secondary, #e67e22); }
    .resa-submit {
        width: 100%; padding: 14px; background: linear-gradient(135deg, var(--secondary, #e67e22), var(--secondary-dark, #c0392b));
        color: white; border: none; border-radius: var(--radius, 8px); font-weight: 700; font-size: .95rem;
        cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all .2s;
    }
    .resa-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -8px rgba(230,126,34,.5); }
    .resa-submit:disabled { opacity: .6; cursor: not-allowed; transform: none; }
    @media (max-width: 576px) {
        .resa-row { grid-template-columns: 1fr; gap: 0; }
        .resa-modal-body { padding: 20px; }
        .resa-modal-head { padding: 20px; }
    }
</style>

<script>
    (function () {
        const overlay = document.getElementById('resaModalOverlay');
        const loading = document.getElementById('resaModalLoading');
        const form = document.getElementById('resaForm');
        const submitBtn = document.getElementById('resaSubmitBtn');
        let prixUnitaireActuel = 0;

        function csrfToken() {
            // Défensif : si jamais cette balise venait à manquer sur une page (elle doit
            // être présente dans le <head> de CHAQUE page du site, y compris pour le
            // moteur de transitions qui ne remplace jamais le <head>), on ne bloque pas
            // silencieusement le modal sur "Chargement..." pour toujours.
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : '';
        }

        function formatPhoneInput(el) {
            el.addEventListener('input', function () {
                let val = this.value.replace(/\D/g, '').slice(0, 8);
                let formatted = val;
                if (val.length > 2) formatted = val.slice(0, 2) + ' ' + val.slice(2);
                if (val.length > 4) formatted = formatted.slice(0, 5) + ' ' + formatted.slice(5);
                if (val.length > 6) formatted = formatted.slice(0, 8) + ' ' + formatted.slice(8);
                this.value = formatted;
            });
        }
        formatPhoneInput(document.getElementById('resaNumeroClient'));
        formatPhoneInput(document.getElementById('resaNumeroPaiement'));

        function updateTotal() {
            const nb = parseInt(document.getElementById('resaNbPassagers').value, 10) || 1;
            document.getElementById('resaTotalPrice').textContent = (prixUnitaireActuel * nb).toLocaleString('fr-FR');
        }
        document.getElementById('resaNbPassagers').addEventListener('input', updateTotal);

        window.openReservationModal = function (idProgramme) {
            overlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            loading.style.display = 'block';
            form.style.display = 'none';

            fetch("{{ url('/reservation') }}/" + idProgramme + "/donnees", {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            })
                .then(function (res) { return res.json(); })
                .then(function (trajet) {
                    if (!trajet.ok) {
                        Swal.fire('Trajet introuvable', trajet.message || "Ce trajet n'est plus disponible.", 'error');
                        closeReservationModal();
                        return;
                    }

                    document.getElementById('resaModalRoute').textContent = trajet.departLocalite + ' → ' + trajet.destinationLocalite + ' · ' + trajet.heureDepart;
                    document.getElementById('resaIdProgramme').value = trajet.idProgrammer;
                    document.getElementById('resaIdCompagnie').value = trajet.id_compagnie;
                    document.getElementById('resaNumeroGare').value = trajet.numeroGare1 || '';
                    document.getElementById('resaDepartId').value = trajet.departLocalite;
                    document.getElementById('resaDestinationId').value = trajet.destinationLocalite;
                    document.getElementById('resaHeureDepart').value = trajet.heureDepart;
                    document.getElementById('resaDepartDisplay').value = trajet.departLocalite + ' ( ' + (trajet.numeroGare1 || '-') + ' )';
                    document.getElementById('resaDestinationDisplay').value = trajet.destinationLocalite + ' ( ' + (trajet.numeroGare2 || '-') + ' )';
                    document.getElementById('resaHeureDisplay').value = trajet.heureDepart;

                    // La date sert de garde-fou horaire : si le départ (ex. 05:00) est déjà
                    // passé aujourd'hui, "aujourd'hui" ne doit plus être sélectionnable pour
                    // CE trajet précis — sinon on pourrait réserver un départ déjà parti.
                    const now = new Date();
                    const [depH, depM] = (trajet.heureDepart || '00:00').split(':').map(Number);
                    const departAujourdhuiDejaPasse = now.getHours() > depH || (now.getHours() === depH && now.getMinutes() >= depM);

                    const minDateObj = new Date(Date.now() + (departAujourdhuiDejaPasse ? 1 : 0) * 86400000);
                    const minDate = minDateObj.toISOString().slice(0, 10);
                    const maxDate = new Date(Date.now() + {{ (int) config('billets.jours_reservation_avance', 6) }} * 86400000).toISOString().slice(0, 10);
                    const dateInput = document.getElementById('resaJourVoyage');
                    dateInput.min = minDate;
                    dateInput.max = maxDate;
                    dateInput.value = minDate;

                    const avisHeurePassee = document.getElementById('resaAvisHeurePassee');
                    if (avisHeurePassee) {
                        avisHeurePassee.style.display = departAujourdhuiDejaPasse ? 'flex' : 'none';
                    }

                    prixUnitaireActuel = parseInt(trajet.prix, 10) || 0;

                    const escaleGroup = document.getElementById('resaEscaleGroup');
                    const escaleList = document.getElementById('resaEscaleList');
                    escaleList.innerHTML = '';
                    const radioNone = document.createElement('div');
                    if (trajet.escales && trajet.escales.length) {
                        escaleGroup.style.display = 'block';
                        trajet.escales.forEach(function (escale, index) {
                            const item = document.createElement('div');
                            item.className = 'resa-escale-item';
                            item.innerHTML = '<input type="radio" name="escale_finale" id="resaEscale' + index + '" value="' + escale.ville + '" data-prix="' + escale.prix + '">'
                                + '<label for="resaEscale' + index + '">' + escale.ville + ' (+' + escale.prix.toLocaleString('fr-FR') + ' FCFA)</label>';
                            escaleList.appendChild(item);
                        });
                        escaleList.querySelectorAll('input[type=radio]').forEach(function (radio) {
                            radio.addEventListener('change', function () {
                                prixUnitaireActuel = parseInt(this.dataset.prix, 10) || trajet.prix;
                                updateTotal();
                            });
                        });
                    } else {
                        escaleGroup.style.display = 'none';
                    }

                    updateTotal();
                    loading.style.display = 'none';
                    form.style.display = 'block';
                })
                .catch(function () {
                    Swal.fire('Erreur', 'Impossible de charger ce trajet pour le moment.', 'error');
                    closeReservationModal();
                });
        };

        window.closeReservationModal = function () {
            overlay.classList.remove('is-open');
            document.body.style.overflow = '';
            form.reset();
        };

        document.getElementById('resaModalClose').addEventListener('click', closeReservationModal);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closeReservationModal(); });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Réservation en cours...';

            fetch("{{ route('site.reservation.store') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: new FormData(form),
            })
                .then(function (res) { return res.json().then(function (data) { return { status: res.status, data: data }; }); })
                .then(function (result) {
                    if (result.data.ok) {
                        closeReservationModal();
                        Swal.fire({
                            icon: 'success',
                            title: '🎉 Réservation enregistrée !',
                            html: result.data.message,
                            confirmButtonText: 'Voir mon billet',
                        }).then(function () {
                            window.location.href = result.data.redirect;
                        });
                    } else {
                        Swal.fire('Réservation impossible', result.data.message, 'error');
                    }
                })
                .catch(function () {
                    Swal.fire('Erreur', 'La réservation a échoué. Merci de réessayer.', 'error');
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Réserver maintenant';
                });
        });
    })();
</script>
