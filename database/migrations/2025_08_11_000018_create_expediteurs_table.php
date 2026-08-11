<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expediteurs', function (Blueprint $table) {
            $table->integer('id_expediteur', true, false);
            $table->string('expediteur', 100)->nullable();
            $table->string('numero_exp', 100)->nullable();
            $table->string('whatsapp_exp', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expediteurs');
    }
};
