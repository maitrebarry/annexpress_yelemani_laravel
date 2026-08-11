<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmation_voyage', function (Blueprint $table) {
            $table->integer('id_programmation', true, false);
            $table->integer('id_car_programmer');
            $table->time('id_horaire');
            $table->string('id_trajet', 100);
            $table->integer('id_agence_destination')->nullable();
            $table->string('localite_user', 100)->nullable();
            $table->integer('id_agence')->nullable();
            $table->integer('id_suivis')->nullable();
            $table->integer('nbr_places_affectees')->nullable();
            $table->date('date_enregistre');
            $table->integer('id_compagnie');
            $table->enum('statut', ['active','annulee'])->default('active');
            $table->dateTime('decolle_le')->nullable();
            $table->integer('decolle_par')->nullable();
            $table->index(['id_suivis'], 'id_suivis');
            $table->foreign('id_suivis', 'programmation_voyage_ibfk_1')->references('idSuivis')->on('suivis')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programmation_voyage');
    }
};
