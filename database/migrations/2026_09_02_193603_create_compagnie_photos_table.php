<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photos de cars/bus d'une compagnie, utilisées dans les carrousels du site public
 * (vitrine de la compagnie) et de la page de connexion (mosaïque toutes compagnies
 * confondues — voir App\Models\CompagniePhoto).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compagnie_photos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_compagnie');
            $table->string('chemin', 255);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();

            $table->foreign('id_compagnie')->references('id_compagnie')->on('compagnie')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compagnie_photos');
    }
};
