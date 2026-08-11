<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partenaire_message', function (Blueprint $table) {
            $table->integer('id_message', true, false);
            $table->integer('id_partenaire');
            $table->string('auteur', 20);
            $table->text('message');
            $table->dateTime('date_envoi');
            $table->index(['id_partenaire'], 'idx_partenaire');
            $table->foreign('id_partenaire', 'fk_partenaire_message')->references('id_partenaire')->on('partenaire_compte')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partenaire_message');
    }
};
