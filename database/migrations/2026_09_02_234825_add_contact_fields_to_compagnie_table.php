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
        Schema::table('compagnie', function (Blueprint $table) {
            $table->string('telephone', 30)->nullable()->after('logo');
            $table->string('email', 150)->nullable()->after('telephone');
            $table->string('adresse', 255)->nullable()->after('email');
            $table->string('facebook', 255)->nullable()->after('adresse');
            $table->string('instagram', 255)->nullable()->after('facebook');
            $table->string('whatsapp', 30)->nullable()->after('instagram');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compagnie', function (Blueprint $table) {
            $table->dropColumn(['telephone', 'email', 'adresse', 'facebook', 'instagram', 'whatsapp']);
        });
    }
};
