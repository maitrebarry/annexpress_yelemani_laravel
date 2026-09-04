<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Port de Projets_licence/ajout_salaires.sql — voir GESTION_SALAIRES.md.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletin_paie', function (Blueprint $table) {
            $table->integer('id_bulletin', true, false);
            $table->integer('id_employe');
            $table->string('periode', 7);
            $table->integer('salaire_verse');
            $table->dateTime('date_generation');
            $table->integer('genere_par')->nullable();
            $table->integer('id_compagnie');

            $table->index('id_employe');
            $table->index('id_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_paie');
    }
};
