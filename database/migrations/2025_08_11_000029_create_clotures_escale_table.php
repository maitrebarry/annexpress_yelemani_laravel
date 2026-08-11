<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clotures_escale', function (Blueprint $table) {
            $table->integer('id_cloture', true, false);
            $table->integer('id_chef_escale');
            $table->integer('id_agence');
            $table->integer('id_compagnie');
            $table->date('date_cloture');
            $table->decimal('total_billets', 12, 2)->default(0.00);
            $table->decimal('total_colis', 12, 2)->default(0.00);
            $table->decimal('total_versements', 12, 2)->default(0.00);
            $table->decimal('total_ecarts', 12, 2)->default(0.00);
            $table->enum('statut', ['brouillon','validee'])->default('brouillon');
            $table->text('rapport_json')->nullable();
            $table->dateTime('date_creation')->useCurrent();
            $table->index(['id_agence'], 'idx_agence');
            $table->index(['date_cloture'], 'idx_date_cloture');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clotures_escale');
    }
};
