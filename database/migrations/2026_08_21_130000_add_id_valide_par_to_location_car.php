<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Colonne ajoutée au legacy après la création initiale de la table (voir
 * Projets_licence/ajout_location_car_valide_par.sql) mais absente de la migration
 * d'origine de ce port — nécessaire pour afficher qui a validé une location.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE location_car ADD COLUMN id_valide_par INT(11) NULL DEFAULT NULL AFTER statut');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE location_car DROP COLUMN id_valide_par');
    }
};
