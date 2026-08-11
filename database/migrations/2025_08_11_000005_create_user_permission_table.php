<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_permission', function (Blueprint $table) {
            $table->integer('id_user_permission', true, false);
            $table->integer('permission_id');
            $table->integer('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permission');
    }
};
