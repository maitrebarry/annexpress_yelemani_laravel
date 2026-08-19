<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versements_caisse', function (Blueprint $table) {
            $table->integer('id_versement', true, false);
            $table->integer('id_caisse_user');
            $table->integer('id_emetteur');
            $table->integer('id_chef_escale');
            $table->integer('id_agence');
            $table->integer('id_compagnie');
            $table->decimal('montant', 12, 2);
            $table->dateTime('date_versement')->useCurrent();
            $table->enum('statut', ['en_attente','valide','rejete'])->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->dateTime('date_validation')->nullable();
            $table->index(['id_agence'], 'idx_versements_caisse_agence');
            $table->index(['id_caisse_user'], 'idx_versements_caisse_user');
            $table->index(['id_chef_escale'], 'idx_chef_escale');
            $table->index(['statut'], 'idx_statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versements_caisse');
    }
};
