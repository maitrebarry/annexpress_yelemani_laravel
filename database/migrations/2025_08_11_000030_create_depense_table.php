<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depense', function (Blueprint $table) {
            $table->integer('id_depense', true, false);
            $table->integer('id_compagnie');
            $table->integer('id_agence')->nullable();
            $table->integer('id_caisse')->nullable();
            $table->integer('id_caisse_user')->nullable();
            $table->enum('categorie', ['Carburant','Entretien/Reparation','Peage','Fournitures','Communication','Salaire','Loyer','Assurance','Autre']);
            $table->string('libelle', 255)->nullable();
            $table->integer('montant');
            $table->date('date_depense');
            $table->integer('id_utilisateur')->nullable();
            $table->dateTime('date_enregistrement')->useCurrent();
            $table->enum('statut', ['en_attente','valide','rejete'])->default('valide');
            $table->index(['id_caisse_user'], 'idx_caisse_user');
            $table->index(['id_agence'], 'idx_depense_agence');
            $table->index(['id_caisse'], 'idx_depense_caisse');
            $table->index(['id_compagnie'], 'idx_depense_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depense');
    }
};
