<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suivis', function (Blueprint $table) {
            $table->integer('idSuivis', true, false);
            $table->integer('place_totals');
            $table->integer('place_reserve');
            $table->enum('statut', ['ouvert','ferme'])->default('ouvert');
            $table->integer('nombre_paliers')->default(1);
            $table->string('destination', 240);
            $table->time('heur_depart');
            $table->date('date_reservation');
            $table->string('depart', 100);
            $table->integer('id_agence')->nullable();
            $table->integer('id_compagnie');
            $table->unique(['depart', 'id_agence', 'destination', 'heur_depart', 'date_reservation', 'id_compagnie'], 'uniq_suivis_creneau_agence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suivis');
    }
};
