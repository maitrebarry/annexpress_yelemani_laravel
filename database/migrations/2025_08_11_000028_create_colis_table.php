<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colis', function (Blueprint $table) {
            $table->integer('id_colis', true, false);
            $table->string('nom_colis', 100)->nullable();
            $table->string('nature', 100)->nullable();
            $table->string('provient_de', 100)->nullable();
            $table->integer('id_agence')->nullable();
            $table->integer('valeur')->nullable();
            $table->integer('fraix_transaction')->nullable();
            $table->integer('id_utilisateur')->nullable();
            $table->integer('id_expediteur')->nullable();
            $table->integer('id_destinataire')->nullable();
            $table->date('date_enregistrement')->nullable();
            $table->string('code_colis', 255)->nullable();
            $table->string('num_gare', 100)->nullable();
            $table->string('status', 255);
            $table->date('date_livraison')->nullable();
            $table->integer('reclamer')->nullable();
            $table->date('date_reclamer')->nullable();
            $table->string('livre', 255)->nullable();
            $table->integer('id_compagnie');
            $table->text('motif_reclamation')->nullable();
            $table->integer('montant_remboursement')->nullable()->default(0);
            $table->string('status_reclamation', 50)->nullable();
            $table->integer('id_caisse_user')->nullable();
            $table->index(['id_destinataire'], 'id_destinataire');
            $table->index(['id_expediteur'], 'id_expediteur');
            $table->index(['id_utilisateur'], 'id_utilisateur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colis');
    }
};
