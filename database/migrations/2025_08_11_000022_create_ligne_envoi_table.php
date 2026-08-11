<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_envoi', function (Blueprint $table) {
            $table->integer('id_ligne_envoi', true, false);
            $table->integer('numero_car');
            $table->dateTime('dates');
            $table->integer('id_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_envoi');
    }
};
