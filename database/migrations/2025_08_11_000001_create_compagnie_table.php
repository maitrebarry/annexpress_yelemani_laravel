<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compagnie', function (Blueprint $table) {
            $table->integer('id_compagnie', true, false);
            $table->string('nom_compagnie', 255);
            $table->string('libele', 255);
            $table->string('slogant', 100);
            $table->string('logo', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compagnie');
    }
};
