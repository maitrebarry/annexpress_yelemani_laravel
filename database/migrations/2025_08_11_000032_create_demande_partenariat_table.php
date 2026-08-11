<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_partenariat', function (Blueprint $table) {
            $table->integer('id_demande', true, false);
            $table->string('nom_compagnie', 255);
            $table->string('email', 255);
            $table->string('telephone', 50)->nullable();
            $table->text('message');
            $table->text('reponse')->nullable();
            $table->string('statut', 20)->default('en_attente');
            $table->dateTime('date_demande');
            $table->dateTime('date_reponse')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_partenariat');
    }
};
