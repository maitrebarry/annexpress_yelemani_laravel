// Délégation sur document (pas un binding direct par élément) : les liens .delete-button
// visés vivent dans le <body> régénéré à chaque navigation douce de l'admin (voir
// mon_js/admin-transitions.js) — un binding direct raterait tout nouveau lien apparu après
// la première exécution. Garde anti-double-exécution : ce script étant spécifique à
// certaines pages (pas chargé partout via foot.blade.php), une navigation douce peut le
// re-déclencher si la page qui le charge est quittée puis retrouvée ; sans cette garde,
// chaque ré-exécution ajouterait un nouvel écouteur en double sur document (confirmations
// affichées plusieurs fois).
(function () {
    if (window.__alertDeleteInit) return;
    window.__alertDeleteInit = true;

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.delete-button');
        if (!button) return;

        event.preventDefault();
        var deleteUrl = button.getAttribute('href');

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Cette action est irréversible !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer !',
            cancelButtonText: 'Annuler',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-primary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Supprimé !",
                    text: "Votre suppression a été faite avec succès.",
                    icon: "success",
                    confirmButtonText: "OK",
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                }).then(() => {
                    window.location.href = deleteUrl;
                });
            }
        });
    });
})();
