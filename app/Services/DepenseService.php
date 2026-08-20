<?php

namespace App\Services;

use App\Models\Agence;
use App\Models\CaisseUtilisateur;
use App\Models\Depense;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Depense.php. Chaque méthode mutante retourne
 * ['ok' => bool, 'type' => 'success'|'danger'|'warning', 'message' => string] — le
 * contrôleur se contente de relayer via Flash::set() (même convention que
 * CaisseUtilisateurService).
 */
class DepenseService
{
    public function saveDepense(Utilisateur $user, array $data): array
    {
        $portee = $data['portee'] ?? 'locale';
        $categorie = $data['categorie'] ?? null;
        $montant = $data['montant'] ?? null;

        if (! in_array($categorie, Depense::CATEGORIES, true)) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Catégorie de dépense invalide.'];
        }

        if (! is_numeric($montant) || (float) $montant <= 0) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Le montant de la dépense doit être un nombre positif.'];
        }

        if ($categorie === 'Autre' && trim((string) ($data['libelle'] ?? '')) === '') {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Le libellé est obligatoire pour la catégorie "Autre".'];
        }

        if ($portee === 'globale') {
            if ($user->droit !== 'Admin') {
                return ['ok' => false, 'type' => 'danger', 'message' => "Seul l'Admin peut enregistrer une dépense globale."];
            }

            Depense::create([
                'id_compagnie' => $user->id_compagnie,
                'categorie' => $categorie,
                'libelle' => $data['libelle'] ?? null,
                'montant' => $montant,
                'date_depense' => $data['date_depense'],
                'id_utilisateur' => $user->idUser,
                'statut' => 'valide',
            ]);

            return ['ok' => true, 'type' => 'success', 'message' => 'Dépense globale enregistrée avec succès.'];
        }

        // Dépense locale : rattachée à une caisse ouverte. Un chef d'escale ne peut viser
        // que sa propre gare, même si le formulaire est trafiqué.
        if ($user->droit === 'chef_d_escale') {
            $idAgence = $user->id_agence;
        } else {
            $idAgence = $data['id_agence'] ?? null;
            if (empty($idAgence)) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Veuillez choisir la gare concernée par cette dépense.'];
            }
        }

        // IDOR guard : un Admin ne peut débiter que les gares de sa propre compagnie.
        $agenceValide = Agence::where('idAgence', $idAgence)->where('id_compagnie', $user->id_compagnie)->exists();
        if (! $agenceValide) {
            return ['ok' => false, 'type' => 'danger', 'message' => "Cette gare n'appartient pas à votre compagnie."];
        }

        $idCaisse = null;
        $idCaisseUser = null;

        if ($user->droit === 'chef_d_escale') {
            // Le chef d'escale débite toujours SA PROPRE caisse individuelle du jour,
            // jamais celle d'un collègue.
            $caisseUser = CaisseUtilisateur::where('id_utilisateur', $user->idUser)
                ->whereDate('date_service', now()->toDateString())->where('statut', 'ouverte')->first();
            if (! $caisseUser) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Vous devez avoir votre propre caisse ouverte pour enregistrer une dépense.'];
            }
            $idCaisseUser = $caisseUser->id_caisse_user;
        } else {
            // Admin ciblant une gare précise : caisse de gare (ancien système, table
            // `caisse`) si ouverte — n'a jamais de ligne dans cette app, ce lookup ne
            // matche donc jamais et on tombe systématiquement sur la caisse individuelle
            // ouverte pour cette gare aujourd'hui, qui est le système réellement utilisé.
            $caisse = DB::table('caisse')->where('id_agence', $idAgence)->where('status_caisse', 1)->first();

            if ($caisse) {
                $idCaisse = $caisse->id_caisse;
            } else {
                $caisseUser = CaisseUtilisateur::where('id_agence', $idAgence)
                    ->whereDate('date_service', now()->toDateString())->where('statut', 'ouverte')->first();
                if ($caisseUser) {
                    $idCaisseUser = $caisseUser->id_caisse_user;
                }
            }

            if (! $idCaisse && ! $idCaisseUser) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Aucune caisse ouverte pour cette gare : impossible d\'enregistrer la dépense.'];
            }
        }

        $statut = $user->droit === 'Admin' ? 'valide' : 'en_attente';

        $depense = Depense::create([
            'id_compagnie' => $user->id_compagnie,
            'id_agence' => $idAgence,
            'id_caisse' => $idCaisse,
            'id_caisse_user' => $idCaisseUser,
            'categorie' => $categorie,
            'libelle' => $data['libelle'] ?? null,
            'montant' => $montant,
            'date_depense' => $data['date_depense'],
            'id_utilisateur' => $user->idUser,
            'statut' => $statut,
        ]);

        if ($statut === 'valide') {
            $this->deduireCaisse($depense->id_caisse, $depense->id_caisse_user, (float) $montant);

            return ['ok' => true, 'type' => 'success', 'message' => 'Dépense enregistrée avec succès et déduite de la caisse.'];
        }

        return ['ok' => true, 'type' => 'success', 'message' => 'Dépense enregistrée avec succès. Elle est en attente de validation par l\'administrateur.'];
    }

    private function deduireCaisse(?int $idCaisse, ?int $idCaisseUser, float $montant): void
    {
        if ($idCaisse) {
            DB::table('caisse')->where('id_caisse', $idCaisse)->update(['montant_depense' => DB::raw('montant_depense + '.$montant)]);
        } elseif ($idCaisseUser) {
            CaisseUtilisateur::where('id_caisse_user', $idCaisseUser)->update(['montant_depense' => DB::raw('montant_depense + '.$montant)]);
        }
    }

    // Liste des dépenses visibles selon le rôle : le chef d'escale ne voit que les
    // dépenses locales de sa propre gare, l'Admin/PDG voit tout (locales + globales).
    public function getDepenses(Utilisateur $user)
    {
        return Depense::query()
            ->leftJoin('agence as a', 'depense.id_agence', '=', 'a.idAgence')
            ->leftJoin('utilisateur as u', 'depense.id_utilisateur', '=', 'u.idUser')
            ->where('depense.id_compagnie', $user->id_compagnie)
            ->when($user->droit === 'chef_d_escale', fn ($q) => $q->where('depense.id_agence', $user->id_agence))
            ->orderByDesc('depense.date_depense')->orderByDesc('depense.id_depense')
            ->get(['depense.*', 'a.localite', 'a.numeroGare', 'u.utilisateurs as agent']);
    }

    public function getBenefice(int $idCompagnie, string $periode = 'jour', ?string $gareVille = null, ?string $jourDate = null): array
    {
        $filtreJour = fn ($q, string $colonne) => match ($periode) {
            'jour' => $q->whereDate($colonne, $jourDate ?? now()->toDateString()),
            'mois' => $q->whereMonth($colonne, now()->month)->whereYear($colonne, now()->year),
            default => $q,
        };

        $totalBillets = (float) (DB::table('billets as b')
            ->join('client as c', 'b.id_client', '=', 'c.idClient')
            ->where('b.id_compagnie', $idCompagnie)
            ->where('b.validation_billets', 'valider')
            ->where(fn ($q) => $q->whereNull('b.status_billets')->orWhere('b.status_billets', '!=', 'annule'))
            ->when($gareVille, fn ($q) => $q->where('b.departId', $gareVille))
            ->tap(fn ($q) => $filtreJour($q, 'b.date_reservation'))
            ->sum(DB::raw("CAST(REPLACE(REPLACE(c.montant_payer, ' ', ''), 'FCFA', '') AS DECIMAL(12,2))")) ?? 0);

        $totalColis = (float) (DB::table('colis')
            ->join('agence', 'colis.id_agence', '=', 'agence.idAgence')
            ->where('colis.id_compagnie', $idCompagnie)
            ->when($gareVille, fn ($q) => $q->where('agence.localite', $gareVille))
            ->tap(fn ($q) => $filtreJour($q, 'colis.date_enregistrement'))
            ->sum('colis.fraix_transaction') ?? 0);

        $totalRembourse = (float) (DB::table('caisse as c')
            ->join('agence as a', 'c.id_agence', '=', 'a.idAgence')
            ->where('a.id_compagnie', $idCompagnie)
            ->when($gareVille, fn ($q) => $q->where('a.localite', $gareVille))
            ->sum('c.montant_rembourse') ?? 0);

        $totalDepenseLocale = (float) (DB::table('depense')
            ->join('agence as a', 'depense.id_agence', '=', 'a.idAgence')
            ->where('depense.id_compagnie', $idCompagnie)
            ->where('depense.statut', 'valide')
            ->when($gareVille, fn ($q) => $q->where('a.localite', $gareVille))
            ->tap(fn ($q) => $filtreJour($q, 'depense.date_depense'))
            ->sum('depense.montant') ?? 0);

        // Les dépenses "globales" (id_agence IS NULL) ne sont rattachées à aucune gare :
        // elles ne doivent pas être imputées quand on filtre par gare.
        $totalDepenseGlobale = 0.0;
        if (! $gareVille) {
            $totalDepenseGlobale = (float) (DB::table('depense')
                ->where('id_compagnie', $idCompagnie)->whereNull('id_agence')->where('statut', 'valide')
                ->tap(fn ($q) => $filtreJour($q, 'date_depense'))
                ->sum('montant') ?? 0);
        }

        $totalLocation = (float) (DB::table('location_car as l')
            ->join('agence as a', 'l.id_agence_depart', '=', 'a.idAgence')
            ->where('l.id_compagnie', $idCompagnie)
            ->where('l.statut', 'valide')
            ->when($gareVille, fn ($q) => $q->where('a.localite', $gareVille))
            ->tap(fn ($q) => $filtreJour($q, 'l.date_depart'))
            ->sum('l.frais_location') ?? 0);

        $benefice = $totalBillets + $totalColis + $totalLocation - $totalRembourse - $totalDepenseLocale - $totalDepenseGlobale;

        return [
            'revenus_billets' => $totalBillets,
            'revenus_colis' => $totalColis,
            'revenus_location' => $totalLocation,
            'remboursements' => $totalRembourse,
            'depenses_locales' => $totalDepenseLocale,
            'depenses_globales' => $totalDepenseGlobale,
            'benefice' => $benefice,
        ];
    }

    public function validerDepense(int $id, int $idCompagnie): array
    {
        $depense = Depense::where('id_depense', $id)->where('id_compagnie', $idCompagnie)->first();
        if (! $depense) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Dépense introuvable.'];
        }

        if ($depense->statut !== 'en_attente') {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette dépense a déjà été traitée (validée ou rejetée).'];
        }

        // Compare-and-swap : évite qu'une validation et un rejet concurrents déduisent
        // tous les deux la caisse pour la même dépense.
        $affectee = Depense::where('id_depense', $id)->where('statut', 'en_attente')->update(['statut' => 'valide']);
        if (! $affectee) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette dépense a déjà été traitée entre-temps par quelqu\'un d\'autre.'];
        }

        if ($depense->id_caisse || $depense->id_caisse_user) {
            $this->deduireCaisse($depense->id_caisse, $depense->id_caisse_user, (float) $depense->montant);
        }

        return ['ok' => true, 'type' => 'success', 'message' => 'Dépense validée avec succès et déduite de la caisse.'];
    }

    public function rejeterDepense(int $id, int $idCompagnie): array
    {
        $depense = Depense::where('id_depense', $id)->where('id_compagnie', $idCompagnie)->first();
        if (! $depense) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Dépense introuvable.'];
        }

        if ($depense->statut !== 'en_attente') {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette dépense a déjà été traitée.'];
        }

        $affectee = Depense::where('id_depense', $id)->where('statut', 'en_attente')->update(['statut' => 'rejete']);
        if (! $affectee) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette dépense a déjà été traitée entre-temps par quelqu\'un d\'autre.'];
        }

        return ['ok' => true, 'type' => 'success', 'message' => 'Dépense rejetée avec succès.'];
    }
}
