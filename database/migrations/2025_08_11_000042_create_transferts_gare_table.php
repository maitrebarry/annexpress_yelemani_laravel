<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferts_gare', function (Blueprint $table) {
            $table->integer('id_transfert', true, false);
            $table->integer('id_compagnie');
            $table->integer('id_agence_source');
            $table->integer('id_agence_destination');
            $table->integer('id_programmation_source');
            $table->integer('id_programmation_destination');
            $table->integer('id_caisse_source');
            $table->integer('id_caisse_destination');
            $table->integer('nombre_billets');
            $table->integer('nombre_passagers');
            $table->decimal('montant_total', 12, 2);
            $table->integer('id_utilisateur');
            $table->dateTime('date_transfert')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferts_gare');
    }
};
