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
        Schema::create('messages_contact', function (Blueprint $table) {
            $table->id();
            $table->integer('id_compagnie');
            $table->string('nom', 255);
            $table->string('telephone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('message');
            $table->string('origine', 20)->default('contact');
            $table->boolean('traite')->default(false);
            $table->timestamps();

            $table->foreign('id_compagnie')->references('id_compagnie')->on('compagnie')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages_contact');
    }
};
