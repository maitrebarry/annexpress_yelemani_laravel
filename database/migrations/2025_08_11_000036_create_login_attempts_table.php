<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->integer('id', true, false);
            $table->string('identifiant', 255);
            $table->integer('tentatives')->default(1);
            $table->dateTime('derniere_tentative');
            $table->dateTime('bloque_jusqu')->nullable();
            $table->unique(['identifiant'], 'uniq_identifiant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
