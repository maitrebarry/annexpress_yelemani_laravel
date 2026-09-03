<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('actualites', function (Blueprint $table) {
            $table->id();
            $table->integer('id_compagnie');
            $table->string('titre', 255);
            $table->text('contenu');
            $table->string('image', 255)->nullable();
            $table->date('date_publication');
            $table->timestamps();

            $table->foreign('id_compagnie')->references('id_compagnie')->on('compagnie')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actualites');
    }
};
