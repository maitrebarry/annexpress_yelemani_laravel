<?php

namespace App\Services;

use App\Models\Banque;

/**
 * Port de Projets_licence/app/models/Banque.php — partie "gestion des comptes banque"
 * (création/édition/consultation). Le workflow de dépôt (demande/confirmation/rejet)
 * est dans App\Services\DepotBanqueService.
 */
class BanqueService
{
    public function getBanquesActives(int $idCompagnie)
    {
        return Banque::where('id_compagnie', $idCompagnie)->where('statut', 'active')->orderBy('nom')->get();
    }

    public function getBanques(int $idCompagnie)
    {
        return Banque::where('id_compagnie', $idCompagnie)->orderBy('nom')->get();
    }

    public function getBanque(int $id, int $idCompagnie): ?Banque
    {
        return Banque::where('id_banque', $id)->where('id_compagnie', $idCompagnie)->first();
    }

    public function creerBanque(int $idCompagnie, array $data): array
    {
        $nom = trim((string) ($data['nom'] ?? ''));
        if ($nom === '') {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Le nom du compte banque est obligatoire.'];
        }

        Banque::create([
            'id_compagnie' => $idCompagnie,
            'nom' => $nom,
            'numero_compte' => trim((string) ($data['numero_compte'] ?? '')) ?: null,
        ]);

        return ['ok' => true, 'type' => 'success', 'message' => 'Compte banque ajouté.'];
    }

    public function modifierBanque(int $id, int $idCompagnie, array $data): array
    {
        $nom = trim((string) ($data['nom'] ?? ''));
        $statut = $data['statut'] ?? '';
        if ($nom === '' || ! in_array($statut, ['active', 'inactive'], true)) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Données invalides.'];
        }

        $affecte = Banque::where('id_banque', $id)->where('id_compagnie', $idCompagnie)->update([
            'nom' => $nom,
            'numero_compte' => trim((string) ($data['numero_compte'] ?? '')) ?: null,
            'statut' => $statut,
        ]);

        if (! $affecte) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Compte banque introuvable.'];
        }

        return ['ok' => true, 'type' => 'success', 'message' => 'Compte banque mis à jour.'];
    }

    // Mouvements (entrées uniquement pour l'instant, aucun retrait banque -> caisse
    // n'existe encore) d'un compte banque précis, pour la modale "Mouvements".
    public function getMouvementsBanque(int $idBanque, int $idCompagnie)
    {
        return \App\Models\DepotBanque::query()
            ->join('agence as a', 'depots_banque.id_agence', '=', 'a.idAgence')
            ->join('utilisateur as uD', 'depots_banque.id_utilisateur_demandeur', '=', 'uD.idUser')
            ->leftJoin('utilisateur as uV', 'depots_banque.id_utilisateur_validateur', '=', 'uV.idUser')
            ->where('depots_banque.id_banque', $idBanque)
            ->where('depots_banque.id_compagnie', $idCompagnie)
            ->where('depots_banque.statut', 'confirme')
            ->orderByDesc('depots_banque.date_validation')
            ->get([
                'depots_banque.*', 'a.localite', 'a.numeroGare',
                'uD.utilisateurs as demandeur', 'uV.utilisateurs as validateur',
            ]);
    }
}
