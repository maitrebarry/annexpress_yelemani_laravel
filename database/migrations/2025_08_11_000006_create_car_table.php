<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car', function (Blueprint $table) {
            $table->integer('id_car', true, false);
            $table->integer('numero_car');
            $table->string('matriculle', 100);
            $table->integer('nbr_place');
            $table->integer('nbr_place_reserve')->nullable();
            $table->string('programmer_car', 40);
            $table->integer('id_compagnie');
            $table->string('status_car', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car');
    }
};
