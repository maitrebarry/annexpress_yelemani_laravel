<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Port de Projets_licence/ajout_bagages.sql — bagages payants associés à un billet : un
 * passager peut avoir plusieurs bagages, saisis soit au moment de la vente du billet, soit
 * après coup (depuis la liste des tickets). Pas de formule de calcul automatique : l'agent
 * pèse le bagage et saisit le prix à la main.
 *
 * Pas de FOREIGN KEY (ce schéma n'en utilise nulle part) : cohérence déléguée au code
 * applicatif, comme partout ailleurs dans ce projet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bagages', function (Blueprint $table) {
            $table->integer('id_bagage', true, false);
            $table->integer('idBillets');
            $table->string('description', 255)->nullable();
            $table->decimal('poids', 6, 2)->nullable();
            $table->decimal('prix', 10, 2);
            $table->integer('id_compagnie');
            $table->integer('id_utilisateur');
            $table->dateTime('date_enregistrement')->useCurrent();

            $table->index('idBillets', 'idx_bagages_idBillets');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bagages');
    }
};
