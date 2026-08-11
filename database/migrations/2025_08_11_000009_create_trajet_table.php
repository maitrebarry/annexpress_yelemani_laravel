<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trajet', function (Blueprint $table) {
            $table->integer('idTrajet', true, false);
            $table->string('depart', 100);
            $table->string('destination', 100);
            $table->integer('id_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trajet');
    }
};
