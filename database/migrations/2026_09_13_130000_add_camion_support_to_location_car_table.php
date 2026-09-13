<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Port de Projets_licence/ajout_location_camion.sql — voir GESTION_CAMIONS_COLIS.md. Une
 * location porte désormais sur un car OU un camion (jamais les deux) : id_car devient
 * nullable, id_camion est ajouté (nullable). Pas de colonne "type" séparée : le type se
 * déduit de savoir laquelle des deux colonnes est renseignée (même convention que
 * chauffeur.id_car/id_camion, cf. 2026_09_04_130002_add_camion_support_to_chauffeur_table).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE location_car MODIFY id_car INT NULL');

        Schema::table('location_car', function (Blueprint $table) {
            $table->integer('id_camion')->nullable()->after('id_car');
        });
    }

    public function down(): void
    {
        Schema::table('location_car', function (Blueprint $table) {
            $table->dropColumn('id_camion');
        });
    }
};
