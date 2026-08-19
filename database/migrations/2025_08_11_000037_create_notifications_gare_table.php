<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_gare', function (Blueprint $table) {
            $table->integer('id_notification', true, false);
            $table->integer('id_compagnie');
            $table->integer('id_agence')->nullable();
            $table->integer('id_suivis')->nullable();
            $table->string('type', 50)->default('suivis_plein');
            $table->string('depart', 240);
            $table->string('destination', 240);
            $table->time('heur_depart');
            $table->date('date_reservation');
            $table->integer('place_totals');
            $table->integer('place_reserve');
            $table->enum('statut', ['non_lue','lue','resolue'])->default('non_lue');
            $table->enum('action_resolution', ['reactiver','fermer'])->nullable();
            $table->dateTime('date_creation')->useCurrent();
            $table->dateTime('date_resolution')->nullable();
            $table->integer('resolue_par')->nullable();
            $table->index(['id_agence', 'statut'], 'idx_agence_statut');
            $table->index(['id_compagnie', 'statut'], 'idx_compagnie_statut');
            $table->index(['id_suivis'], 'idx_notifications_gare_suivis');
            $table->index(['resolue_par'], 'resolue_par');
            $table->foreign('id_suivis', 'notifications_gare_ibfk_1')->references('idSuivis')->on('suivis')->onDelete('set null');
            $table->foreign('id_agence', 'notifications_gare_ibfk_2')->references('idAgence')->on('agence')->onDelete('set null');
            $table->foreign('resolue_par', 'notifications_gare_ibfk_3')->references('idUser')->on('utilisateur')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_gare');
    }
};
