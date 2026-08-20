<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // La légende legacy (app/models/Depense.php) inclut "Remboursement annulation",
        // absente de la migration d'origine.
        DB::statement("ALTER TABLE depense MODIFY categorie ENUM('Carburant','Entretien/Reparation','Peage','Fournitures','Communication','Salaire','Loyer','Assurance','Remboursement annulation','Autre') NOT NULL");

        // Cohérence avec les autres colonnes monétaires de l'app (banques.solde,
        // depots_banque.montant sont déjà decimal(12,2)).
        DB::statement('ALTER TABLE depense MODIFY montant DECIMAL(12,2) NOT NULL');

        // L'ancienne caisse de gare (table `caisse`) n'a plus de ligne créée nulle part :
        // depots_banque.id_caisse reste en base pour parité de schéma mais n'est plus
        // renseignable, donc doit devenir nullable.
        DB::statement('ALTER TABLE depots_banque MODIFY id_caisse INT(11) NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE depense MODIFY categorie ENUM('Carburant','Entretien/Reparation','Peage','Fournitures','Communication','Salaire','Loyer','Assurance','Autre') NOT NULL");
        DB::statement('ALTER TABLE depense MODIFY montant INT(11) NOT NULL');
        DB::statement('ALTER TABLE depots_banque MODIFY id_caisse INT(11) NOT NULL');
    }
};
