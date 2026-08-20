<?php

namespace App\Services;

use App\Models\Agence;
use App\Models\CaisseUtilisateur;
use App\Models\ClotureEscale;
use App\Models\JournalCaisse;
use App\Models\Utilisateur;
use App\Models\VersementCaisse;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Caisse_utilisateur.php — cycle de vie de la
 * caisse individuelle d'un opérateur (billettière/agent colis/chef d'escale/Admin) :
 * ouverture, crédit automatique à la vente, fermeture (comptage/écart), versement au
 * chef d'escale, validation, et clôture consolidée de la gare.
 *
 * Chaque méthode mutante retourne ['ok' => bool, 'type' => 'success'|'danger'|'warning'|'info',
 * 'message' => string] — le contrôleur se contente de relayer via Flash::set().
 */
class CaisseUtilisateurService
{
    public function getCaisseOuverte(int $idUtilisateur): ?CaisseUtilisateur
    {
        return CaisseUtilisateur::with('agence')
            ->where('id_utilisateur', $idUtilisateur)
            ->whereDate('date_service', now()->toDateString())
            ->where('statut', 'ouverte')
            ->first();
    }

    // Caisse fermée aujourd'hui mais pas encore versée : doit rester visible (avec un
    // bouton "Verser") tant qu'aucune nouvelle caisse n'est ouverte.
    public function getCaisseFermeeNonVersee(int $idUtilisateur): ?CaisseUtilisateur
    {
        return CaisseUtilisateur::with('agence')
            ->where('id_utilisateur', $idUtilisateur)
            ->whereDate('date_service', now()->toDateString())
            ->where('statut', 'fermee')
            ->first();
    }

    // Un Admin n'a pas de gare fixe en session : il doit choisir la gare concernée
    // dans le formulaire (revalidée contre sa propre compagnie).
    public function ouvrirCaisse(Utilisateur $user, ?int $idAgencePoste, float $montantInitial): array
    {
        if ($montantInitial < 0) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Le montant initial ne peut pas être négatif.'];
        }

        if ($user->droit === 'Admin') {
            if (! $idAgencePoste) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Veuillez choisir une gare.'];
            }
            $idAgence = Agence::where('idAgence', $idAgencePoste)->where('id_compagnie', $user->id_compagnie)->value('idAgence');
            if (! $idAgence) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Gare invalide.'];
            }
        } else {
            $idAgence = $user->id_agence;
            if (! $idAgence) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Informations de session manquantes.'];
            }
        }

        // Verrouille la ligne utilisateur le temps de la transaction, puis revérifie
        // l'absence de caisse ouverte : sans ça, deux clics/onglets concurrents
        // pourraient tous les deux lire "aucune caisse ouverte" avant qu'aucun n'écrive,
        // créant deux caisses pour le même utilisateur/jour.
        return DB::transaction(function () use ($user, $idAgence, $montantInitial) {
            DB::table('utilisateur')->where('idUser', $user->idUser)->lockForUpdate()->first();

            $existante = $this->getCaisseOuverte($user->idUser);
            if ($existante) {
                return ['ok' => false, 'type' => 'warning', 'message' => "Vous avez déjà une caisse ouverte (Réf : {$existante->reference})."];
            }

            $reference = 'CU'.now()->format('Ymd').'-'.$user->idUser.'-'.random_int(100, 999);

            $caisse = CaisseUtilisateur::create([
                'id_utilisateur' => $user->idUser,
                'id_agence' => $idAgence,
                'id_compagnie' => $user->id_compagnie,
                'date_service' => now()->toDateString(),
                'heure_ouverture' => now(),
                'montant_initial' => $montantInitial,
                'statut' => 'ouverte',
                'reference' => $reference,
            ]);

            $this->insererJournal($caisse->id_caisse_user, $user->idUser, 'ouverture', $reference, $montantInitial, 'Ouverture de caisse (fond initial : '.number_format($montantInitial, 0, ',', ' ').' FCFA)');

            return ['ok' => true, 'type' => 'success', 'message' => "Caisse ouverte avec succès (Réf : $reference)."];
        });
    }

    public function crediterColis(int $idUtilisateur, float $montant, string $codeColis, int $idColis): int|false
    {
        $caisse = $this->getCaisseOuverte($idUtilisateur);
        if (! $caisse) {
            return false;
        }

        CaisseUtilisateur::where('id_caisse_user', $caisse->id_caisse_user)->update([
            'total_colis' => DB::raw('total_colis + '.$montant),
            'nb_colis' => DB::raw('nb_colis + 1'),
        ]);

        DB::table('colis')->where('id_colis', $idColis)->update(['id_caisse_user' => $caisse->id_caisse_user]);

        $this->insererJournal($caisse->id_caisse_user, $idUtilisateur, 'colis', $codeColis, $montant, "Colis $codeColis");

        return $caisse->id_caisse_user;
    }

    public function fermerCaisse(Utilisateur $user, float $montantCompte): array
    {
        if ($montantCompte < 0) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Le montant compté ne peut pas être négatif.'];
        }

        $caisse = $this->getCaisseOuverte($user->idUser);
        if (! $caisse) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Aucune caisse ouverte à fermer.'];
        }

        $montantAttendu = (float) $caisse->montant_initial + (float) $caisse->total_billets + (float) $caisse->total_colis;
        $ecart = $montantCompte - $montantAttendu;

        // Update conditionnel (compare-and-swap) : si la caisse a déjà été fermée
        // entre-temps par un double-clic/double-onglet, rowCount = 0.
        $affectee = CaisseUtilisateur::where('id_caisse_user', $caisse->id_caisse_user)
            ->where('id_utilisateur', $user->idUser)
            ->where('statut', 'ouverte')
            ->update([
                'statut' => 'fermee',
                'heure_fermeture' => now(),
                'montant_compte' => $montantCompte,
                'ecart' => $ecart,
            ]);

        if (! $affectee) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Cette caisse a déjà été traitée entre-temps.'];
        }

        $this->insererJournal(
            $caisse->id_caisse_user, $user->idUser, 'fermeture', $caisse->reference, $montantCompte,
            'Fermeture de caisse — attendu : '.number_format($montantAttendu, 0, ',', ' ').' FCFA, compté : '.number_format($montantCompte, 0, ',', ' ').' FCFA, écart : '.number_format($ecart, 0, ',', ' ').' FCFA'
        );

        if ((float) $ecart === 0.0) {
            return ['ok' => true, 'type' => 'success', 'message' => 'Caisse fermée — aucun écart.'];
        }

        return $ecart > 0
            ? ['ok' => true, 'type' => 'info', 'message' => 'Caisse fermée — excédent de '.number_format($ecart, 0, ',', ' ').' FCFA.']
            : ['ok' => true, 'type' => 'warning', 'message' => 'Caisse fermée — déficit de '.number_format(abs($ecart), 0, ',', ' ').' FCFA.'];
    }

    public function getChefsDEscale(int $idAgence, int $idCompagnie)
    {
        return Utilisateur::where('droit', 'chef_d_escale')
            ->where('id_agence', $idAgence)->where('id_compagnie', $idCompagnie)->where('status', 1)
            ->orderBy('utilisateurs')
            ->get(['idUser', 'utilisateurs']);
    }

    public function creerVersement(Utilisateur $user, int $idChefEscale, float $montant, ?string $commentaire): array
    {
        if ($montant <= 0) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Le montant du versement doit être supérieur à 0.'];
        }

        $caisse = CaisseUtilisateur::where('id_utilisateur', $user->idUser)
            ->whereDate('date_service', now()->toDateString())->where('statut', 'fermee')->first();
        if (! $caisse) {
            return ['ok' => false, 'type' => 'warning', 'message' => "Fermez d'abord votre caisse avant de procéder au versement."];
        }

        // Le chef d'escale posté est revalidé (même gare/compagnie que la caisse) : sans
        // ça un versement pourrait être créé pour un chef d'une autre compagnie, "orphelin"
        // (visible de personne), tout en marquant la caisse comme versée.
        $chefValide = Utilisateur::where('idUser', $idChefEscale)->where('droit', 'chef_d_escale')
            ->where('id_agence', $caisse->id_agence)->where('id_compagnie', $caisse->id_compagnie)->exists();
        if (! $chefValide) {
            return ['ok' => false, 'type' => 'danger', 'message' => "Ce chef d'escale n'est pas valide pour votre gare."];
        }

        return DB::transaction(function () use ($caisse, $user, $idChefEscale, $montant, $commentaire) {
            CaisseUtilisateur::where('id_caisse_user', $caisse->id_caisse_user)->lockForUpdate()->first();

            $dejaExistant = VersementCaisse::where('id_caisse_user', $caisse->id_caisse_user)->where('statut', '!=', 'rejete')->exists();
            if ($dejaExistant) {
                return ['ok' => false, 'type' => 'warning', 'message' => 'Un versement est déjà en cours pour cette caisse.'];
            }

            $versement = VersementCaisse::create([
                'id_caisse_user' => $caisse->id_caisse_user,
                'id_emetteur' => $user->idUser,
                'id_chef_escale' => $idChefEscale,
                'id_agence' => $caisse->id_agence,
                'id_compagnie' => $caisse->id_compagnie,
                'montant' => $montant,
                'statut' => 'en_attente',
                'commentaire' => $commentaire,
            ]);

            CaisseUtilisateur::where('id_caisse_user', $caisse->id_caisse_user)->update(['statut' => 'versee']);

            $this->insererJournal($caisse->id_caisse_user, $user->idUser, 'versement', 'VRS-'.$versement->id_versement, $montant, "Versement au chef d'escale");

            return ['ok' => true, 'type' => 'success', 'message' => 'Demande de versement envoyée avec succès.'];
        });
    }

    public function validerVersement(Utilisateur $user, int $idVersement, string $action, ?int $idAgencePoste): array
    {
        if (! in_array($action, ['valide', 'rejete'], true)) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Action invalide.'];
        }

        $query = VersementCaisse::where('id_versement', $idVersement)->where('statut', 'en_attente');

        if ($user->droit === 'Admin') {
            if (! $idAgencePoste) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Gare manquante.'];
            }
            $query->where('id_agence', $idAgencePoste)->where('id_compagnie', $user->id_compagnie);
        } else {
            $query->where('id_chef_escale', $user->idUser)->where('id_agence', $user->id_agence);
        }

        // Update conditionnel : si déjà validé/rejeté entre-temps, rowCount = 0.
        $affecte = $query->update(['statut' => $action, 'date_validation' => now()]);
        if (! $affecte) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Versement introuvable ou déjà traité.'];
        }

        return $action === 'valide'
            ? ['ok' => true, 'type' => 'success', 'message' => 'Versement validé avec succès.']
            : ['ok' => true, 'type' => 'warning', 'message' => 'Versement rejeté.'];
    }

    public function getCaissesEscale(int $idAgence, string $date)
    {
        return CaisseUtilisateur::query()
            ->join('utilisateur as u', 'u.idUser', '=', 'caisse_utilisateur.id_utilisateur')
            ->leftJoin('versements_caisse as v', function ($j) {
                $j->on('v.id_caisse_user', '=', 'caisse_utilisateur.id_caisse_user')->where('v.statut', '!=', 'rejete');
            })
            ->where('caisse_utilisateur.id_agence', $idAgence)
            ->whereDate('caisse_utilisateur.date_service', $date)
            ->orderBy('caisse_utilisateur.heure_ouverture')
            ->get([
                'caisse_utilisateur.*', 'u.utilisateurs', 'u.droit',
                DB::raw('COALESCE(v.montant, 0) as montant_verse'), 'v.statut as statut_versement',
            ]);
    }

    public function getVersementsEnAttente(?int $idChef, int $idAgence)
    {
        return VersementCaisse::query()
            ->join('utilisateur as u', 'u.idUser', '=', 'versements_caisse.id_emetteur')
            ->join('caisse_utilisateur as cu', 'cu.id_caisse_user', '=', 'versements_caisse.id_caisse_user')
            ->where('versements_caisse.id_agence', $idAgence)->where('versements_caisse.statut', 'en_attente')
            ->when($idChef, fn ($q) => $q->where('versements_caisse.id_chef_escale', $idChef))
            ->orderByDesc('versements_caisse.date_versement')
            ->get([
                'versements_caisse.*', 'u.utilisateurs',
                'cu.total_billets', 'cu.total_colis', 'cu.montant_compte', 'cu.ecart', 'cu.date_service', 'cu.reference',
            ]);
    }

    public function getHistoriqueVersements(?int $idChef, int $idAgence)
    {
        return VersementCaisse::query()
            ->join('utilisateur as u', 'u.idUser', '=', 'versements_caisse.id_emetteur')
            ->join('caisse_utilisateur as cu', 'cu.id_caisse_user', '=', 'versements_caisse.id_caisse_user')
            ->where('versements_caisse.id_agence', $idAgence)
            ->when($idChef, fn ($q) => $q->where('versements_caisse.id_chef_escale', $idChef))
            ->orderByDesc('versements_caisse.date_versement')
            ->limit(100)
            ->get(['versements_caisse.*', 'u.utilisateurs', 'cu.date_service', 'cu.reference as ref_caisse']);
    }

    // Réservé Admin/chef_d_escale (Caisse_modifier). Idempotent : relance le même jour
    // met à jour la clôture existante au lieu d'en créer une seconde (le legacy tentait
    // un ON DUPLICATE KEY sans contrainte unique correspondante — n'atteignait donc
    // jamais réellement l'idempotence qu'il visait ; corrigé ici avec updateOrCreate()).
    public function cloturerEscale(Utilisateur $user, int $idAgence, string $date): array
    {
        $ouvertes = CaisseUtilisateur::where('id_agence', $idAgence)->whereDate('date_service', $date)->where('statut', 'ouverte')->count();
        if ($ouvertes > 0) {
            return ['ok' => false, 'type' => 'danger', 'message' => "Clôture impossible : $ouvertes caisse(s) encore ouverte(s) pour cette gare."];
        }

        $totaux = CaisseUtilisateur::where('id_agence', $idAgence)->whereDate('date_service', $date)
            ->whereIn('statut', ['fermee', 'versee'])
            ->selectRaw('COALESCE(SUM(total_billets),0) as total_billets, COALESCE(SUM(total_colis),0) as total_colis, COALESCE(SUM(ecart),0) as total_ecarts')
            ->first();

        $totalVersements = VersementCaisse::whereHas('caisseUser', fn ($q) => $q->where('id_agence', $idAgence)->whereDate('date_service', $date))
            ->where('statut', 'valide')->sum('montant');

        $detail = CaisseUtilisateur::with('utilisateur')->where('id_agence', $idAgence)->whereDate('date_service', $date)
            ->whereIn('statut', ['fermee', 'versee'])->get();

        ClotureEscale::updateOrCreate(
            ['id_agence' => $idAgence, 'date_cloture' => $date],
            [
                'id_chef_escale' => $user->idUser,
                'id_compagnie' => $user->id_compagnie,
                'total_billets' => $totaux->total_billets,
                'total_colis' => $totaux->total_colis,
                'total_versements' => $totalVersements,
                'total_ecarts' => $totaux->total_ecarts,
                'statut' => 'validee',
                'rapport_json' => $detail->map(fn ($c) => [
                    'operateur' => $c->utilisateur->utilisateurs ?? null,
                    'droit' => $c->utilisateur->droit ?? null,
                    'reference' => $c->reference,
                    'total_billets' => $c->total_billets,
                    'total_colis' => $c->total_colis,
                    'ecart' => $c->ecart,
                ])->toJson(),
            ]
        );

        return ['ok' => true, 'type' => 'success', 'message' => "Clôture de l'escale enregistrée avec succès."];
    }

    public function getRapportProprietaire(int $idCompagnie, string $date)
    {
        return Agence::query()
            ->leftJoin('caisse_utilisateur as cu', function ($j) use ($date) {
                $j->on('cu.id_agence', '=', 'agence.idAgence')->where('cu.date_service', $date);
            })
            ->where('agence.id_compagnie', $idCompagnie)
            ->groupBy('agence.idAgence', 'agence.localite', 'agence.numeroGare')
            ->orderBy('agence.localite')->orderBy('agence.numeroGare')
            ->get([
                'agence.localite', 'agence.numeroGare', 'agence.idAgence',
                DB::raw('COALESCE(SUM(cu.total_billets),0) as total_billets'),
                DB::raw('COALESCE(SUM(cu.total_colis),0) as total_colis'),
                DB::raw('COALESCE(SUM(cu.total_billets) + SUM(cu.total_colis),0) as grand_total'),
                DB::raw('COALESCE(SUM(cu.ecart),0) as total_ecarts'),
                DB::raw('COUNT(cu.id_caisse_user) as nb_caisses'),
                DB::raw("COALESCE(SUM(CASE WHEN cu.statut = 'ouverte' THEN 1 ELSE 0 END),0) as caisses_ouvertes"),
                DB::raw("COALESCE(SUM(CASE WHEN cu.statut IN ('fermee','versee') THEN 1 ELSE 0 END),0) as caisses_fermees"),
            ]);
    }

    public function getJournal(int $idCaisseUser)
    {
        return JournalCaisse::with('utilisateur')->where('id_caisse_user', $idCaisseUser)->orderByDesc('date_heure')->get();
    }

    public function getHistoriqueCaisses(int $idUtilisateur, int $limit = 30)
    {
        return CaisseUtilisateur::query()
            ->join('agence as a', 'a.idAgence', '=', 'caisse_utilisateur.id_agence')
            ->where('caisse_utilisateur.id_utilisateur', $idUtilisateur)
            ->orderByDesc('caisse_utilisateur.date_service')->orderByDesc('caisse_utilisateur.heure_ouverture')
            ->limit($limit)
            ->get([
                'caisse_utilisateur.*', 'a.localite', 'a.numeroGare',
                DB::raw('(caisse_utilisateur.montant_initial + caisse_utilisateur.total_billets + caisse_utilisateur.total_colis) as montant_attendu'),
            ]);
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
