<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permision', function (Blueprint $table) {
            $table->integer('id_permision', true, false);
            $table->string('nom_permission', 240);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permision');
    }
};
