<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versement', function (Blueprint $table) {
            $table->integer('id_versement', true, false);
            $table->integer('id_caisse_agent');
            $table->integer('id_caisse_chef')->nullable();
            $table->decimal('montant_verse', 12, 2);
            $table->dateTime('date_versement');
            $table->string('status_versement', 50)->nullable()->default('en_attente')->comment('en_attente, valide, rejete');
            $table->integer('id_chef')->nullable();
            $table->text('remarques')->nullable();
            $table->index(['id_caisse_agent'], 'fk_versement_caisse_agent');
            $table->index(['id_caisse_chef'], 'fk_versement_caisse_chef');
            $table->index(['id_chef'], 'fk_versement_chef');
            $table->foreign('id_caisse_agent', 'fk_versement_caisse_agent')->references('id_caisse')->on('caisse')->onDelete('cascade');
            $table->foreign('id_caisse_chef', 'fk_versement_caisse_chef')->references('id_caisse')->on('caisse')->onDelete('set null');
            $table->foreign('id_chef', 'fk_versement_chef')->references('idUser')->on('utilisateur')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versement');
    }
};
