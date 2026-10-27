<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // CaisseUtilisateurService::crediterBagage() journalise sous un type distinct
        // 'bagage', absent de l'ENUM d'origine (sans ça, MySQL tronquerait la valeur en
        // chaîne vide et l'insertion échouerait).
        DB::statement("ALTER TABLE journal_caisse MODIFY type_operation ENUM('ouverture','billet','colis','fermeture','versement','annulation','bagage') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE journal_caisse MODIFY type_operation ENUM('ouverture','billet','colis','fermeture','versement','annulation') NOT NULL");
    }
};
