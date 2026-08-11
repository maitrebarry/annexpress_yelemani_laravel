<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caisse_utilisateur', function (Blueprint $table) {
            $table->integer('id_caisse_user', true, false);
            $table->integer('id_utilisateur');
            $table->integer('id_agence');
            $table->integer('id_compagnie');
            $table->date('date_service');
            $table->dateTime('heure_ouverture');
            $table->dateTime('heure_fermeture')->nullable();
            $table->decimal('montant_initial', 12, 2)->default(0.00);
            $table->decimal('total_billets', 12, 2)->default(0.00);
            $table->decimal('total_colis', 12, 2)->default(0.00);
            $table->decimal('montant_depense', 12, 2)->default(0.00);
            $table->decimal('montant_location', 12, 2)->default(0.00);
            $table->integer('nb_billets')->default(0);
            $table->integer('nb_colis')->default(0);
            $table->decimal('montant_compte', 12, 2)->nullable();
            $table->decimal('ecart', 12, 2)->nullable();
            $table->enum('statut', ['ouverte','fermee','versee'])->default('ouverte');
            $table->string('reference', 40);
            $table->index(['id_agence'], 'idx_agence');
            $table->index(['date_service'], 'idx_date_service');
            $table->index(['id_utilisateur'], 'idx_utilisateur');
            $table->unique(['reference'], 'reference_unique');
            $table->unique(['id_utilisateur', 'date_service', 'statut'], 'user_date_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caisse_utilisateur');
    }
};
