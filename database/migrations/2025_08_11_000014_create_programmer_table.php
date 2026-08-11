<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmer', function (Blueprint $table) {
            $table->integer('idProgrammer', true, false);
            $table->string('idDepart', 250);
            $table->string('idDestination', 250);
            $table->time('heureDepart');
            $table->time('rdv');
            $table->integer('prix');
            $table->integer('id_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programmer');
    }
};
