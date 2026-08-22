<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Le module Réclamation de colis rembourse via une `Depense` (même pattern que
        // "Remboursement annulation" pour les billets), catégorie absente de l'ENUM d'origine.
        DB::statement("ALTER TABLE depense MODIFY categorie ENUM('Carburant','Entretien/Reparation','Peage','Fournitures','Communication','Salaire','Loyer','Assurance','Remboursement annulation','Remboursement colis','Autre') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE depense MODIFY categorie ENUM('Carburant','Entretien/Reparation','Peage','Fournitures','Communication','Salaire','Loyer','Assurance','Remboursement annulation','Autre') NOT NULL");
    }
};
