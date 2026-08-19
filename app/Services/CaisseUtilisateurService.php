<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Caisse_utilisateur.php::crediterColis() (+ helpers privés).
 */
class CaisseUtilisateurService
{
    public function crediterColis(int $idUtilisateur, float $montant, string $codeColis, int $idColis): int|false
    {
        $caisse = DB::table('caisse_utilisateur')
            ->where('id_utilisateur', $idUtilisateur)
            ->whereDate('date_service', now()->toDateString())
            ->where('statut', 'ouverte')
            ->first();

        if (! $caisse) {
            return false;
        }

        DB::table('caisse_utilisateur')
            ->where('id_caisse_user', $caisse->id_caisse_user)
            ->update([
                'total_colis' => DB::raw('total_colis + '.$montant),
                'nb_colis' => DB::raw('nb_colis + 1'),
            ]);

        DB::table('colis')
            ->where('id_colis', $idColis)
            ->update(['id_caisse_user' => $caisse->id_caisse_user]);

        $this->insererJournal($caisse->id_caisse_user, $idUtilisateur, 'colis', $codeColis, $montant, "Colis $codeColis");

        return $caisse->id_caisse_user;
    }

    private function insererJournal(int $idCaisseUser, int $idUtilisateur, string $type, ?string $reference, float $montant, string $libelle): void
    {
        DB::table('journal_caisse')->insert([
            'id_caisse_user' => $idCaisseUser,
            'id_utilisateur' => $idUtilisateur,
            'type_operation' => $type,
            'reference_op' => $reference,
            'montant' => $montant,
            'libelle' => $libelle,
        ]);
    }
}
