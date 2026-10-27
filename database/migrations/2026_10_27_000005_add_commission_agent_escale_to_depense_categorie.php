<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Le module Bordereau d'escale règle la commission de l'agent via une `Depense`,
        // catégorie absente de l'ENUM d'origine (même pattern que "Remboursement colis").
        DB::statement("ALTER TABLE depense MODIFY categorie ENUM('Carburant','Entretien/Reparation','Peage','Fournitures','Communication','Salaire','Loyer','Assurance','Remboursement annulation','Remboursement colis','Commission agent d''escale','Autre') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE depense MODIFY categorie ENUM('Carburant','Entretien/Reparation','Peage','Fournitures','Communication','Salaire','Loyer','Assurance','Remboursement annulation','Remboursement colis','Autre') NOT NULL");
    }
};
