<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envoi', function (Blueprint $table) {
            $table->integer('id_envoi', true, false);
            $table->string('id_coli', 100)->nullable();
            $table->integer('id_car')->nullable();
            $table->dateTime('date_enregistre')->nullable();
            $table->integer('id_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envoi');
    }
};
