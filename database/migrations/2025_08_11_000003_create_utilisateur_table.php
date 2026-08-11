<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateur', function (Blueprint $table) {
            $table->integer('idUser', true, false);
            $table->string('utilisateurs', 250);
            $table->string('droit', 250);
            $table->integer('contact')->nullable();
            $table->string('motPasse', 100);
            $table->integer('status')->nullable();
            $table->string('emailUser', 250);
            $table->string('telephone', 20)->nullable();
            $table->string('id_agence', 100)->nullable();
            $table->string('id_compagnie', 100)->nullable();
            $table->string('profile', 250)->nullable();
            $table->string('photo', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateur');
    }
};
