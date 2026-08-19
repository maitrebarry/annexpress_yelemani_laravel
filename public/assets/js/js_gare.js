// script pour la suppresion
document.addEventListener('DOMContentLoaded', function() {
    var deleteButtons = document.querySelectorAll('.delete-button');

    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            var idToDelete = this.getAttribute('ListGare');

            Swal.fire({
                title: "Êtes-vous sûr?",
                text: "Vous ne pourrez pas annuler cette action!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Oui, supprimer!",
                cancelButtonText: "Annuler"
            }).then((result) => {
                if (result.isConfirmed) {
                    swal.fire({
                        title: "Deleted!",
                        text: "Votre suppresin a ete faite avec succes.",
                        icon: "success"
                    }).then(() => {
                        // Rediriger vers le script de suppression avec l'ID
                        window.location.href =
                            "Suppression/suppressionGare.php?id=" +
                            idToDelete + "&confirm=true";
                    });

                }
            });
        });
    });
})
;
// fin du script



// script pour la verification
function formaterNumero(numero) {
    // Supprimer tout sauf les chiffres
    numero = numero.replace(/\D/g, '');

    // Vérifier la longueur (max 8 chiffres)
    if (numero.length > 8) {
        numero = numero.substring(0, 8);
    }

    // Appliquer le format XX XX XX XX
    return numero.replace(/(\d{2})(?=\d)/g, '$1 ');
}

function verifierNumero() {
    let input = document.getElementById("tel");
    let messageErreur = document.getElementById("messageErreur");

    // Récupérer et formater le numéro
    let numero = input.value.replace(/\D/g, '');
    input.value = formaterNumero(numero); // Toujours appliquer le formatage

    // Vérifier si le numéro commence par un chiffre interdit
    if (/^[1234]/.test(numero)) {
        messageErreur.textContent = "Le numéro ne doit pas commencer par 1, 2, 3 ou 4.";
        input.style.borderColor = "red";
        return false;
    }

    // Vérifier que le numéro contient exactement 8 chiffres
    if (numero.length !== 8) {
        messageErreur.textContent = "Le numéro doit contenir exactement 8 chiffres.";
        input.style.borderColor = "red";
        return false;
    }

    // Si tout est correct, réinitialiser les erreurs
    messageErreur.textContent = "";
    input.style.borderColor = "";
    return true;
}

// Fonction pour empêcher la soumission du formulaire si le numéro est invalide
function validerFormulaire() {
    return verifierNumero();
}