<?php

// Comptes super_admin garantis présents en base (voir Utilisateur::seedSuperAdminsParDefaut()).
// Les mots de passe viennent de l'environnement, jamais du code : ne pas les committer.
return [
    ['nom' => 'Aminata Diallo', 'email' => 'amitacompt9@gmail.com', 'motPasse' => env('SUPER_ADMIN_1_PASSWORD')],
    ['nom' => 'Rokhaya Djiré', 'email' => 'rokhayadjire5@gmail.com', 'motPasse' => env('SUPER_ADMIN_2_PASSWORD')],
    ['nom' => 'Barry', 'email' => 'maitredjkbarry@icloud.com', 'motPasse' => env('SUPER_ADMIN_3_PASSWORD')],
];
