// Impression thermique ESC/POS : le site (hébergé sur LWS) ne peut pas atteindre
// l'imprimante du comptoir (réseau local ou USB). On récupère donc les données du
// document (billet ou colis) en JSON depuis le site, puis on les transmet au pont
// d'impression local (local-print-bridge/), qui tourne sur le poste du comptoir et
// parle directement à l'imprimante. Si le pont est injoignable, on ouvre le PDF (repli
// fiable, imprimable via le pilote Windows déjà installé) plutôt que de ne rien faire.
//
// Adresse du pont : 127.0.0.1 par défaut (le pont tourne sur CET appareil, cas du PC de
// comptoir). Un téléphone ne peut pas faire tourner le pont lui-même (pas de serveur local
// persistant possible) : il doit appeler le pont d'un PC voisin sur le même Wi-Fi via son
// IP réseau (pont installé avec -AllowLan). Cette adresse est donc mémorisée par appareil
// (localStorage), modifiable depuis l'écran d'erreur "Pont d'impression injoignable".
(function () {
    function adresseHotePont() {
        return localStorage.getItem('pontImpressionHote') || '127.0.0.1:9200';
    }

    function urlPont() {
        return 'http://' + adresseHotePont() + '/print';
    }

    function demanderAdressePont(callbackApresEnregistrement) {
        Swal.fire({
            title: 'Adresse du pont d\'impression',
            html: 'Sur le PC de comptoir lui-même, laissez la valeur par défaut. ' +
                'Sur un téléphone, indiquez l\'adresse IP et le port du PC qui héberge ' +
                'le pont sur le même Wi-Fi (ex: 192.168.1.50:9200).',
            input: 'text',
            inputValue: adresseHotePont(),
            showCancelButton: true,
            confirmButtonText: 'Enregistrer',
            cancelButtonText: 'Annuler'
        }).then(function (resultat) {
            if (resultat.isConfirmed && resultat.value) {
                localStorage.setItem('pontImpressionHote', resultat.value.trim());
                if (callbackApresEnregistrement) callbackApresEnregistrement();
            }
        });
    }
    window.configurerAdressePontImpression = demanderAdressePont;

    function base() {
        return window.PWA_BASE_URL || '';
    }

    // auto = true pour un déclenchement automatique (ex: juste après l'enregistrement
    // d'un billet) : pas de message de confirmation bruyant en cas de succès, juste en
    // cas d'échec — et le repli PDF s'ouvre sans que l'agent ait à recliquer.
    function imprimerDocumentThermique(urlDonnees, urlPdf, options) {
        options = options || {};
        var auto = options.auto === true;

        function ouvrirPdf() {
            window.open(urlPdf, '_blank');
        }

        $.getJSON(urlDonnees)
            .done(function (donnees) {
                if (donnees.error) {
                    if (auto) { ouvrirPdf(); return; }
                    Swal.fire('Erreur', donnees.error, 'error');
                    return;
                }

                fetch(urlPont(), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(donnees)
                })
                    .then(function (r) { return r.json(); })
                    .then(function (resultat) {
                        if (resultat.success) {
                            if (!auto) {
                                Swal.fire('Ticket imprimé', resultat.message, 'success');
                            }
                            return;
                        }
                        if (auto) { ouvrirPdf(); return; }
                        Swal.fire('Erreur d\'impression', resultat.message, 'error');
                    })
                    .catch(function () {
                        // Pont injoignable à l'adresse configurée : on n'empêche pas la remise du
                        // document au client pour autant (repli PDF), sauf en déclenchement manuel
                        // où l'on propose de corriger l'adresse (utile sur téléphone) et réessayer.
                        if (auto) { ouvrirPdf(); return; }
                        Swal.fire({
                            title: 'Pont d\'impression injoignable',
                            text: 'Vérifiez que le logiciel d\'impression locale est bien lancé, ou changez l\'adresse du pont si vous imprimez depuis un téléphone.',
                            icon: 'error',
                            showCancelButton: true,
                            confirmButtonText: 'Configurer l\'adresse',
                            cancelButtonText: 'Fermer'
                        }).then(function (r) {
                            if (r.isConfirmed) {
                                demanderAdressePont(function () {
                                    imprimerDocumentThermique(urlDonnees, urlPdf, options);
                                });
                            }
                        });
                    });
            })
            .fail(function () {
                if (auto) { ouvrirPdf(); return; }
                Swal.fire('Erreur', 'Impossible de récupérer les données.', 'error');
            });
    }

    window.imprimerBilletThermique = function (idBillets, options) {
        imprimerDocumentThermique(
            base() + '/admin/Liste_du_jours/donneesTicketThermique/' + idBillets,
            base() + '/admin/Liste_du_jours/recu/' + idBillets,
            options
        );
    };

    window.imprimerColisThermique = function (idColis, options) {
        imprimerDocumentThermique(
            base() + '/admin/Colis_prise_en_charges/donneesRecuThermique/' + idColis,
            base() + '/admin/Colis_prise_en_charges/imprimer_recu/' + idColis,
            options
        );
    };

    $(document).on('click', '.thermal-print-btn', function (e) {
        e.preventDefault();
        window.imprimerBilletThermique($(this).data('id'));
    });

    $(document).on('click', '.thermal-print-colis-btn', function (e) {
        e.preventDefault();
        window.imprimerColisThermique($(this).data('id'));
    });
})();
