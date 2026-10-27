<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Port de Projets_licence/ajout_bordereau_escale.sql — saisie dans le système des
 * bordereaux d'escale papier (escales isolées sans électricité ni réseau). Deux tables
 * d'audit qui conservent l'écriture exacte du papier, ligne par ligne, sans créer de faux
 * billets individuels (le papier ne capture que des comptages agrégés, pas de passager
 * nommé ni de numéro de place).
 *
 * Pas de FOREIGN KEY (ce schéma n'en utilise nulle part) : cohérence déléguée au code
 * applicatif, comme partout ailleurs dans ce projet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bordereau_escale', function (Blueprint $table) {
            $table->integer('id_bordereau', true, false);
            $table->string('numero_bordereau', 40)->nullable();
            $table->date('date_escale');
            $table->string('numero_bus', 50)->nullable();
            $table->string('escale_libelle', 150)->nullable();
            $table->string('nom_agent_escale', 150)->nullable();
            $table->integer('id_agence');
            $table->integer('id_compagnie');
            $table->integer('id_caisse_user')->nullable();
            $table->decimal('total_billets', 12, 2)->default(0);
            $table->decimal('total_bagages', 12, 2)->default(0);
            $table->decimal('montant_depenses', 12, 2)->default(0);
            $table->decimal('montant_commission', 12, 2)->default(0);
            $table->decimal('total_verse_saisi', 12, 2)->default(0);
            $table->integer('saisi_par');
            $table->dateTime('date_saisie')->useCurrent();

            $table->index('date_escale', 'idx_bordereau_date');
            $table->index('id_agence', 'idx_bordereau_agence');
        });

        Schema::create('bordereau_escale_lignes', function (Blueprint $table) {
            $table->integer('id_ligne', true, false);
            $table->integer('id_bordereau');
            $table->enum('type_ligne', ['billet', 'bagage']);
            $table->string('destination', 150)->nullable();
            $table->integer('nombre')->nullable();
            $table->decimal('prix_unitaire', 10, 2)->nullable();
            $table->string('description', 255)->nullable();
            $table->decimal('montant', 10, 2);

            $table->index('id_bordereau', 'idx_lignes_bordereau');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bordereau_escale_lignes');
        Schema::dropIfExists('bordereau_escale');
    }
};
