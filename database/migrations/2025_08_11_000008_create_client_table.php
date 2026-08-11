<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client', function (Blueprint $table) {
            $table->integer('idClient', true, false);
            $table->string('Client', 250);
            $table->string('numeroClient', 250)->nullable();
            $table->string('numeroPaiement', 20)->nullable();
            $table->string('emailClient', 250)->nullable();
            $table->string('codeQuere', 250)->nullable();
            $table->string('montant_payer', 100)->nullable();
            $table->date('date_enregistrement');
            $table->integer('id_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client');
    }
};
