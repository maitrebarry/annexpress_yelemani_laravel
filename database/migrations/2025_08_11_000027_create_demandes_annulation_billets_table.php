<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_annulation_billets', function (Blueprint $table) {
            $table->integer('id_demande', true, false);
            $table->integer('id_compagnie');
            $table->integer('id_billet');
            $table->string('motif', 255)->nullable();
            $table->enum('statut', ['en_attente','confirmee','rejetee'])->default('en_attente');
            $table->string('motif_rejet', 255)->nullable();
            $table->integer('id_utilisateur_demandeur');
            $table->integer('id_utilisateur_validateur')->nullable();
            $table->dateTime('date_demande')->useCurrent();
            $table->dateTime('date_validation')->nullable();
            $table->index(['id_billet'], 'id_billet');
            $table->foreign('id_billet', 'demandes_annulation_billets_ibfk_1')->references('idBillets')->on('billets');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_annulation_billets');
    }
};
