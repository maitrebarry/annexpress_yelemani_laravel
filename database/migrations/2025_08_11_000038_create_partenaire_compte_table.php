<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partenaire_compte', function (Blueprint $table) {
            $table->integer('id_partenaire', true, false);
            $table->string('nom_compagnie', 255);
            $table->string('email', 255);
            $table->string('mot_de_passe', 255);
            $table->string('telephone', 50)->nullable();
            $table->dateTime('date_creation');
            $table->unique(['email'], 'uniq_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partenaire_compte');
    }
};
