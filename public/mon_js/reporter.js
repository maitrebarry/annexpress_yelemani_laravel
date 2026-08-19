$(document).ready(function () {
    $('.report-btn').click(function (e) {
        e.preventDefault();

        let idClient = $(this).data('idclient');
        let jourVoyage = $(this).data('jour_voyage');
        let destinationId = $(this).data('destinationid');
        let dateExpiration = $(this).data('date_expiration');
        let heureActuelle = $(this).data('heure_actuelle');

        let today = new Date();
        let expirationDate = new Date(dateExpiration);
        let voyageDate = new Date(jourVoyage);

        today.setHours(0, 0, 0, 0);
        expirationDate.setHours(0, 0, 0, 0);
        voyageDate.setHours(0, 0, 0, 0);

        if (today <= expirationDate) {
            let minDate = voyageDate.toISOString().split('T')[0];
            let maxDateObj = new Date(voyageDate);
            maxDateObj.setDate(maxDateObj.getDate() + 7);
            let maxDate = maxDateObj.toISOString().split('T')[0];

            $('#nouvelleDate').attr('min', minDate);
            $('#nouvelleDate').attr('max', maxDate);
            $('#nouvelleDate').val(minDate);

            $('#dateExpiration').val(dateExpiration);
            $('#destination').val(destinationId);
            $('input[name="idClient"]').val(idClient);

            $.ajax({
                url: BASE_URL + '/Liste_du_jours/getHeuresDisponibles',
                method: 'POST',
                data: {
                    destination_id: destinationId
                },
                success: function (response) {
                    let heures = JSON.parse(response);
                    let heureSelect = $('#heureDepartSelect');
                    heureSelect.empty();
                    heureSelect.append('<option value="">Choisissez une heure de départ</option>');

                    if (heures.length === 0) {
                        heureSelect.append('<option disabled>Aucune heure disponible</option>');
                    } else {
                        let ancienneHeureDansListe = false;

                        heures.forEach(function (h) {
                            let selected = '';
                            if (h === heureActuelle) {
                                selected = 'selected';
                                ancienneHeureDansListe = true;
                            }
                            heureSelect.append('<option value="' + h + '" ' + selected + '>' + h + '</option>');
                        });

                        if (!ancienneHeureDansListe && heureActuelle) {
                            heureSelect.prepend('<option value="' + heureActuelle + '" selected disabled>' + heureActuelle + ' (ancienne)</option>');
                        }
                    }
                },
                error: function () {
                    alert("Erreur lors du chargement des heures.");
                }
            });

            $('#exampleDangerModal').modal('show');
        } else {
            alert("La période de modification de ce voyage est expirée !");
        }
    });
});
