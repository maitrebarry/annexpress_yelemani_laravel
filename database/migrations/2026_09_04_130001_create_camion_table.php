<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Port de Projets_licence/ajout_camions.sql — voir GESTION_CAMIONS_COLIS.md. Table
 * séparée de `car` (et non un type ajouté dessus) : la logique billetterie/programmation
 * couplée à `car` (nbr_place, programmer_car, programmation_voyage...) n'a pas de sens
 * pour un camion de colis, qui n'a qu'une notion de disponibilité on/off.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camion', function (Blueprint $table) {
            $table->integer('id_camion', true, false);
            $table->integer('numero_camion');
            $table->string('matriculle', 100);
            $table->string('actif', 10)->default('on');
            $table->integer('id_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camion');
    }
};
