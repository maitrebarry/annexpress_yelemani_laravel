<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation', function (Blueprint $table) {
            $table->integer('id_reservation', true, false);
            $table->string('nom_prenom', 250);
            $table->string('idDepart', 250);
            $table->string('idDestination', 250);
            $table->integer('nombrePassager');
            $table->date('date');
            $table->time('horaire');
            $table->string('telephone', 50);
            $table->string('email', 250);
            $table->integer('id_compagnie');
            $table->double('prix');
            $table->double('prixTotal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation');
    }
};
