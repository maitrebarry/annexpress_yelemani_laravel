<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/ajout_bagages.sql — convention "Billets_<action>" déjà en place
 * (Billets_creation, Billets_impression, ...). Permission::seedPermissionsParDefautSiVide()
 * ne s'exécute qu'au premier accès à l'écran Permissions sur une base neuve : les comptes
 * déjà existants qui ont Billets_creation n'auraient donc pas Billets_bagages
 * automatiquement — ce backfill rétroactif comble l'écart (même principe que
 * 2026_09_04_130006_add_salaire_apercu_permission.php pour Salaire_apercu).
 */
return new class extends Migration
{
    public function up(): void
    {
        $idPermission = DB::table('permision')->where('nom_permission', 'Billets_bagages')->value('id_permision');

        if (! $idPermission) {
            $idPermission = DB::table('permision')->insertGetId(['nom_permission' => 'Billets_bagages']);
        }

        $idBilletsCreation = DB::table('permision')->where('nom_permission', 'Billets_creation')->value('id_permision');
        if (! $idBilletsCreation) {
            return;
        }

        $utilisateurs = DB::table('user_permission')->where('permission_id', $idBilletsCreation)->pluck('user_id');

        foreach ($utilisateurs as $idUser) {
            $dejaAssignee = DB::table('user_permission')
                ->where('user_id', $idUser)
                ->where('permission_id', $idPermission)
                ->exists();

            if (! $dejaAssignee) {
                DB::table('user_permission')->insert(['user_id' => $idUser, 'permission_id' => $idPermission]);
            }
        }
    }

    public function down(): void
    {
        $idPermission = DB::table('permision')->where('nom_permission', 'Billets_bagages')->value('id_permision');

        if ($idPermission) {
            DB::table('user_permission')->where('permission_id', $idPermission)->delete();
            DB::table('permision')->where('id_permision', $idPermission)->delete();
        }
    }
};
