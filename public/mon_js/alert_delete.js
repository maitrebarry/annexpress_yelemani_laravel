// document.addEventListener('DOMContentLoaded', function () {
//     var deleteButtons = document.querySelectorAll('.delete-button');

//     deleteButtons.forEach(function (button) {
//         button.addEventListener('click', function (event) {
//             event.preventDefault();

//             var deleteUrl = this.getAttribute('href');

//             Swal.fire({
//                 title: 'Êtes-vous sûr ?',
//                 text: "Cette action est irréversible !",
//                 icon: 'warning',
//                 showCancelButton: true,
//                 confirmButtonText: 'Oui, supprimer !',
//                 cancelButtonText: 'Annuler',
//                 customClass: {
//                     confirmButton: 'btn btn-danger',
//                     cancelButton: 'btn btn-primary'
//                 },
//                 buttonsStyling: false
//             }).then((result) => {
//                 if (result.isConfirmed) {
//                     Swal.fire({
//                         title: "Supprimé !",
//                         text: "Votre suppression a été faite avec succès.",
//                         icon: "success",
//                         showConfirmButton: false,
//                         timer: 2000
//                     }).then(() => {
//                         // Redirection vers la suppression réelle
//                         window.location.href = deleteUrl;
//                     });
//                 }
//             });
//         });
//     });
// });



document.addEventListener('DOMContentLoaded', function () {
    var deleteButtons = document.querySelectorAll('.delete-button');

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();

            var deleteUrl = this.getAttribute('href');

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
                        confirmButtonText: "OK", // 👈 Bouton OK ici
                        customClass: {
                            confirmButton: 'btn btn-primary' // 💚 Bouton vert (ou selon ton style)
                        },
                        buttonsStyling: false
                    }).then(() => {
                        // Redirection vers la suppression réelle
                        window.location.href = deleteUrl;
                    });
                }
            });
        });
    });
});

