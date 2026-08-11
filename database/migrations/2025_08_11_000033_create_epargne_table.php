<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epargne', function (Blueprint $table) {
            $table->integer('id_epargne', true, false);
            $table->integer('id_client');
            $table->enum('type_operation', ['depot','retrait']);
            $table->decimal('montant', 10, 2);
            $table->string('reference_operation', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->dateTime('date_operation')->nullable()->useCurrent();
            $table->integer('id_compagnie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epargne');
    }
};
