<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escale', function (Blueprint $table) {
            $table->integer('id_escale', true, false);
            $table->string('escales', 255);
            $table->integer('id_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escale');
    }
};
