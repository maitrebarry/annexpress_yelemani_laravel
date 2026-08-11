<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depots_banque', function (Blueprint $table) {
            $table->integer('id_depot', true, false);
            $table->integer('id_compagnie');
            $table->integer('id_agence');
            $table->integer('id_caisse');
            $table->integer('id_banque');
            $table->decimal('montant', 12, 2);
            $table->string('reference', 100)->nullable();
            $table->enum('statut', ['en_attente','confirme','rejete'])->default('en_attente');
            $table->string('motif_rejet', 255)->nullable();
            $table->integer('id_utilisateur_demandeur');
            $table->integer('id_utilisateur_validateur')->nullable();
            $table->dateTime('date_demande')->useCurrent();
            $table->dateTime('date_validation')->nullable();
            $table->index(['id_banque'], 'id_banque');
            $table->foreign('id_banque', 'depots_banque_ibfk_1')->references('id_banque')->on('banques');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depots_banque');
    }
};
