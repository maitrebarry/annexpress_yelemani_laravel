<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/ajout_salaires.sql — voir GESTION_SALAIRES.md.
 *
 * Permission::assignPermissionsParDefautPourRole() n'attribue les permissions par défaut
 * qu'à la CRÉATION d'un compte : les comptes Admin/PDG déjà existants avant ce module ne
 * l'auraient donc pas automatiquement, malgré le fait qu'elle leur soit censée être
 * accordée par défaut (voir Permission::NOMS_PERMISSIONS_PAR_DEFAUT, où 'Salaire_apercu'
 * est ajoutée séparément dans le code applicatif). Ce backfill rétroactif comble l'écart.
 */
return new class extends Migration
{
    public function up(): void
    {
        $idPermission = DB::table('permision')->where('nom_permission', 'Salaire_apercu')->value('id_permision');

        if (! $idPermission) {
            $idPermission = DB::table('permision')->insertGetId(['nom_permission' => 'Salaire_apercu']);
        }

        $utilisateurs = DB::table('utilisateur')->whereIn('droit', ['Admin', 'PDG'])->pluck('idUser');

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
        $idPermission = DB::table('permision')->where('nom_permission', 'Salaire_apercu')->value('id_permision');

        if ($idPermission) {
            DB::table('user_permission')->where('permission_id', $idPermission)->delete();
            DB::table('permision')->where('id_permision', $idPermission)->delete();
        }
    }
};
