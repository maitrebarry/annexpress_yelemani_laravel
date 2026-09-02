<?php

// Fenêtre de réservation à l'avance (achat de billet), guichet comme en ligne.
// Port de la règle "aujourd'hui à J+6" de Projets_licence (Add_billet::saveBillets(),
// Reservations_ligne::saveBilletsEnligne(), Reservation_formulaire.php) — dupliquée en
// dur à 4 endroits côté legacy faute de constante centrale ; centralisée ici pour ne
// pas reproduire ce problème côté port Laravel.
return [
    'jours_reservation_avance' => (int) env('BILLETS_JOURS_RESERVATION_AVANCE', 6),
];
