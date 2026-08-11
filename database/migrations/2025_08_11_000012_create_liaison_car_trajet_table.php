<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liaison_car_trajet', function (Blueprint $table) {
            $table->integer('id_liaison', true, false);
            $table->integer('id_car');
            $table->integer('id_trajets');
            $table->integer('id_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liaison_car_trajet');
    }
};
