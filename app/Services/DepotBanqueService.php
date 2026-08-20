<?php

namespace App\Services;

use App\Models\Agence;
use App\Models\Banque;
use App\Models\DepotBanque;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Banque.php — partie "dépôt en banque"
 * (app/controllers/admin/Depots_banque.php). Workflow à deux temps : une demande
 * (en_attente) ne débite rien, seule sa confirmation par un Admin déplace l'argent
 * (versements_caisse -> banques.solde).
 *
 * Écart volontaire avec le legacy : celui-ci calculait le "solde disponible" depuis la
 * caisse de gare (table `caisse`), qui n'a aucune ligne dans cette app (système remplacé
 * par la caisse individuelle, voir CaisseUtilisateurService). Le solde disponible pour un
 * dépôt est donc recalculé ici comme un solde cumulatif : somme des versements
 * d'opérateurs déjà validés pour la gare, moins ce qui a déjà été déposé en banque —
 * confirmé avec l'utilisateur que ce solde peut s'accumuler sur plusieurs jours avant
 * qu'un dépôt ne soit fait (pas de remise à zéro quotidienne).
 */
class DepotBanqueService
{
    public function soldeDisponible(int $idAgence): float
    {
        $totalVerse = (float) (DB::table('versements_caisse')
            ->where('id_agence', $idAgence)->where('statut', 'valide')
            ->sum('montant') ?? 0);

        $totalDepose = (float) (DB::table('depots_banque')
            ->where('id_agence', $idAgence)->where('statut', 'confirme')
            ->sum('montant') ?? 0);

        return $totalVerse - $totalDepose;
    }

    public function creerDemande(Utilisateur $user, array $data): array
    {
        if (! in_array($user->droit, ['chef_d_escale', 'Admin'], true)) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Accès refusé.'];
        }

        if ($user->droit === 'chef_d_escale') {
            $idAgence = $user->id_agence;
        } else {
            $idAgence = $data['id_agence'] ?? null;
            $agenceValide = Agence::where('idAgence', $idAgence)->where('id_compagnie', $user->id_compagnie)->exists();
            if (! $agenceValide) {
                return ['ok' => false, 'type' => 'danger', 'message' => "Cette gare n'appartient pas à votre compagnie."];
            }
        }
        if (! $idAgence) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Veuillez choisir la gare concernée par ce dépôt.'];
        }

        $montant = $data['montant'] ?? null;
        if (! is_numeric($montant) || (float) $montant <= 0) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Le montant du dépôt doit être un nombre positif.'];
        }

        $banque = Banque::where('id_banque', $data['id_banque'] ?? null)
            ->where('id_compagnie', $user->id_compagnie)->where('statut', 'active')->first();
        if (! $banque) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Compte banque introuvable ou inactif.'];
        }

        if ((float) $montant > $this->soldeDisponible($idAgence)) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Le montant demandé dépasse le solde actuellement disponible pour cette gare.'];
        }

        DepotBanque::create([
            'id_compagnie' => $user->id_compagnie,
            'id_agence' => $idAgence,
            'id_caisse' => null,
            'id_banque' => $banque->id_banque,
            'montant' => $montant,
            'reference' => trim((string) ($data['reference'] ?? '')) ?: null,
            'statut' => 'en_attente',
            'id_utilisateur_demandeur' => $user->idUser,
        ]);

        return ['ok' => true, 'type' => 'success', 'message' => 'Demande de dépôt envoyée, en attente de confirmation.'];
    }

    // Transactionnel : verrouille les versements validés de la gare pour serialiser des
    // confirmations concurrentes (remplace le verrou sur une ligne `caisse` unique du
    // legacy, qui n'existe plus). Re-vérifie le solde à l'intérieur du verrou : c'est ce
    // contrôle-ci qui est réellement autoritaire, celui à la création n'étant qu'un
    // retour rapide côté UX.
    public function confirmerDemande(int $idDepot, Utilisateur $user): array
    {
        if (! in_array($user->droit, ['Admin'], true)) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Accès refusé.'];
        }

        return DB::transaction(function () use ($idDepot, $user) {
            $depot = DepotBanque::where('id_depot', $idDepot)->where('id_compagnie', $user->id_compagnie)
                ->lockForUpdate()->first();

            if (! $depot) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Demande introuvable.'];
            }
            if ($depot->statut !== 'en_attente') {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Cette demande a déjà été traitée.'];
            }

            DB::table('versements_caisse')->where('id_agence', $depot->id_agence)->where('statut', 'valide')->lockForUpdate()->get();

            if ($this->soldeDisponible($depot->id_agence) < (float) $depot->montant) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Solde disponible insuffisant pour confirmer ce dépôt.'];
            }

            DepotBanque::where('id_depot', $idDepot)->update([
                'statut' => 'confirme',
                'id_utilisateur_validateur' => $user->idUser,
                'date_validation' => now(),
            ]);

            Banque::where('id_banque', $depot->id_banque)->update(['solde' => DB::raw('solde + '.(float) $depot->montant)]);

            return ['ok' => true, 'type' => 'success', 'message' => 'Dépôt confirmé : '.number_format($depot->montant, 0, ',', ' ').' FCFA transférés en banque.'];
        });
    }

    public function rejeterDemande(int $idDepot, Utilisateur $user, ?string $motif): array
    {
        if ($user->droit !== 'Admin') {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Accès refusé.'];
        }

        // Compare-and-swap : empêche un rejet d'écraser une demande déjà confirmée
        // (et dont l'argent a déjà bougé) par une confirmation concurrente.
        $affecte = DepotBanque::where('id_depot', $idDepot)->where('id_compagnie', $user->id_compagnie)
            ->where('statut', 'en_attente')
            ->update([
                'statut' => 'rejete',
                'motif_rejet' => trim((string) ($motif ?? '')) ?: null,
                'id_utilisateur_validateur' => $user->idUser,
                'date_validation' => now(),
            ]);

        if (! $affecte) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette demande a déjà été traitée entre-temps par quelqu\'un d\'autre.'];
        }

        return ['ok' => true, 'type' => 'success', 'message' => 'Demande rejetée.'];
    }

    public function getDemandesEnAttente(int $idCompagnie)
    {
        return DepotBanque::query()
            ->join('agence as a', 'depots_banque.id_agence', '=', 'a.idAgence')
            ->join('banques as b', 'depots_banque.id_banque', '=', 'b.id_banque')
            ->join('utilisateur as u', 'depots_banque.id_utilisateur_demandeur', '=', 'u.idUser')
            ->where('depots_banque.id_compagnie', $idCompagnie)->where('depots_banque.statut', 'en_attente')
            ->orderBy('depots_banque.date_demande')
            ->get(['depots_banque.*', 'a.localite', 'a.numeroGare', 'b.nom as nom_banque', 'u.utilisateurs as demandeur']);
    }

    // Historique des demandes (tous statuts), filtré selon le rôle : le chef d'escale ne
    // voit que les demandes de sa propre gare, l'Admin/PDG voit toute la compagnie.
    public function getHistorique(Utilisateur $user)
    {
        return DepotBanque::query()
            ->join('agence as a', 'depots_banque.id_agence', '=', 'a.idAgence')
            ->join('banques as b', 'depots_banque.id_banque', '=', 'b.id_banque')
            ->join('utilisateur as uD', 'depots_banque.id_utilisateur_demandeur', '=', 'uD.idUser')
            ->leftJoin('utilisateur as uV', 'depots_banque.id_utilisateur_validateur', '=', 'uV.idUser')
            ->where('depots_banque.id_compagnie', $user->id_compagnie)
            ->when($user->droit === 'chef_d_escale', fn ($q) => $q->where('depots_banque.id_agence', $user->id_agence))
            ->orderByDesc('depots_banque.date_demande')
            ->limit(100)
            ->get(['depots_banque.*', 'a.localite', 'a.numeroGare', 'b.nom as nom_banque', 'uD.utilisateurs as demandeur', 'uV.utilisateurs as validateur']);
    }
}
