<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caisse', function (Blueprint $table) {
            $table->integer('id_caisse', true, false);
            $table->integer('id_user')->nullable();
            $table->string('type_caisse', 50)->nullable()->default('billetterie');
            $table->integer('id_compagnie');
            $table->integer('id_agence');
            $table->integer('montant_initial')->nullable();
            $table->integer('montant_billets');
            $table->integer('montant_colis');
            $table->date('date_enregistrement');
            $table->date('date_fermeture')->nullable();
            $table->string('reference_caise', 90);
            $table->integer('status_caisse')->nullable()->default(1)->comment('1=Ouverte, 2=Fermee, 3=Versee, 4=Validee');
            $table->integer('montant_rembourse')->nullable()->default(0);
            $table->decimal('montant_versements_recus', 12, 2)->nullable()->default(0.00);
            $table->integer('montant_depense')->default(0);
            $table->integer('montant_location')->default(0);
            $table->decimal('montant_attendu', 12, 2)->nullable()->default(0.00);
            $table->decimal('montant_reel', 12, 2)->nullable();
            $table->decimal('ecart', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caisse');
    }
};
