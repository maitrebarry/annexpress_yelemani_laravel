<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Port de Projets_licence/ajout_camions.sql — voir GESTION_CAMIONS_COLIS.md. Un chauffeur
 * conduit désormais un car OU un camion (jamais les deux) : id_car devient nullable,
 * id_camion est ajouté (nullable), type_vehicule indique lequel des deux fait foi.
 * Cohérence "exactement un des deux renseigné" imposée par le code applicatif
 * (Chauffeur::store()/update()), pas par une contrainte SQL — comme partout ailleurs
 * dans ce schéma (aucune FOREIGN KEY/CHECK nulle part).
 */
return new class extends Migration
{
    public function up(): void
    {
        // MODIFY COLUMN (rendre id_car nullable) : raw SQL plutôt que ->change(), qui
        // nécessiterait d'ajouter doctrine/dbal comme dépendance rien que pour cette
        // migration — aucune autre migration de ce projet ne s'en sert.
        DB::statement('ALTER TABLE chauffeur MODIFY id_car INT NULL');

        Schema::table('chauffeur', function (Blueprint $table) {
            $table->integer('id_camion')->nullable()->after('id_car');
            $table->enum('type_vehicule', ['car', 'camion'])->default('car')->after('id_camion');
        });

        // Backfill : tous les chauffeurs existants sont déjà rattachés à un car.
        DB::table('chauffeur')->whereNotNull('id_car')->update(['type_vehicule' => 'car']);
    }

    public function down(): void
    {
        Schema::table('chauffeur', function (Blueprint $table) {
            $table->dropColumn(['id_camion', 'type_vehicule']);
        });
    }
};
