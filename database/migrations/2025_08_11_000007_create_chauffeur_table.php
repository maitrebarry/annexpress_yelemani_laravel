<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chauffeur', function (Blueprint $table) {
            $table->integer('id_chauffeur', true, false);
            $table->string('nom_prenom', 200);
            $table->string('numero', 40);
            $table->integer('id_car');
            $table->integer('id_compagnie');
            $table->string('photo', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chauffeur');
    }
};
