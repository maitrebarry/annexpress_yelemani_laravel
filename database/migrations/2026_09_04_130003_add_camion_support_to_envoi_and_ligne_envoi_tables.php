<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Port de Projets_licence/ajout_camions.sql — voir GESTION_CAMIONS_COLIS.md. Un envoi de
 * colis peut désormais être affecté à un camion plutôt qu'à un car.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('envoi', function (Blueprint $table) {
            $table->integer('id_camion')->nullable()->after('id_car');
        });

        // numero_car (nom trompeur : stocke en réalité un id_car, convention historique
        // déjà présente avant cette fonctionnalité) doit devenir nullable pour permettre
        // une ligne "camion" du jour ; numero_camion est son miroir.
        DB::statement('ALTER TABLE ligne_envoi MODIFY numero_car INT NULL');

        Schema::table('ligne_envoi', function (Blueprint $table) {
            $table->integer('numero_camion')->nullable()->after('numero_car');
        });
    }

    public function down(): void
    {
        Schema::table('envoi', function (Blueprint $table) {
            $table->dropColumn('id_camion');
        });

        Schema::table('ligne_envoi', function (Blueprint $table) {
            $table->dropColumn('numero_camion');
        });
    }
};
