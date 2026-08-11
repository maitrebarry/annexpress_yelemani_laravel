<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agence', function (Blueprint $table) {
            $table->integer('idAgence', true, false);
            $table->integer('code');
            $table->string('localite', 250);
            $table->string('numeroGare', 11);
            $table->string('tel', 250);
            $table->integer('id_compagnie');
            $table->boolean('status')->nullable()->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agence');
    }
};
