<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billets', function (Blueprint $table) {
            $table->integer('idBillets', true, false);
            $table->integer('id_client');
            $table->integer('idUser')->nullable();
            $table->string('numeroBillets', 200);
            $table->date('jourVoyage');
            $table->time('Heur_departs');
            $table->integer('nombrePassages');
            $table->string('destinationId', 240);
            $table->string('departId', 240);
            $table->date('date_expiration');
            $table->string('numeroPlace', 49)->nullable();
            $table->date('date_reservation')->nullable();
            $table->string('status_billets', 100)->nullable();
            $table->string('statut_embarquement', 20)->nullable();
            $table->dateTime('embarque_le')->nullable();
            $table->integer('embarque_par')->nullable();
            $table->string('status_reservation', 80);
            $table->string('validation_billets', 100)->nullable();
            $table->date('date_repporte')->nullable();
            $table->integer('id_compagnie');
            $table->string('num_gare', 70)->nullable();
            $table->dateTime('delait_reservation')->nullable();
            $table->dateTime('date_annulation')->nullable();
            $table->string('motif_annulation', 255)->nullable();
            $table->integer('demande_annulation_par')->nullable();
            $table->dateTime('demande_annulation_le')->nullable();
            $table->integer('annule_par')->nullable();
            $table->date('nouvelle_date_demandee')->nullable();
            $table->time('nouvelle_heure_demandee')->nullable();
            $table->integer('demande_report_par')->nullable();
            $table->dateTime('demande_report_le')->nullable();
            $table->integer('report_transmis_par')->nullable();
            $table->dateTime('report_transmis_le')->nullable();
            $table->integer('id_caisse_user')->nullable();
            $table->unique(['numeroBillets'], 'uniq_numero_billets');
            $table->unique(['numeroBillets'], 'uq_numeroBillets');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billets');
    }
};
