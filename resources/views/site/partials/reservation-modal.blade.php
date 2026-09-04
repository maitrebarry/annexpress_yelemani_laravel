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

                <details class="resa-payment-guide" id="resaPaymentGuide" open>
                    <summary><i class="fas fa-circle-question"></i> Comment payer avec Orange Money ?</summary>
                    <div class="resa-payment-guide-body">
                        <p class="resa-payment-target" id="resaPaymentTarget">Chargement des informations de paiement...</p>

                        <div class="resa-pay-carousel" id="resaPayCarousel">
                            <div class="resa-pay-carousel-track" id="resaPayTrack">
                                <div class="resa-pay-slide">
                                    <div class="resa-pay-slide-icon"><i class="fas fa-mobile-screen-button"></i></div>
                                    <div class="resa-pay-slide-step">Étape 1 / 7</div>
                                    <div class="resa-pay-slide-title">Ouvrez le clavier d'appel</div>
                                    <p class="resa-pay-slide-text">Sur le téléphone qui va payer, ouvrez l'application <strong>Téléphone</strong> — comme pour composer un appel normal.</p>
                                </div>
                                <div class="resa-pay-slide">
                                    <div class="resa-pay-slide-icon"><i class="fas fa-hashtag"></i></div>
                                    <div class="resa-pay-slide-step">Étape 2 / 7</div>
                                    <div class="resa-pay-slide-title">Tapez le début du code</div>
                                    <div class="resa-pay-slide-code">#144#8*</div>
                                    <p class="resa-pay-slide-text">C'est le début du code Orange Money « Paiement Marchand » — toujours le même, à taper tel quel.</p>
                                </div>
                                <div class="resa-pay-slide">
                                    <div class="resa-pay-slide-icon"><i class="fas fa-store"></i></div>
                                    <div class="resa-pay-slide-step">Étape 3 / 7</div>
                                    <div class="resa-pay-slide-title">Ajoutez le code marchand</div>
                                    <div class="resa-pay-slide-code"><span class="js-pay-code">------</span>*</div>
                                    <p class="resa-pay-slide-text">C'est le code de la gare de départ, déjà indiqué ci-dessus — recopiez-le tel quel.</p>
                                </div>
                                <div class="resa-pay-slide">
                                    <div class="resa-pay-slide-icon"><i class="fas fa-money-bill-wave"></i></div>
                                    <div class="resa-pay-slide-step">Étape 4 / 7</div>
                                    <div class="resa-pay-slide-title">Ajoutez le montant</div>
                                    <div class="resa-pay-slide-code"><span class="js-pay-montant">0</span>*</div>
                                    <p class="resa-pay-slide-text">Le montant exact à payer — il se met à jour tout seul selon le nombre de passagers.</p>
                                </div>
                                <div class="resa-pay-slide">
                                    <div class="resa-pay-slide-icon"><i class="fas fa-lock"></i></div>
                                    <div class="resa-pay-slide-step">Étape 5 / 7</div>
                                    <div class="resa-pay-slide-title">Ajoutez votre code secret</div>
                                    <div class="resa-pay-slide-code">••••#</div>
                                    <p class="resa-pay-slide-text">Terminez par votre <strong>code secret Orange Money</strong> personnel (4 chiffres), puis <strong>#</strong>. Ne le partagez jamais avec personne.</p>
                                </div>
                                <div class="resa-pay-slide">
                                    <div class="resa-pay-slide-icon"><i class="fas fa-phone"></i></div>
                                    <div class="resa-pay-slide-step">Étape 6 / 7</div>
                                    <div class="resa-pay-slide-title">Appelez ce numéro</div>
                                    <div class="resa-pay-slide-code resa-pay-slide-code-small"><span class="js-pay-full">#144#8*...*...*••••#</span></div>
                                    <p class="resa-pay-slide-text">Une fois le code entier tapé, appuyez sur la touche d'appel verte — comme pour un appel classique.</p>
                                </div>
                                <div class="resa-pay-slide">
                                    <div class="resa-pay-slide-icon"><i class="fas fa-circle-check"></i></div>
                                    <div class="resa-pay-slide-step">Étape 7 / 7</div>
                                    <div class="resa-pay-slide-title">Paiement confirmé</div>
                                    <p class="resa-pay-slide-text">Un message s'affiche à l'écran, puis un <strong>SMS de confirmation Orange Money</strong> arrive — gardez-le, il sert de preuve de paiement.</p>
                                </div>
                            </div>
                        </div>
                        <div class="resa-pay-nav" id="resaPayNav">
                            <button type="button" class="resa-pay-nav-btn" id="resaPayPrev" aria-label="Étape précédente"><i class="fas fa-chevron-left"></i></button>
                            <div class="resa-pay-dots" id="resaPayDots"></div>
                            <button type="button" class="resa-pay-nav-btn" id="resaPayNext" aria-label="Étape suivante"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </details>

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
    .resa-payment-guide { border: 2px solid #e2e8f0; border-radius: var(--radius, 8px); overflow: hidden; margin-bottom: 16px; }
    .resa-payment-guide summary {
        cursor: pointer; list-style: none; padding: 12px 14px; font-weight: 700; font-size: .84rem;
        color: var(--primary, #0f3b5e); background: #f8fafc; display: flex; align-items: center; gap: 8px;
    }
    .resa-payment-guide summary::-webkit-details-marker { display: none; }
    .resa-payment-guide summary i:first-child { color: var(--secondary, #e67e22); }
    .resa-payment-guide summary::after {
        content: '\f078'; font-family: 'Font Awesome 6 Free'; font-weight: 900; margin-left: auto;
        font-size: .72rem; color: var(--gray, #7f8c8d); transition: transform .2s;
    }
    .resa-payment-guide[open] summary::after { transform: rotate(180deg); }
    .resa-payment-guide-body { padding: 14px; font-size: .8rem; color: var(--dark, #2c3e50); }
    .resa-payment-target {
        background: #eff8ff; border-left: 3px solid var(--accent, #3498db); border-radius: 6px;
        padding: 10px 12px; margin: 0 0 12px; font-size: .82rem; line-height: 1.5;
    }
    .resa-payment-target strong { font-size: 1rem; }
    .resa-payment-guide-note { display: flex; gap: 6px; align-items: flex-start; font-size: .72rem; color: var(--gray, #7f8c8d); margin: 0; }

    /* Carrousel "Comment payer" — anime les étapes une par une (utilisateurs peu à l'aise
       avec le numérique : mieux vaut une info à la fois qu'une longue liste à lire). */
    .resa-pay-carousel { position: relative; background: #fff; border: 1px solid #e2e8f0; border-radius: var(--radius, 8px); overflow: hidden; }
    .resa-pay-carousel-track { display: flex; transition: transform .35s ease; }
    .resa-pay-slide { flex: 0 0 100%; padding: 18px 16px 14px; text-align: center; min-height: 150px; }
    .resa-pay-slide-icon {
        width: 42px; height: 42px; border-radius: 50%; margin: 0 auto 10px;
        background: linear-gradient(135deg, var(--primary, #0f3b5e), var(--primary-dark, #0a2a44));
        color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.05rem;
    }
    .resa-pay-slide-step { font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--secondary, #e67e22); margin-bottom: 4px; }
    .resa-pay-slide-title { font-size: .9rem; font-weight: 700; color: var(--dark, #2c3e50); margin-bottom: 8px; }
    .resa-pay-slide-code {
        display: inline-block; background: var(--primary, #0f3b5e); color: #fff; font-family: 'Courier New', monospace;
        font-size: 1.05rem; font-weight: 700; letter-spacing: .03em; padding: 7px 14px; border-radius: 8px; margin-bottom: 8px;
    }
    .resa-pay-slide-code-small { font-size: .78rem; padding: 7px 10px; word-break: break-all; }
    .resa-pay-slide-text { font-size: .76rem; color: var(--gray, #7f8c8d); line-height: 1.45; margin: 0; }
    .resa-pay-nav { display: flex; align-items: center; justify-content: center; gap: 14px; padding: 10px 0 2px; }
    .resa-pay-nav-btn {
        width: 26px; height: 26px; border-radius: 50%; border: none; background: #e2e8f0; color: var(--dark, #2c3e50);
        cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .68rem; flex-shrink: 0;
    }
    .resa-pay-nav-btn:hover { background: var(--secondary, #e67e22); color: #fff; }
    .resa-pay-dots { display: flex; gap: 6px; }
    .resa-pay-dot { width: 6px; height: 6px; border-radius: 50%; background: #cbd5e1; padding: 0; border: none; cursor: pointer; transition: all .2s; }
    .resa-pay-dot.is-active { background: var(--secondary, #e67e22); width: 16px; border-radius: 4px; }
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
        let codeMarchandActuel = '';

        // Carrousel "Comment payer" : une étape à la fois, défilement automatique
        // (pensé pour des utilisateurs peu à l'aise avec le numérique — voir demande :
        // "surtout ceux qui ne savent manipuler les outils numérique"), navigable
        // aussi manuellement (flèches/points).
        (function () {
            const track = document.getElementById('resaPayTrack');
            const dotsWrap = document.getElementById('resaPayDots');
            const prevBtn = document.getElementById('resaPayPrev');
            const nextBtn = document.getElementById('resaPayNext');
            if (! track || ! dotsWrap) return;

            const slides = track.children;
            const count = slides.length;
            let index = 0;
            let timer = null;

            for (let i = 0; i < count; i++) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'resa-pay-dot' + (i === 0 ? ' is-active' : '');
                dot.setAttribute('aria-label', 'Étape ' + (i + 1));
                dot.addEventListener('click', function () { goTo(i); restart(); });
                dotsWrap.appendChild(dot);
            }
            const dots = dotsWrap.children;

            function goTo(i) {
                index = (i + count) % count;
                track.style.transform = 'translateX(-' + (index * 100) + '%)';
                for (let j = 0; j < dots.length; j++) dots[j].classList.toggle('is-active', j === index);
            }
            function start() { stop(); timer = setInterval(function () { goTo(index + 1); }, 4000); }
            function stop() { if (timer) clearInterval(timer); }
            function restart() { start(); }

            if (prevBtn) prevBtn.addEventListener('click', function () { goTo(index - 1); restart(); });
            if (nextBtn) nextBtn.addEventListener('click', function () { goTo(index + 1); restart(); });

            start();

            const detailsEl = document.getElementById('resaPaymentGuide');
            if (detailsEl) {
                detailsEl.addEventListener('toggle', function () {
                    if (detailsEl.open) { goTo(0); start(); } else { stop(); }
                });
            }
        })();

        function updatePaymentCarousel(montantTexte) {
            document.querySelectorAll('.js-pay-code').forEach(function (el) { el.textContent = codeMarchandActuel || '------'; });
            document.querySelectorAll('.js-pay-montant').forEach(function (el) { el.textContent = montantTexte; });
            document.querySelectorAll('.js-pay-full').forEach(function (el) {
                el.textContent = '#144#8*' + (codeMarchandActuel || '------') + '*' + montantTexte.replace(/\s/g, '') + '*••••#';
            });
        }

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
            const total = (prixUnitaireActuel * nb).toLocaleString('fr-FR');
            document.getElementById('resaTotalPrice').textContent = total;
            updatePaymentCarousel(total);
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

                    // Informations de paiement de la gare de départ (voir Admin > Gares :
                    // "Code marchand" + "Numéro Orange (Mobile Money)"), jusqu'ici jamais
                    // transmises au client alors que le formulaire lui demandait déjà de
                    // payer — sans jamais lui dire à qui envoyer l'argent.
                    const paymentTarget = document.getElementById('resaPaymentTarget');
                    const payCarousel = document.getElementById('resaPayCarousel');
                    const payNav = document.getElementById('resaPayNav');
                    codeMarchandActuel = trajet.codeMarchand || '';
                    if (paymentTarget) {
                        if (trajet.codeMarchand || trajet.numeroOrangeMoney) {
                            let html = '';
                            if (trajet.numeroOrangeMoney) {
                                html += 'Numéro à utiliser : <strong>' + trajet.numeroOrangeMoney + '</strong><br>';
                            }
                            if (trajet.codeMarchand) {
                                html += 'Code marchand : <strong>' + trajet.codeMarchand + '</strong>';
                            }
                            paymentTarget.innerHTML = html;
                            if (payCarousel) payCarousel.style.display = '';
                            if (payNav) payNav.style.display = '';
                        } else {
                            paymentTarget.textContent = "Les informations de paiement de cette gare ne sont pas encore configurées — contactez la compagnie avant de payer.";
                            if (payCarousel) payCarousel.style.display = 'none';
                            if (payNav) payNav.style.display = 'none';
                        }
                    }

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
