<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligneTrajet', function (Blueprint $table) {
            $table->integer('id_trajet_ligne', true, false);
            $table->integer('id_escales');
            $table->integer('id_trajets');
            $table->enum('type_trajet', ['trajet','programmer']);
            $table->integer('prix_escale')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligneTrajet');
    }
};
