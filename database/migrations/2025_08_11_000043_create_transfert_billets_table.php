<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfert_billets', function (Blueprint $table) {
            $table->integer('id_transfert_billet', true, false);
            $table->integer('id_transfert');
            $table->integer('idBillets');
            $table->string('ancien_num_gare', 50);
            $table->string('nouveau_num_gare', 50);
            $table->index(['id_transfert'], 'id_transfert');
            $table->foreign('id_transfert', 'transfert_billets_ibfk_1')->references('id_transfert')->on('transferts_gare');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfert_billets');
    }
};
