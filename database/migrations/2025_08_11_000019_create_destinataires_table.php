<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinataires', function (Blueprint $table) {
            $table->integer('id_destinataire', true, false);
            $table->string('destinataire', 100)->nullable();
            $table->string('numero_dest', 100)->nullable();
            $table->string('whatsapp_dest', 100)->nullable();
            $table->integer('id_exp')->nullable();
            $table->index(['id_exp'], 'id_exp');
            $table->foreign('id_exp', 'destinataires_ibfk_1')->references('id_expediteur')->on('expediteurs')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinataires');
    }
};
