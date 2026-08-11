<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_caisse', function (Blueprint $table) {
            $table->integer('id_journal', true, false);
            $table->integer('id_caisse_user');
            $table->integer('id_utilisateur');
            $table->enum('type_operation', ['ouverture','billet','colis','fermeture','versement','annulation']);
            $table->string('reference_op', 100)->nullable();
            $table->decimal('montant', 12, 2)->default(0.00);
            $table->string('libelle', 255)->nullable();
            $table->dateTime('date_heure')->useCurrent();
            $table->index(['id_caisse_user'], 'idx_caisse_user');
            $table->index(['date_heure'], 'idx_date_heure');
            $table->index(['id_utilisateur'], 'idx_utilisateur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_caisse');
    }
};
