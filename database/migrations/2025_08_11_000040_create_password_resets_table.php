<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->integer('id', true, false);
            $table->string('email', 250);
            $table->string('token_hash', 64);
            $table->dateTime('expires_at');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['email'], 'idx_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};
