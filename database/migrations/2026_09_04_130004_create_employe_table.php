<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Port de Projets_licence/ajout_salaires.sql — voir GESTION_SALAIRES.md. Table séparée de
 * `utilisateur`/`chauffeur` (pas de colonne salaire ajoutée directement dessus) : le nom
 * et les infos de contact restent la source de vérité sur utilisateur/chauffeur (lus par
 * jointure, jamais dupliqués) ; `employe` ne porte que les données propres à la paie.
 * Seul le personnel hors-système (sans compte ni fiche chauffeur) porte son propre nom
 * directement sur `employe`.
 *
 * Pas de FOREIGN KEY (ce schéma n'en utilise nulle part) : cohérence déléguée au code
 * applicatif, comme partout ailleurs dans ce projet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employe', function (Blueprint $table) {
            $table->integer('id_employe', true, false);
            $table->integer('id_utilisateur')->nullable();
            $table->integer('id_chauffeur')->nullable();
            $table->string('nom_prenom', 200)->nullable();
            $table->string('poste', 100);
            $table->integer('id_agence')->nullable();
            $table->integer('id_compagnie');
            $table->integer('salaire_base')->default(0);
            $table->string('statut', 10)->default('actif');
            $table->date('date_creation');

            $table->index('id_compagnie');
            $table->index('id_agence');
            $table->index('id_utilisateur');
            $table->index('id_chauffeur');
        });

        // Backfill : une fiche employe pour chaque compte utilisateur existant (hors
        // super_admin, qui n'est pas du personnel salarié de la compagnie) et pour chaque
        // chauffeur existant. Salaire à 0 par défaut -- l'Admin le renseigne ensuite depuis
        // l'écran Salaires, aucun montant n'est inventé ici.
        $utilisateurs = DB::table('utilisateur')->where('droit', '!=', 'super_admin')->get();
        foreach ($utilisateurs as $u) {
            DB::table('employe')->insert([
                'id_utilisateur' => $u->idUser,
                'poste' => $u->droit,
                'id_agence' => $u->id_agence,
                'id_compagnie' => $u->id_compagnie,
                'salaire_base' => 0,
                'statut' => 'actif',
                'date_creation' => now()->toDateString(),
            ]);
        }

        $chauffeurs = DB::table('chauffeur')->get();
        foreach ($chauffeurs as $c) {
            DB::table('employe')->insert([
                'id_chauffeur' => $c->id_chauffeur,
                'poste' => 'Chauffeur',
                'id_agence' => null,
                'id_compagnie' => $c->id_compagnie,
                'salaire_base' => 0,
                'statut' => 'actif',
                'date_creation' => now()->toDateString(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employe');
    }
};
