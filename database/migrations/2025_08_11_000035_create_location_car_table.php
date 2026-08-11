<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_car', function (Blueprint $table) {
            $table->integer('id_location', true, false);
            $table->integer('id_compagnie');
            $table->integer('id_agence_depart');
            $table->string('destination', 150);
            $table->integer('id_car');
            $table->integer('id_caisse')->nullable();
            $table->integer('id_caisse_user')->nullable();
            $table->string('nom_client', 100);
            $table->string('prenom_client', 100);
            $table->string('telephone_client', 30);
            $table->date('date_depart');
            $table->date('date_retour_prevu');
            $table->integer('frais_location');
            $table->string('statut', 20)->default('en_attente');
            $table->integer('id_utilisateur');
            $table->dateTime('date_enregistrement')->useCurrent();
            $table->index(['id_agence_depart'], 'id_agence_depart');
            $table->index(['id_car'], 'id_car');
            $table->index(['id_compagnie'], 'id_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_car');
    }
};
