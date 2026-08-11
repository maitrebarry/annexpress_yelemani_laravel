<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banques', function (Blueprint $table) {
            $table->integer('id_banque', true, false);
            $table->integer('id_compagnie');
            $table->string('nom', 100);
            $table->string('numero_compte', 50)->nullable();
            $table->decimal('solde', 12, 2)->default(0.00);
            $table->enum('statut', ['active','inactive'])->default('active');
            $table->dateTime('date_creation')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banques');
    }
};
