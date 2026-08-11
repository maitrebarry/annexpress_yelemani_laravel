<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_car', function (Blueprint $table) {
            $table->integer('id_reference', true, false);
            $table->integer('id_car');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_car');
    }
};
