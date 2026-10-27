<?php

namespace App\Services;

use App\Models\Agence;
use App\Models\BordereauEscale;
use App\Models\BordereauEscaleLigne;
use App\Models\CaisseUtilisateur;
use App\Models\Depense;
use App\Models\JournalCaisse;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Bordereau_escale.php::saveBordereau() — saisie dans
 * le système d'un bordereau d'escale papier (escale isolée sans électricité ni réseau, cf.
 * migration create_bordereau_escale_tables). Pas de faux billets individuels : le papier ne
 * capture que des comptages agrégés (nombre de billets par destination), jamais de
 * passager nommé ni de numéro de place. Alimente directement caisse_utilisateur (déjà
 * fermée) + depense, exactement comme une vraie caisse, et conserve l'écriture exacte du
 * papier dans bordereau_escale(_lignes) pour la traçabilité.
 */
class BordereauEscaleService
{
    public function saveBordereau(Utilisateur $user, array $data): array
    {
        $idAgence = (int) ($data['id_agence'] ?? 0);
        $agenceValide = Agence::where('idAgence', $idAgence)->where('id_compagnie', $user->id_compagnie)->exists();
        if (! $agenceValide) {
            return ['ok' => false, 'message' => 'Veuillez choisir la gare de rattachement.'];
        }

        $dateEscale = trim((string) ($data['date_escale'] ?? ''));
        $aujourdhui = now()->toDateString();
        if (! $dateEscale || $dateEscale > $aujourdhui) {
            return ['ok' => false, 'message' => "Date invalide : elle ne peut pas être vide ni dans le futur."];
        }

        $numeroBus = trim((string) ($data['numero_bus'] ?? '')) ?: null;
        $escaleLibelle = trim((string) ($data['escale_libelle'] ?? '')) ?: null;
        $nomAgentEscale = trim((string) ($data['nom_agent_escale'] ?? '')) ?: null;
        $numeroBordereau = trim((string) ($data['numero_bordereau'] ?? '')) ?: null;

        // Lignes "billets" (destination / nombre / prix unitaire) : tableaux parallèles,
        // même convention que bagage_description[]/bagage_poids[]/bagage_prix[] ailleurs.
        $destinations = (array) ($data['destination'] ?? []);
        $nombres = (array) ($data['nombre_billets'] ?? []);
        $prixUnitaires = (array) ($data['prix_unitaire'] ?? []);

        $lignesBillets = [];
        $totalBillets = 0.0;
        $nbBillets = 0;
        foreach ($destinations as $i => $dest) {
            $dest = trim((string) $dest);
            $nombre = (int) ($nombres[$i] ?? 0);
            $prix = (float) ($prixUnitaires[$i] ?? 0);
            if ($dest === '' || $nombre <= 0 || $prix <= 0) {
                continue;
            }
            $montant = $nombre * $prix;
            $lignesBillets[] = ['destination' => $dest, 'nombre' => $nombre, 'prix_unitaire' => $prix, 'montant' => $montant];
            $totalBillets += $montant;
            $nbBillets += $nombre;
        }

        // Lignes "bagages" (description / prix)
        $descriptions = (array) ($data['bagage_description'] ?? []);
        $prixBagages = (array) ($data['bagage_prix'] ?? []);

        $lignesBagages = [];
        $totalBagages = 0.0;
        foreach ($prixBagages as $i => $prix) {
            if ($prix === '' || $prix === null) {
                continue;
            }
            $prix = (float) $prix;
            if ($prix <= 0) {
                continue;
            }
            $lignesBagages[] = ['description' => trim((string) ($descriptions[$i] ?? '')) ?: null, 'montant' => $prix];
            $totalBagages += $prix;
        }

        if (empty($lignesBillets) && empty($lignesBagages)) {
            return ['ok' => false, 'message' => 'Veuillez saisir au moins une ligne de billets ou de bagages.'];
        }

        $montantDepenses = (float) ($data['montant_depenses'] ?? 0);
        $montantCommission = (float) ($data['montant_commission'] ?? 0);
        $totalVerseSaisi = (float) ($data['total_verse'] ?? 0);
        if ($montantDepenses < 0 || $montantCommission < 0 || $totalVerseSaisi < 0) {
            return ['ok' => false, 'message' => 'Les montants ne peuvent pas être négatifs.'];
        }

        $totalGeneral = $totalBillets + $totalBagages;

        $idBordereau = DB::transaction(function () use (
            $user, $idAgence, $dateEscale, $numeroBus, $escaleLibelle, $nomAgentEscale, $numeroBordereau,
            $lignesBillets, $totalBillets, $nbBillets, $lignesBagages, $totalBagages,
            $montantDepenses, $montantCommission, $totalVerseSaisi, $totalGeneral
        ) {
            // Caisse déjà fermée : pas de passage par CaisseUtilisateurService::ouvrirCaisse()
            // (câblée sur aujourd'hui), la saisie étant par nature rétroactive. Formule
            // d'écart identique à fermerCaisse() (montant_initial + total_billets +
            // total_colis), avec montant_initial=0 et total_colis=0 ici.
            $reference = 'CU'.\Illuminate\Support\Carbon::parse($dateEscale)->format('Ymd').'-'.$user->idUser.'-'.random_int(100, 999);
            $ecart = $totalVerseSaisi - $totalGeneral;

            $caisse = CaisseUtilisateur::create([
                'id_utilisateur' => $user->idUser,
                'id_agence' => $idAgence,
                'id_compagnie' => $user->id_compagnie,
                'date_service' => $dateEscale,
                'heure_ouverture' => $dateEscale.' 00:00:00',
                'heure_fermeture' => $dateEscale.' 23:59:00',
                'montant_initial' => 0,
                'total_billets' => $totalGeneral,
                'total_colis' => 0,
                'nb_billets' => $nbBillets,
                'nb_colis' => 0,
                'montant_depense' => $montantDepenses + $montantCommission,
                'montant_compte' => $totalVerseSaisi,
                'ecart' => $ecart,
                'statut' => 'fermee',
                'reference' => $reference,
            ]);

            // Journal : ouverture, une ligne par destination, une ligne par bagage, fermeture
            // (même format qu'une vraie caisse, pour que l'onglet Journal de cette caisse
            // soit aussi lisible).
            JournalCaisse::create([
                'id_caisse_user' => $caisse->id_caisse_user, 'id_utilisateur' => $user->idUser,
                'type_operation' => 'ouverture', 'reference_op' => $reference, 'montant' => 0,
                'libelle' => 'Ouverture de caisse (saisie bordereau papier)',
            ]);
            foreach ($lignesBillets as $l) {
                JournalCaisse::create([
                    'id_caisse_user' => $caisse->id_caisse_user, 'id_utilisateur' => $user->idUser,
                    'type_operation' => 'billet', 'reference_op' => $numeroBordereau, 'montant' => $l['montant'],
                    'libelle' => "Vente groupée {$l['nombre']} billet(s) vers {$l['destination']} — bordereau papier",
                ]);
            }
            foreach ($lignesBagages as $l) {
                JournalCaisse::create([
                    'id_caisse_user' => $caisse->id_caisse_user, 'id_utilisateur' => $user->idUser,
                    'type_operation' => 'bagage', 'reference_op' => $numeroBordereau, 'montant' => $l['montant'],
                    'libelle' => 'Bagages — bordereau papier'.(! empty($l['description']) ? " ({$l['description']})" : ''),
                ]);
            }
            JournalCaisse::create([
                'id_caisse_user' => $caisse->id_caisse_user, 'id_utilisateur' => $user->idUser,
                'type_operation' => 'fermeture', 'reference_op' => $reference, 'montant' => $totalVerseSaisi,
                'libelle' => "Fermeture – attendu: {$totalGeneral} FCFA, compté: {$totalVerseSaisi} FCFA, écart: {$ecart} FCFA",
            ]);

            // Dépenses (catégorie 'Autre') et commission (catégorie dédiée) : déjà
            // physiquement réglées et signées sur le papier, donc statut 'valide' direct
            // (pas de circuit de validation a posteriori à rejouer, contrairement à une
            // dépense saisie en direct par un chef d'escale).
            if ($montantDepenses > 0) {
                Depense::create([
                    'id_compagnie' => $user->id_compagnie, 'id_agence' => $idAgence, 'id_caisse_user' => $caisse->id_caisse_user,
                    'categorie' => 'Autre', 'libelle' => 'Dépenses — bordereau escale du '.\Illuminate\Support\Carbon::parse($dateEscale)->format('d/m/Y'),
                    'montant' => (int) round($montantDepenses), 'date_depense' => $dateEscale, 'id_utilisateur' => $user->idUser, 'statut' => 'valide',
                ]);
            }
            if ($montantCommission > 0) {
                Depense::create([
                    'id_compagnie' => $user->id_compagnie, 'id_agence' => $idAgence, 'id_caisse_user' => $caisse->id_caisse_user,
                    'categorie' => "Commission agent d'escale",
                    'libelle' => "Commission agent d'escale".(! empty($nomAgentEscale) ? " ($nomAgentEscale)" : '').' — bordereau du '.\Illuminate\Support\Carbon::parse($dateEscale)->format('d/m/Y'),
                    'montant' => (int) round($montantCommission), 'date_depense' => $dateEscale, 'id_utilisateur' => $user->idUser, 'statut' => 'valide',
                ]);
            }

            // Bordereau (audit) + ses lignes, tel quel depuis le papier.
            $bordereau = BordereauEscale::create([
                'numero_bordereau' => $numeroBordereau, 'date_escale' => $dateEscale, 'numero_bus' => $numeroBus,
                'escale_libelle' => $escaleLibelle, 'nom_agent_escale' => $nomAgentEscale,
                'id_agence' => $idAgence, 'id_compagnie' => $user->id_compagnie, 'id_caisse_user' => $caisse->id_caisse_user,
                'total_billets' => $totalBillets, 'total_bagages' => $totalBagages,
                'montant_depenses' => $montantDepenses, 'montant_commission' => $montantCommission,
                'total_verse_saisi' => $totalVerseSaisi, 'saisi_par' => $user->idUser,
            ]);

            foreach ($lignesBillets as $l) {
                BordereauEscaleLigne::create([
                    'id_bordereau' => $bordereau->id_bordereau, 'type_ligne' => 'billet', 'destination' => $l['destination'],
                    'nombre' => $l['nombre'], 'prix_unitaire' => $l['prix_unitaire'], 'description' => null, 'montant' => $l['montant'],
                ]);
            }
            foreach ($lignesBagages as $l) {
                BordereauEscaleLigne::create([
                    'id_bordereau' => $bordereau->id_bordereau, 'type_ligne' => 'bagage', 'destination' => null,
                    'nombre' => null, 'prix_unitaire' => null, 'description' => $l['description'], 'montant' => $l['montant'],
                ]);
            }

            return $bordereau->id_bordereau;
        });

        // Avertissement non bloquant si le total saisi ne colle pas à l'arithmétique du
        // papier (billets+bagages-dépenses-commission = total versé) : aide à repérer une
        // erreur de transcription, distinct de l'écart système (déjà enregistré ci-dessus
        // dans caisse_utilisateur.ecart, qui peut légitimement être non nul si la caisse
        // physique avait un vrai écart).
        $calcule = $totalGeneral - $montantDepenses - $montantCommission;
        if (abs($calcule - $totalVerseSaisi) > 1) {
            return [
                'ok' => true, 'idBordereau' => $idBordereau, 'type' => 'warning',
                'message' => 'Bordereau enregistré. Attention : billets + bagages − dépenses − commission = '.
                    number_format($calcule, 0, ',', ' ').' FCFA, mais le total versé saisi est de '.
                    number_format($totalVerseSaisi, 0, ',', ' ').' FCFA. Vérifiez la saisie.',
            ];
        }

        return ['ok' => true, 'idBordereau' => $idBordereau, 'type' => 'success', 'message' => 'Bordereau enregistré et caisse créée avec succès.'];
    }

    public function getBordereaux(int $idCompagnie, ?int $idAgence, ?string $date)
    {
        return BordereauEscale::query()
            ->leftJoin('agence', 'bordereau_escale.id_agence', '=', 'agence.idAgence')
            ->leftJoin('caisse_utilisateur', 'bordereau_escale.id_caisse_user', '=', 'caisse_utilisateur.id_caisse_user')
            ->leftJoin('utilisateur', 'bordereau_escale.saisi_par', '=', 'utilisateur.idUser')
            ->where('bordereau_escale.id_compagnie', $idCompagnie)
            ->when($idAgence, fn ($q) => $q->where('bordereau_escale.id_agence', $idAgence))
            ->when($date, fn ($q) => $q->where('bordereau_escale.date_escale', $date))
            ->orderByDesc('bordereau_escale.date_escale')
            ->orderByDesc('bordereau_escale.id_bordereau')
            ->limit(200)
            ->get([
                'bordereau_escale.*', 'agence.localite', 'agence.numeroGare',
                'caisse_utilisateur.statut as statut_caisse', 'caisse_utilisateur.ecart',
                'utilisateur.utilisateurs as nom_saisi_par',
            ]);
    }

    public function getBordereauDetail(int $id, int $idCompagnie): ?object
    {
        $bordereau = BordereauEscale::query()
            ->leftJoin('agence', 'bordereau_escale.id_agence', '=', 'agence.idAgence')
            ->leftJoin('caisse_utilisateur', 'bordereau_escale.id_caisse_user', '=', 'caisse_utilisateur.id_caisse_user')
            ->leftJoin('utilisateur', 'bordereau_escale.saisi_par', '=', 'utilisateur.idUser')
            ->where('bordereau_escale.id_bordereau', $id)
            ->where('bordereau_escale.id_compagnie', $idCompagnie)
            ->first([
                'bordereau_escale.*', 'agence.localite', 'agence.numeroGare',
                'caisse_utilisateur.statut as statut_caisse', 'caisse_utilisateur.ecart',
                'caisse_utilisateur.id_utilisateur as id_utilisateur_caisse', 'utilisateur.utilisateurs as nom_saisi_par',
            ]);

        if (! $bordereau) {
            return null;
        }

        $bordereau->lignes = BordereauEscaleLigne::where('id_bordereau', $id)
            ->orderBy('type_ligne')->orderBy('id_ligne')->get();

        return $bordereau;
    }
}
