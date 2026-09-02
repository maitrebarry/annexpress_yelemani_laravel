<?php

namespace App\Services;

use App\Models\Agence;
use App\Models\Billet;
use App\Models\CaisseUtilisateur;
use App\Models\Car;
use App\Models\Client;
use App\Models\Compagnie;
use App\Models\Depense;
use App\Models\LigneTrajet;
use App\Models\PlaceMinimale;
use App\Models\Programme;
use App\Models\ProgrammationVoyage;
use App\Models\Suivis;
use App\Models\Utilisateur;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Add_billet.php (création) et des méthodes de
 * app/models/Liste_du_jour.php nécessaires à la liste/l'historique (listeBillets,
 * getDestinations, donneesTicketThermique). Les workflows d'annulation/report/
 * embarquement/validation "en entente" restent à porter (voir plan).
 */
class BilletService
{
    public function getFormData(Utilisateur $user): array
    {
        if ($user->droit === 'Admin') {
            $agences = Agence::where('id_compagnie', $user->id_compagnie)
                ->orderBy('localite')->orderBy('numeroGare')->get();

            $destinationsParGare = [];
            foreach ($agences as $agence) {
                $destinationsParGare[$agence->idAgence] = $this->destinationsPourGare($agence->idAgence, $user->id_compagnie);
            }

            return ['agences' => $agences, 'destinationsParGare' => $destinationsParGare, 'destinations' => []];
        }

        $idAgence = $user->id_agence;

        return [
            'agences' => collect(),
            'destinationsParGare' => [],
            'destinations' => $idAgence ? $this->destinationsPourGare($idAgence, $user->id_compagnie) : [],
        ];
    }

    // Structure : [['nom' => destLocalite, 'programmes' => [['idProgrammer','heureDepart','prix','escales' => [...]]]]]
    private function destinationsPourGare(int $idAgenceDepart, int $idCompagnie): array
    {
        $rows = Programme::query()
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->where('programmer.idDepart', $idAgenceDepart)
            ->where('programmer.id_compagnie', $idCompagnie)
            ->get(['programmer.idProgrammer', 'programmer.heureDepart', 'programmer.prix', 'a2.localite as destinationLocalite']);

        if ($rows->isEmpty()) {
            return [];
        }

        $escalesParProgramme = LigneTrajet::query()
            ->join('escale as e', 'ligneTrajet.id_escales', '=', 'e.id_escale')
            ->whereIn('ligneTrajet.id_trajets', $rows->pluck('idProgrammer'))
            ->where('ligneTrajet.type_trajet', 'programmer')
            ->get(['ligneTrajet.id_trajets', 'e.id_escale', 'ligneTrajet.prix_escale', 'e.escales as escale_nom'])
            ->groupBy('id_trajets');

        $result = [];
        foreach ($rows as $row) {
            $destNom = $row->destinationLocalite;
            if (! isset($result[$destNom])) {
                $result[$destNom] = ['nom' => $destNom, 'programmes' => []];
            }

            $escales = ($escalesParProgramme->get($row->idProgrammer) ?? collect())
                ->map(fn ($e) => ['id_escale' => $e->id_escale, 'prix_escale' => $e->prix_escale, 'escale_nom' => $e->escale_nom])
                ->values()->all();

            $result[$destNom]['programmes'][] = [
                'idProgrammer' => $row->idProgrammer,
                'heureDepart' => $row->heureDepart,
                'prix' => $row->prix,
                'escales' => $escales,
            ];
        }

        return array_values($result);
    }

    private function resolveDepart(Utilisateur $user, $idDepartPoste): array
    {
        if ($user->droit === 'Admin') {
            if (! $idDepartPoste) {
                return [null, null, null];
            }
            $agence = Agence::where('idAgence', $idDepartPoste)->where('id_compagnie', $user->id_compagnie)->first();

            return $agence ? [$agence->idAgence, $agence->localite, $agence->numeroGare] : [null, null, null];
        }

        $agence = $user->agence;

        return [$user->id_agence, $agence?->localite, $agence?->numeroGare];
    }

    // Ne fait jamais confiance au prix posté par le client : recalculé depuis le trajet
    // programmé (et son éventuelle escale) côté serveur.
    private function prixReference(int $idAgenceDepart, string $destinationLocalite, string $heureDepart, int $idCompagnie, ?string $escaleNom): ?float
    {
        $programme = Programme::query()
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->where('programmer.idDepart', $idAgenceDepart)
            ->where('a2.localite', $destinationLocalite)
            ->where('programmer.heureDepart', $heureDepart)
            ->where('programmer.id_compagnie', $idCompagnie)
            ->first(['programmer.idProgrammer', 'programmer.prix']);

        if (! $programme) {
            return null;
        }

        $prix = (float) $programme->prix;

        if ($escaleNom) {
            $prixEscale = LigneTrajet::query()
                ->join('escale as e', 'ligneTrajet.id_escales', '=', 'e.id_escale')
                ->where('ligneTrajet.id_trajets', $programme->idProgrammer)
                ->where('ligneTrajet.type_trajet', 'programmer')
                ->where('e.escales', $escaleNom)
                ->value('ligneTrajet.prix_escale');

            if ($prixEscale !== null) {
                $prix = (float) $prixEscale;
            }
        }

        return $prix;
    }

    // Le numéro affiché côté formulaire n'est qu'un aperçu, jamais utilisé côté serveur :
    // régénéré et vérifié en base pour éviter tout doublon (deux ventes dans la même
    // seconde pourraient sinon produire le même numéro).
    private function genererNumeroBilletUnique(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $numero = 'SMT'.now()->format('Hismd').random_int(100, 999);
            if (! Billet::where('numeroBillets', $numero)->exists()) {
                return $numero;
            }
        }

        return 'SMT'.uniqid();
    }

    public function creerReservation(Utilisateur $user, array $data, CaisseUtilisateurService $caisseService): array
    {
        if ($user->estLectureSeule()) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Votre rôle est en lecture seule.'];
        }

        [$idAgenceDepart, $departLocalite, $numeroGareDepart] = $this->resolveDepart($user, $data['idDepart'] ?? null);
        if (! $departLocalite) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Veuillez choisir la gare de départ.'];
        }

        $jourVoyage = $data['jourVoyage'] ?? null;
        $aujourdhui = now()->toDateString();
        $joursAvance = (int) config('billets.jours_reservation_avance', 6);
        $maxJour = now()->addDays($joursAvance)->toDateString();
        if (! $jourVoyage || $jourVoyage < $aujourdhui || $jourVoyage > $maxJour) {
            return ['ok' => false, 'type' => 'danger', 'message' => "Date invalide : choisissez une date entre aujourd'hui et dans $joursAvance jours."];
        }

        $nomClient = trim((string) ($data['Client'] ?? ''));
        $destinationId = trim((string) ($data['destinationId'] ?? ''));
        $programme = trim((string) ($data['programme'] ?? ''));
        $nombrePassages = (int) ($data['nombrePassages'] ?? 0);

        if ($nomClient === '' || $destinationId === '' || $programme === '' || $nombrePassages <= 0) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Tous les champs obligatoires doivent être remplis.'];
        }

        $escaleNom = trim((string) ($data['escale'] ?? ''));
        $destFinale = $escaleNom !== '' ? $escaleNom : $destinationId;

        $prixUnitaire = $this->prixReference($idAgenceDepart, $destinationId, $programme, $user->id_compagnie, $escaleNom ?: null);
        if ($prixUnitaire === null) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Trajet ou tarif introuvable pour cette destination/heure.'];
        }
        $montantTotal = $prixUnitaire * $nombrePassages;

        try {
            return DB::transaction(function () use (
                $user, $idAgenceDepart, $departLocalite, $numeroGareDepart, $jourVoyage, $aujourdhui,
                $nomClient, $destinationId, $programme, $nombrePassages, $destFinale, $montantTotal, $caisseService
            ) {
                $client = Client::create([
                    'Client' => $nomClient,
                    'montant_payer' => $montantTotal,
                    'date_enregistrement' => now()->toDateString(),
                    'id_compagnie' => $user->id_compagnie,
                ]);

                $numPlace = '-';
                $idCarProgrammer = null;

                if ($jourVoyage === $aujourdhui) {
                    $prog = ProgrammationVoyage::where('id_horaire', $programme)
                        ->where('date_enregistre', $jourVoyage)
                        ->where('id_trajet', $destinationId)
                        ->where('localite_user', $departLocalite)
                        ->where('id_agence', $idAgenceDepart)
                        ->where('id_compagnie', $user->id_compagnie)
                        ->first();

                    if (! $prog) {
                        throw new \RuntimeException('AUCUN_CAR_PROGRAMME');
                    }
                    $idCarProgrammer = $prog->id_car_programmer;

                    $car = Car::where('id_car', $idCarProgrammer)->lockForUpdate()->first();
                    if (! $car) {
                        throw new \RuntimeException('CAR_INTROUVABLE');
                    }

                    $placesDispo = $car->nbr_place - $car->nbr_place_reserve;
                    if ($nombrePassages > $placesDispo) {
                        throw new \RuntimeException("PLACES_INSUFFISANTES:$placesDispo");
                    }

                    $start = (int) $car->nbr_place_reserve + 1;
                    $end = $start + $nombrePassages - 1;
                    $numPlace = $nombrePassages === 1 ? (string) $start : "$start-$end";
                } else {
                    $placeTotale = (int) (PlaceMinimale::where('id_compagnie', $user->id_compagnie)->value('place_minumale') ?? 0);
                    if ($placeTotale <= 0) {
                        throw new \RuntimeException('PLACE_MINIMALE_NON_DEFINIE');
                    }

                    $suivi = Suivis::where('depart', $departLocalite)
                        ->where('destination', $destFinale)
                        ->where('heur_depart', $programme)
                        ->where('date_reservation', $jourVoyage)
                        ->where('id_compagnie', $user->id_compagnie)
                        ->lockForUpdate()
                        ->first();

                    if ($suivi) {
                        $placesDispo = $suivi->place_totals - $suivi->place_reserve;
                        if ($nombrePassages > $placesDispo) {
                            throw new \RuntimeException("PLACES_INSUFFISANTES_DEMAIN:$placesDispo");
                        }
                        $suivi->increment('place_reserve', $nombrePassages);
                    } else {
                        if ($nombrePassages > $placeTotale) {
                            throw new \RuntimeException("PLACES_INSUFFISANTES_DEMAIN:$placeTotale");
                        }
                        Suivis::create([
                            'place_reserve' => $nombrePassages,
                            'place_totals' => $placeTotale,
                            'depart' => $departLocalite,
                            'destination' => $destFinale,
                            'heur_depart' => $programme,
                            'date_reservation' => $jourVoyage,
                            'id_agence' => $idAgenceDepart,
                            'id_compagnie' => $user->id_compagnie,
                        ]);
                    }

                    $placesPrises = [];
                    foreach (Billet::where('jourVoyage', $jourVoyage)->where('Heur_departs', $programme)
                        ->where('departId', $departLocalite)->where('destinationId', $destFinale)
                        ->where('id_compagnie', $user->id_compagnie)->pluck('numeroPlace') as $np) {
                        foreach (explode('-', (string) $np) as $p) {
                            if (is_numeric($p)) {
                                $placesPrises[] = (int) $p;
                            }
                        }
                    }

                    $start = 1;
                    $attribues = [];
                    while (count($attribues) < $nombrePassages) {
                        if (! in_array($start, $placesPrises, true)) {
                            $attribues[] = $start;
                        }
                        $start++;
                    }
                    $numPlace = implode('-', $attribues);
                }

                $numeroBillets = $this->genererNumeroBilletUnique();

                $billet = Billet::create([
                    'id_client' => $client->idClient,
                    'idUser' => $user->idUser,
                    'numeroBillets' => $numeroBillets,
                    'jourVoyage' => $jourVoyage,
                    'Heur_departs' => $programme,
                    'nombrePassages' => $nombrePassages,
                    'destinationId' => $destFinale,
                    'departId' => $departLocalite,
                    'date_expiration' => \Illuminate\Support\Carbon::parse($jourVoyage)->addWeek()->toDateString(),
                    'numeroPlace' => $numPlace,
                    'date_reservation' => now()->toDateString(),
                    'status_reservation' => 'presentiel',
                    'validation_billets' => 'valider',
                    'id_compagnie' => $user->id_compagnie,
                    'num_gare' => $numeroGareDepart,
                ]);

                if ($jourVoyage === $aujourdhui) {
                    $affecte = Car::where('id_car', $idCarProgrammer)
                        ->update(['nbr_place_reserve' => DB::raw('nbr_place_reserve + '.$nombrePassages)]);
                    if (! $affecte) {
                        throw new \RuntimeException('ECHEC_MAJ_CAR');
                    }
                }

                $creditOk = $caisseService->crediterBillet($user->idUser, $montantTotal, $numeroBillets, $billet->idBillets);
                if ($creditOk === false) {
                    throw new \RuntimeException('CAISSE_FERMEE');
                }

                // Ancienne caisse de gare (table `caisse`, système remplacé — voir mémoire
                // Caisse) : maintenue pour parité, sans effet réel (0 ligne dans cette app).
                $caisseAgence = DB::table('caisse as c')
                    ->join('agence as a', 'c.id_agence', '=', 'a.idAgence')
                    ->where('c.id_compagnie', $user->id_compagnie)
                    ->where('a.localite', $departLocalite)
                    ->where('a.numeroGare', $numeroGareDepart)
                    ->where('c.status_caisse', 1)
                    ->select('c.id_caisse')
                    ->first();
                if ($caisseAgence) {
                    DB::table('caisse')->where('id_caisse', $caisseAgence->id_caisse)
                        ->update(['montant_billets' => DB::raw('montant_billets + '.$montantTotal)]);
                }

                return [
                    'ok' => true, 'type' => 'success',
                    'message' => 'Réservation enregistrée avec succès et caisse alimentée.',
                    'idBillet' => $billet->idBillets,
                ];
            });
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'type' => 'danger', 'message' => $this->messagePourErreur($e->getMessage())];
        }
    }

    private function messagePourErreur(string $code): string
    {
        [$type, $valeur] = array_pad(explode(':', $code, 2), 2, null);

        return match ($type) {
            'AUCUN_CAR_PROGRAMME' => 'Aucun car programmé pour cette heure et ce trajet.',
            'CAR_INTROUVABLE' => 'Car introuvable.',
            'PLACES_INSUFFISANTES' => "Places insuffisantes : $valeur restantes.",
            'PLACE_MINIMALE_NON_DEFINIE' => 'Erreur : nombre de places minimales non défini.',
            'PLACES_INSUFFISANTES_DEMAIN' => "Places insuffisantes pour demain : $valeur restantes.",
            'ECHEC_MAJ_CAR' => 'Échec de la mise à jour du car.',
            'CAISSE_FERMEE' => "Opération bloquée : Aucune caisse ouverte pour votre compte. Veuillez ouvrir votre caisse d'abord.",
            default => 'Erreur lors de l\'enregistrement de la réservation.',
        };
    }

    // Billets valides d'une date précise (aujourd'hui ou demain), scopés par gare sauf
    // pour Admin/PDG qui voient toute la compagnie. Port de Liste_du_jours::index() +
    // Liste_de_demains::index() — le legacy filtrait "demain" côté vue (PHP foreach), pas
    // en SQL ; fait ici correctement au niveau requête.
    public function getListeParDate(Utilisateur $user, string $date): Collection
    {
        $isAdmin = in_array($user->droit, ['Admin', 'PDG', 'secretaire'], true);

        return Billet::query()
            ->join('client', 'billets.id_client', '=', 'client.idClient')
            ->where('billets.id_compagnie', $user->id_compagnie)
            ->where('billets.validation_billets', 'valider')
            ->where('billets.jourVoyage', $date)
            ->when(! $isAdmin, fn ($q) => $q->where('billets.departId', $user->agence?->localite)->where('billets.num_gare', $user->agence?->numeroGare))
            ->orderBy('billets.Heur_departs')
            ->get(['billets.*', 'client.Client', 'client.montant_payer']);
    }

    public function getHistorique(Utilisateur $user, array $filtres): Collection
    {
        $isAdmin = in_array($user->droit, ['Admin', 'PDG', 'secretaire'], true);

        return Billet::query()
            ->join('client', 'billets.id_client', '=', 'client.idClient')
            ->where('billets.id_compagnie', $user->id_compagnie)
            ->where('billets.jourVoyage', $filtres['date'])
            ->when(! $isAdmin, fn ($q) => $q->where('billets.departId', $user->agence?->localite)->where('billets.num_gare', $user->agence?->numeroGare))
            ->when(! empty($filtres['destination']), fn ($q) => $q->where('billets.destinationId', $filtres['destination']))
            ->when(! empty($filtres['heure']), fn ($q) => $q->where('billets.Heur_departs', $filtres['heure']))
            ->orderBy('billets.Heur_departs')
            ->get(['billets.*', 'client.Client', 'client.montant_payer']);
    }

    public function getDestinationsPourFiltre(Utilisateur $user): Collection
    {
        $isAdmin = in_array($user->droit, ['Admin', 'PDG', 'secretaire'], true);

        return Programme::query()
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->where('programmer.id_compagnie', $user->id_compagnie)
            ->when(! $isAdmin, fn ($q) => $q->where('a1.localite', $user->agence?->localite)->where('a1.numeroGare', $user->agence?->numeroGare))
            ->distinct()
            ->orderBy('a2.localite')
            ->get(['a2.localite as idDestination'])
            ->pluck('idDestination');
    }

    public function getDonneesTicketThermique(int $idBillets, Utilisateur $user): array
    {
        $billet = Billet::query()
            ->join('client', 'billets.id_client', '=', 'client.idClient')
            ->leftJoin('utilisateur', 'utilisateur.idUser', '=', 'billets.idUser')
            ->where('billets.idBillets', $idBillets)
            ->where('billets.id_compagnie', $user->id_compagnie)
            ->first(['billets.*', 'client.Client', 'client.montant_payer', 'utilisateur.utilisateurs']);

        $compagnie = Compagnie::find($user->id_compagnie);

        if (! $billet || ! $compagnie) {
            return ['error' => 'Billet ou compagnie introuvable.'];
        }

        $logoBase64 = null;
        if ($compagnie->logo) {
            $logoPath = public_path('images/logos/'.$compagnie->logo);
            if (is_file($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

        $heure = $billet->Heur_departs ? \Illuminate\Support\Carbon::parse($billet->Heur_departs) : null;
        $montantNet = preg_replace('/[^\d.]/', '', (string) $billet->montant_payer);

        return [
            'compagnie' => $compagnie->nom_compagnie ?? 'Nom Compagnie',
            'slogan' => $compagnie->slogant ?? '',
            'logo' => $logoBase64,
            'numero' => $billet->numeroBillets ?? '-',
            'client' => $billet->Client ?? '-',
            'date' => $billet->jourVoyage ? \Illuminate\Support\Carbon::parse($billet->jourVoyage)->format('d/m/Y') : '-',
            'depart' => $billet->departId ?? '-',
            'heure' => $heure ? $heure->format('H\hi') : (string) ($billet->Heur_departs ?? '-'),
            'destination' => $billet->destinationId ?? '-',
            'places' => $billet->numeroPlace ?? '-',
            'montant' => $montantNet !== '' ? number_format((float) $montantNet, 0, ',', ' ') : '-',
            'emisPar' => $billet->utilisateurs ?? '-',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // ANNULATION — port de Liste_du_jour.php (demanderAnnulationBillet,
    // confirmerAnnulationBillet, rejeterAnnulationBillet, getDemandesAnnulationEnAttente).
    // ─────────────────────────────────────────────────────────────────────

    // Étape 1 (chef d'escale uniquement) : soumet une demande sans toucher à la place ni à
    // la caisse. IDOR-guard ajouté (absent du legacy) : ne peut viser que sa propre gare.
    public function demanderAnnulation(Utilisateur $user, int $idBillets, ?string $motif): array
    {
        $billet = Billet::where('idBillets', $idBillets)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $billet) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Billet introuvable.'];
        }

        if ($billet->departId !== $user->agence?->localite || $billet->num_gare !== $user->agence?->numeroGare) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Ce billet ne concerne pas votre gare.'];
        }

        if (! in_array($billet->status_billets, [null, ''], true)) {
            return ['ok' => false, 'type' => 'warning', 'message' => "Ce billet est déjà annulé ou une demande est déjà en cours."];
        }

        if (\Illuminate\Support\Carbon::parse($billet->jourVoyage)->toDateString() < now()->toDateString()) {
            return ['ok' => false, 'type' => 'danger', 'message' => "Impossible de demander l'annulation d'un billet dont le voyage est déjà passé."];
        }

        $billet->update([
            'status_billets' => 'annulation_demandee',
            'motif_annulation' => $motif ?: null,
            'demande_annulation_par' => $user->idUser,
            'demande_annulation_le' => now(),
        ]);

        return ['ok' => true, 'type' => 'success', 'message' => "Demande d'annulation envoyée : un Admin doit la confirmer avant que la place et l'argent ne soient libérés."];
    }

    public function getDemandesAnnulationEnAttente(int $idCompagnie): Collection
    {
        return Billet::query()
            ->join('client', 'billets.id_client', '=', 'client.idClient')
            ->leftJoin('utilisateur', 'utilisateur.idUser', '=', 'billets.demande_annulation_par')
            ->where('billets.id_compagnie', $idCompagnie)
            ->where('billets.status_billets', 'annulation_demandee')
            ->orderBy('billets.demande_annulation_le')
            ->get(['billets.*', 'client.Client', 'client.montant_payer', 'utilisateur.utilisateurs as demandeur']);
    }

    // Admin uniquement : annule directement (equivaut a une confirmation immediate).
    public function annulerDirect(Utilisateur $user, int $idBillets, ?string $motif): array
    {
        if ($user->droit !== 'Admin') {
            return ['ok' => false, 'type' => 'danger', 'message' => "Vous n'avez pas le droit d'annuler un billet."];
        }

        return $this->executerAnnulation($user, $idBillets, $motif);
    }

    // Admin uniquement : confirme une demande deja soumise par un chef d'escale (le motif
    // vient de la demande elle-meme, deja enregistre sur le billet).
    public function confirmerAnnulation(Utilisateur $user, int $idBillets): array
    {
        if ($user->droit !== 'Admin') {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Accès refusé.'];
        }

        return $this->executerAnnulation($user, $idBillets, null);
    }

    public function rejeterAnnulation(Utilisateur $user, int $idBillets): array
    {
        if ($user->droit !== 'Admin') {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Accès refusé.'];
        }

        $affecte = Billet::where('idBillets', $idBillets)->where('id_compagnie', $user->id_compagnie)
            ->where('status_billets', 'annulation_demandee')
            ->update([
                'status_billets' => null,
                'motif_annulation' => null,
                'demande_annulation_par' => null,
                'demande_annulation_le' => null,
            ]);

        if (! $affecte) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Aucune demande en attente pour ce billet.'];
        }

        return ['ok' => true, 'type' => 'info', 'message' => 'Demande d\'annulation rejetée : le billet reste actif.'];
    }

    // Coeur partagé annulation directe / confirmation de demande. Restaure la place vendue
    // (car ou suivis selon le jour), marque le billet annulé, et enregistre le remboursement
    // comme dépense. Écart avec le legacy : le remboursement visait la caisse de gare
    // (table `caisse`, morte dans cette app — voir DepotBanqueService/DepenseService pour le
    // même type d'adaptation) ; ici, la caisse individuelle actuellement ouverte pour cette
    // gare sert de cible.
    private function executerAnnulation(Utilisateur $user, int $idBillets, ?string $motifSiDirect): array
    {
        try {
            return DB::transaction(function () use ($user, $idBillets, $motifSiDirect) {
                $billet = Billet::where('idBillets', $idBillets)->where('id_compagnie', $user->id_compagnie)
                    ->lockForUpdate()->first();

                if (! $billet) {
                    throw new \RuntimeException('BILLET_INTROUVABLE');
                }
                if ($billet->status_billets === 'annule') {
                    throw new \RuntimeException('DEJA_ANNULE');
                }
                if (\Illuminate\Support\Carbon::parse($billet->jourVoyage)->toDateString() < now()->toDateString()) {
                    throw new \RuntimeException('VOYAGE_PASSE');
                }

                // Compare-and-swap (comparaison NULL-safe <=>) : bloque un double traitement
                // concurrent (deux confirmations, ou une confirmation + un rejet, en même temps).
                $ancienStatut = $billet->status_billets;
                $affecte = DB::table('billets')->where('idBillets', $idBillets)
                    ->where(function ($q) use ($ancienStatut) {
                        $ancienStatut === null ? $q->whereNull('status_billets') : $q->where('status_billets', $ancienStatut);
                    })
                    ->update(['status_billets' => 'annulation_en_cours']);
                if (! $affecte) {
                    throw new \RuntimeException('DEJA_TRAITE');
                }

                $motif = $ancienStatut === 'annulation_demandee' ? $billet->motif_annulation : ($motifSiDirect ?: null);
                $jourVoyage = \Illuminate\Support\Carbon::parse($billet->jourVoyage)->toDateString();
                $aujourdhui = now()->toDateString();

                $mainDest = Programme::resolveDestinationPrincipale($billet->departId, $billet->Heur_departs, $billet->destinationId, $billet->id_compagnie);
                $idAgenceBillet = Agence::where('localite', $billet->departId)->where('numeroGare', $billet->num_gare)
                    ->where('id_compagnie', $billet->id_compagnie)->value('idAgence');

                if ($jourVoyage === $aujourdhui) {
                    $idCarProgrammer = ProgrammationVoyage::where('id_horaire', $billet->Heur_departs)
                        ->where('date_enregistre', $jourVoyage)->where('id_trajet', $mainDest)
                        ->where('localite_user', $billet->departId)->where('id_agence', $idAgenceBillet)
                        ->where('id_compagnie', $billet->id_compagnie)->value('id_car_programmer');
                    if ($idCarProgrammer) {
                        $car = Car::where('id_car', $idCarProgrammer)->lockForUpdate()->first();
                        if ($car) {
                            $car->update(['nbr_place_reserve' => max(0, $car->nbr_place_reserve - $billet->nombrePassages)]);
                        }
                    }
                } else {
                    $suivi = Suivis::where('depart', $billet->departId)->where('destination', $mainDest)
                        ->where('heur_depart', $billet->Heur_departs)->where('date_reservation', $jourVoyage)
                        ->where('id_compagnie', $billet->id_compagnie)->lockForUpdate()->first();
                    if ($suivi) {
                        $suivi->update(['place_reserve' => max(0, $suivi->place_reserve - $billet->nombrePassages)]);
                    }
                }

                $billet->update([
                    'status_billets' => 'annule',
                    'date_annulation' => now(),
                    'motif_annulation' => $motif,
                    'annule_par' => $user->idUser,
                ]);

                // Remboursement enregistré comme dépense formelle, contre la caisse
                // individuelle actuellement ouverte pour cette gare (voir note ci-dessus).
                $caisse = $idAgenceBillet
                    ? CaisseUtilisateur::where('id_agence', $idAgenceBillet)
                        ->whereDate('date_service', now()->toDateString())->where('statut', 'ouverte')->first()
                    : null;
                // montant_payer vit sur `client`, pas sur `billets` — jamais chargé par la
                // requête lockForUpdate() ci-dessus (Billet seul, sans jointure).
                $montantPayerClient = Client::where('idClient', $billet->id_client)->value('montant_payer');
                $montant = (float) preg_replace('/[^\d.]/', '', (string) $montantPayerClient);

                if ($caisse && $montant > 0) {
                    Depense::create([
                        'id_compagnie' => $billet->id_compagnie,
                        'id_agence' => $idAgenceBillet,
                        'id_caisse_user' => $caisse->id_caisse_user,
                        'categorie' => 'Remboursement annulation',
                        'libelle' => 'Remboursement billet annulé n°'.$billet->numeroBillets,
                        'montant' => $montant,
                        'date_depense' => now()->toDateString(),
                        'id_utilisateur' => $user->idUser,
                        'statut' => 'valide',
                    ]);
                    $caisse->update(['montant_depense' => $caisse->montant_depense + $montant]);

                    return ['ok' => true, 'type' => 'success', 'message' => 'Billet annulé avec succès. Remboursement de '.number_format($montant, 0, ',', ' ').' FCFA enregistré comme dépense.'];
                }

                return ['ok' => true, 'type' => 'warning', 'message' => 'Billet annulé avec succès. Aucune caisse ouverte pour cette gare : le remboursement devra être enregistré manuellement.'];
            });
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'type' => 'danger', 'message' => match ($e->getMessage()) {
                'BILLET_INTROUVABLE' => 'Billet introuvable.',
                'DEJA_ANNULE' => 'Ce billet est déjà annulé.',
                'VOYAGE_PASSE' => "Impossible d'annuler un billet dont le voyage est déjà passé.",
                'DEJA_TRAITE' => 'Ce billet a déjà été traité entre-temps par quelqu\'un d\'autre.',
                default => "Erreur lors de l'annulation.",
            }];
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // REPORT DIRECT — port de Liste_du_jour::reporte_voyage(). Le mécanisme en 2 temps
    // (demande chef d'escale -> transmission -> confirmation Admin) reste à porter, il
    // n'est déclenché que depuis l'écran Embarquement (pas encore construit).
    // ─────────────────────────────────────────────────────────────────────

    // Écart volontaire avec le legacy : PDG bloqué explicitement (le legacy ne gate cette
    // action que par la permission Billets_reporte, que PDG a par défaut dans le catalogue —
    // seule action mutante de toute l'app qu'un rôle lecture-seule aurait pu déclencher).
    //
    // Ces contrôles (expiration, plage de dates, départ futur) sont faits ICI et non dans
    // appliquerReport(), fidèle au legacy : Liste_du_jours::reporter() les fait avant
    // d'appeler reporte_voyage(), mais confirmerReportBillet() (report en 2 temps) ne les
    // refait PAS avant d'appeler reporte_voyage() — il fait confiance à la validation déjà
    // faite au moment de la demande (demanderReportBillet()).
    public function reporter(Utilisateur $user, int $idBillets, string $nouvelleDate, string $nouvelleHeure): array
    {
        if ($user->estLectureSeule()) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Votre rôle est en lecture seule.'];
        }

        if ($nouvelleDate === '' || $nouvelleHeure === '') {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Veuillez choisir une nouvelle date et une heure de départ valides.'];
        }

        $billet = Billet::where('idBillets', $idBillets)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $billet) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Billet introuvable.'];
        }

        $aujourdhui = now()->toDateString();
        $demain = now()->addDay()->toDateString();
        $ancienJour = \Illuminate\Support\Carbon::parse($billet->jourVoyage)->toDateString();
        $nouveauJour = \Illuminate\Support\Carbon::parse($nouvelleDate)->toDateString();

        if ($ancienJour !== $nouveauJour || $billet->Heur_departs !== $nouvelleHeure) {
            if (\Illuminate\Support\Carbon::parse($billet->date_expiration)->endOfDay()->isPast()) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Ce billet a expiré, impossible de le reporter.'];
            }
            if (! in_array($nouveauJour, [$aujourdhui, $demain], true)) {
                return ['ok' => false, 'type' => 'danger', 'message' => "Le report n'est possible que vers aujourd'hui ou demain."];
            }
            if (\Illuminate\Support\Carbon::parse($nouvelleDate.' '.$nouvelleHeure)->lessThanOrEqualTo(now())) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'La nouvelle date/heure de départ doit être dans le futur.'];
            }
        }

        return $this->appliquerReport($idBillets, $user->id_compagnie, $nouvelleDate, $nouvelleHeure);
    }

    // Coeur du report (port de reporte_voyage()) : idempotence + bascule des compteurs de
    // places (libère l'ancien créneau, réserve le nouveau). Appelé par reporter() (direct)
    // et confirmerReportDemande() (report en 2 temps) — ne revalide PAS la plage de dates
    // (déjà fait à la création de la demande côté 2-temps, ou juste avant côté direct).
    private function appliquerReport(int $idBillets, int $idCompagnie, string $nouvelleDate, string $nouvelleHeure): array
    {
        try {
            return DB::transaction(function () use ($idBillets, $idCompagnie, $nouvelleDate, $nouvelleHeure) {
                $billet = Billet::where('idBillets', $idBillets)->where('id_compagnie', $idCompagnie)
                    ->lockForUpdate()->first();
                if (! $billet) {
                    throw new \RuntimeException('BILLET_INTROUVABLE');
                }

                $aujourdhui = now()->toDateString();
                $demain = now()->addDay()->toDateString();
                $ancienJour = \Illuminate\Support\Carbon::parse($billet->jourVoyage)->toDateString();
                $nouveauJour = \Illuminate\Support\Carbon::parse($nouvelleDate)->toDateString();

                // Idempotence anti double-soumission : déjà sur exactement ce créneau, on ne
                // touche à rien (double-clic, ou appel concurrent visant la même cible).
                if ($ancienJour === $nouveauJour && $billet->Heur_departs === $nouvelleHeure) {
                    return ['ok' => true, 'type' => 'info', 'message' => 'Le billet est déjà programmé sur cette date et cette heure.'];
                }

                $nombrePassages = (int) $billet->nombrePassages;
                $mainDest = Programme::resolveDestinationPrincipale($billet->departId, $billet->Heur_departs, $billet->destinationId, $billet->id_compagnie);
                $idAgenceBillet = Agence::where('localite', $billet->departId)->where('numeroGare', $billet->num_gare)
                    ->where('id_compagnie', $billet->id_compagnie)->value('idAgence');

                // 1) Libère la place sur l'ancien créneau, si suivi (aujourd'hui ou demain).
                if ($ancienJour === $aujourdhui) {
                    $idCarProgrammer = ProgrammationVoyage::where('id_horaire', $billet->Heur_departs)
                        ->where('date_enregistre', $ancienJour)->where('id_trajet', $mainDest)
                        ->where('localite_user', $billet->departId)->where('id_agence', $idAgenceBillet)
                        ->where('id_compagnie', $billet->id_compagnie)->value('id_car_programmer');
                    if ($idCarProgrammer) {
                        $car = Car::where('id_car', $idCarProgrammer)->lockForUpdate()->first();
                        if ($car) {
                            $car->update(['nbr_place_reserve' => max(0, $car->nbr_place_reserve - $nombrePassages)]);
                        }
                    }
                } elseif ($ancienJour === $demain) {
                    $suivi = Suivis::where('depart', $billet->departId)->where('destination', $mainDest)
                        ->where('heur_depart', $billet->Heur_departs)->where('date_reservation', $ancienJour)
                        ->where('id_compagnie', $billet->id_compagnie)->lockForUpdate()->first();
                    if ($suivi) {
                        $suivi->update(['place_reserve' => max(0, $suivi->place_reserve - $nombrePassages)]);
                    }
                }

                // 2) Réserve la place sur le nouveau créneau + recalcule le numéro de place.
                $numPlace = $billet->numeroPlace;

                if ($nouveauJour === $aujourdhui) {
                    $idCarProgrammer = ProgrammationVoyage::where('id_horaire', $nouvelleHeure)
                        ->where('date_enregistre', $nouveauJour)->where('id_trajet', $mainDest)
                        ->where('localite_user', $billet->departId)->where('id_agence', $idAgenceBillet)
                        ->where('id_compagnie', $billet->id_compagnie)->value('id_car_programmer');
                    if (! $idCarProgrammer) {
                        throw new \RuntimeException('AUCUN_CAR_PROGRAMME');
                    }

                    $car = Car::where('id_car', $idCarProgrammer)->lockForUpdate()->first();
                    if (! $car) {
                        throw new \RuntimeException('CAR_INTROUVABLE');
                    }
                    $placesDispo = $car->nbr_place - $car->nbr_place_reserve;
                    if ($nombrePassages > $placesDispo) {
                        throw new \RuntimeException("PLACES_INSUFFISANTES:$placesDispo");
                    }
                    $car->update(['nbr_place_reserve' => $car->nbr_place_reserve + $nombrePassages]);
                    $start = $car->nbr_place_reserve + 1;
                    $end = $start + $nombrePassages - 1;
                    $numPlace = $nombrePassages === 1 ? (string) $start : "$start-$end";
                } elseif ($nouveauJour === $demain) {
                    $placeTotale = (int) (PlaceMinimale::where('id_compagnie', $billet->id_compagnie)->value('place_minumale') ?? 0);

                    $suivi = Suivis::where('depart', $billet->departId)->where('destination', $mainDest)
                        ->where('heur_depart', $nouvelleHeure)->where('date_reservation', $nouveauJour)
                        ->where('id_compagnie', $billet->id_compagnie)->lockForUpdate()->first();

                    if ($suivi) {
                        $placesDispo = $suivi->place_totals - $suivi->place_reserve;
                        if ($nombrePassages > $placesDispo) {
                            throw new \RuntimeException("PLACES_INSUFFISANTES_DEMAIN:$placesDispo");
                        }
                        $suivi->increment('place_reserve', $nombrePassages);
                    } else {
                        if ($placeTotale <= 0) {
                            throw new \RuntimeException('PLACE_MINIMALE_NON_DEFINIE');
                        }
                        if ($nombrePassages > $placeTotale) {
                            throw new \RuntimeException("PLACES_INSUFFISANTES_DEMAIN:$placeTotale");
                        }
                        Suivis::create([
                            'place_reserve' => $nombrePassages, 'place_totals' => $placeTotale,
                            'depart' => $billet->departId, 'destination' => $mainDest,
                            'heur_depart' => $nouvelleHeure, 'date_reservation' => $nouveauJour,
                            'id_agence' => $idAgenceBillet, 'id_compagnie' => $billet->id_compagnie,
                        ]);
                    }

                    $placesPrises = [];
                    foreach (Billet::where('jourVoyage', $nouveauJour)->where('Heur_departs', $nouvelleHeure)
                        ->where('departId', $billet->departId)->where('destinationId', $mainDest)
                        ->where('id_compagnie', $billet->id_compagnie)->where('idBillets', '!=', $idBillets)
                        ->pluck('numeroPlace') as $np) {
                        foreach (explode('-', (string) $np) as $p) {
                            if (is_numeric($p)) {
                                $placesPrises[] = (int) $p;
                            }
                        }
                    }
                    $start = 1;
                    $attribues = [];
                    while (count($attribues) < $nombrePassages) {
                        if (! in_array($start, $placesPrises, true)) {
                            $attribues[] = $start;
                        }
                        $start++;
                    }
                    $numPlace = implode('-', $attribues);
                }

                // 3) Met à jour le billet lui-même.
                $billet->update([
                    'jourVoyage' => $nouveauJour,
                    'Heur_departs' => $nouvelleHeure,
                    'numeroPlace' => $numPlace,
                    'date_repporte' => now()->toDateString(),
                ]);

                return ['ok' => true, 'type' => 'success', 'message' => 'Voyage reporté avec succès, places mises à jour.'];
            });
        } catch (\RuntimeException $e) {
            [$type, $valeur] = array_pad(explode(':', $e->getMessage(), 2), 2, null);

            return ['ok' => false, 'type' => 'danger', 'message' => match ($type) {
                'BILLET_INTROUVABLE' => 'Billet introuvable.',
                'BILLET_EXPIRE' => 'Ce billet a expiré, impossible de le reporter.',
                'DATE_INVALIDE' => "Le report n'est possible que vers aujourd'hui ou demain.",
                'DEPART_PASSE' => 'La nouvelle date/heure de départ doit être dans le futur.',
                'AUCUN_CAR_PROGRAMME' => 'Aucun car programmé pour cette heure et ce trajet à la nouvelle date.',
                'CAR_INTROUVABLE' => 'Car introuvable pour la nouvelle date.',
                'PLACES_INSUFFISANTES' => "Places insuffisantes sur le nouveau créneau : $valeur restantes.",
                'PLACE_MINIMALE_NON_DEFINIE' => 'Erreur : nombre de places minimales non défini.',
                'PLACES_INSUFFISANTES_DEMAIN' => "Places insuffisantes pour demain sur le nouveau créneau : $valeur restantes.",
                default => 'Erreur lors du report.',
            }];
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // EMBARQUEMENT — port des méthodes homonymes de Liste_du_jour.php.
    // ─────────────────────────────────────────────────────────────────────

    // Billets d'un jour donné, prêts à embarquer. Comparaison faite avec l'heure PHP (pas
    // NOW()/CURDATE() côté MySQL) : le legacy documente un décalage possible entre le fuseau
    // du serveur applicatif et celui du serveur DB — reproduit ici par cohérence, même si les
    // deux tournent sur la même machine dans cette app.
    public function getBilletsPourEmbarquement(Utilisateur $user, string $jour, ?string $destination, ?string $heure): Collection
    {
        $isAdmin = in_array($user->droit, ['Admin', 'PDG', 'secretaire'], true);
        $maintenant = now()->format('Y-m-d H:i:s');

        return DB::table('billets as b')
            ->join('client as c', 'b.id_client', '=', 'c.idClient')
            ->leftJoin('utilisateur as ue', 'ue.idUser', '=', 'b.embarque_par')
            ->leftJoin('programmation_voyage as pv', function ($join) {
                $join->on('pv.date_enregistre', '=', 'b.jourVoyage')
                    ->on('pv.id_horaire', '=', 'b.Heur_departs')
                    ->on('pv.id_trajet', '=', 'b.destinationId')
                    ->on('pv.localite_user', '=', 'b.departId')
                    ->on('pv.id_compagnie', '=', 'b.id_compagnie')
                    ->where('pv.statut', 'active');
            })
            ->where('b.id_compagnie', $user->id_compagnie)
            ->where('b.jourVoyage', $jour)
            ->where(fn ($q) => $q->whereNull('b.status_billets')->orWhere('b.status_billets', ''))
            ->where(function ($q) use ($maintenant) {
                $q->whereNull('b.statut_embarquement')->orWhere('b.statut_embarquement', '!=', 'embarque')
                    ->orWhereRaw('TIMESTAMP(b.jourVoyage, b.Heur_departs) >= ?', [$maintenant]);
            })
            ->when(! $isAdmin, fn ($q) => $q->where('b.departId', $user->agence?->localite)->where('b.num_gare', $user->agence?->numeroGare))
            ->when($destination, fn ($q) => $q->where('b.destinationId', $destination))
            ->when($heure, fn ($q) => $q->where('b.Heur_departs', $heure))
            ->orderBy('b.Heur_departs')->orderBy('c.Client')
            ->get(['b.idBillets', 'b.numeroBillets', 'b.destinationId', 'b.Heur_departs', 'b.numeroPlace',
                'b.statut_embarquement', 'b.embarque_le', 'c.Client', 'ue.utilisateurs as embarque_par_nom', 'pv.decolle_le as bus_decolle_le']);
    }

    // Le bus associé à ce billet a-t-il déjà "décollé" ? Le car programmé peut avoir changé
    // de créneau (report) : on prend la programmation active la plus récente correspondant
    // exactement au trajet du billet.
    private function busDejaDecolle(Billet $billet): bool
    {
        $prog = ProgrammationVoyage::where('date_enregistre', \Illuminate\Support\Carbon::parse($billet->jourVoyage)->toDateString())
            ->where('id_horaire', $billet->Heur_departs)->where('id_trajet', $billet->destinationId)
            ->where('localite_user', $billet->departId)->where('id_compagnie', $billet->id_compagnie)
            ->where('statut', 'active')->orderByDesc('id_programmation')->first();

        return $prog !== null && $prog->decolle_le !== null;
    }

    // Logique commune, réutilisée par embarquer() en solo et embarquerLot() en masse.
    private function embarquerCore(Billet $billet, Utilisateur $user): array
    {
        if ($billet->statut_embarquement === 'embarque') {
            return ['ok' => true, 'deja' => true, 'message' => 'Déjà embarqué.'];
        }
        if (! in_array($billet->status_billets, [null, ''], true)) {
            return ['ok' => false, 'deja' => false, 'message' => "Annulé ou en attente de traitement (report/annulation) : impossible de l'embarquer."];
        }
        if ($this->busDejaDecolle($billet)) {
            return ['ok' => false, 'deja' => false, 'message' => "Le bus a déjà décollé : impossible d'embarquer."];
        }

        $billet->update(['statut_embarquement' => 'embarque', 'embarque_le' => now(), 'embarque_par' => $user->idUser]);

        return ['ok' => true, 'deja' => false, 'message' => 'Client embarqué.'];
    }

    public function embarquer(Utilisateur $user, int $idBillets): array
    {
        $billet = Billet::where('idBillets', $idBillets)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $billet) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Billet introuvable.'];
        }

        $res = $this->embarquerCore($billet, $user);

        return ['ok' => $res['ok'] && ! $res['deja'], 'type' => $res['deja'] ? 'warning' : ($res['ok'] ? 'success' : 'danger'), 'message' => $res['message']];
    }

    // Embarquement en masse : boucle sur la même logique unitaire, résumé agrégé (pas de
    // flash par billet).
    public function embarquerLot(Utilisateur $user, array $idsBillets): array
    {
        $succes = 0;
        $deja = 0;
        $echecs = 0;

        foreach ($idsBillets as $idBillets) {
            $billet = Billet::where('idBillets', (int) $idBillets)->where('id_compagnie', $user->id_compagnie)->first();
            if (! $billet) {
                $echecs++;

                continue;
            }
            $res = $this->embarquerCore($billet, $user);
            if ($res['deja']) {
                $deja++;
            } elseif ($res['ok']) {
                $succes++;
            } else {
                $echecs++;
            }
        }

        return ['ok' => true, 'succes' => $succes, 'deja' => $deja, 'echecs' => $echecs];
    }

    // Annule un embarquement marqué par erreur — possible tant que le bus n'a pas réellement
    // décollé.
    public function annulerEmbarquement(Utilisateur $user, int $idBillets): array
    {
        $billet = Billet::where('idBillets', $idBillets)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $billet || $billet->statut_embarquement !== 'embarque') {
            return ['ok' => false, 'type' => 'warning', 'message' => "Ce client n'est pas marqué comme embarqué."];
        }
        if ($this->busDejaDecolle($billet)) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Le bus a déjà décollé : impossible d\'annuler cet embarquement.'];
        }

        $billet->update(['statut_embarquement' => null, 'embarque_le' => null, 'embarque_par' => null]);

        return ['ok' => true, 'type' => 'info', 'message' => 'Embarquement annulé.'];
    }

    // Cars programmés aujourd'hui pour cette gare (tous, quel que soit le taux de remplissage),
    // avec le nombre de passagers restant à traiter (ni embarqué, ni annulé) — alimente le
    // bouton "Faire décoller".
    public function getCarsDuJourPourEmbarquement(Utilisateur $user, string $jour): Collection
    {
        $isAdmin = in_array($user->droit, ['Admin', 'PDG', 'secretaire'], true);
        $idAgence = ! $isAdmin ? Agence::where('localite', $user->agence?->localite)->where('numeroGare', $user->agence?->numeroGare)
            ->where('id_compagnie', $user->id_compagnie)->value('idAgence') : null;

        return DB::table('programmation_voyage as pv')
            ->join('car as c', 'c.id_car', '=', 'pv.id_car_programmer')
            ->leftJoin('utilisateur as ud', 'ud.idUser', '=', 'pv.decolle_par')
            ->where('pv.date_enregistre', $jour)->where('pv.id_compagnie', $user->id_compagnie)->where('pv.statut', 'active')
            ->when($idAgence, fn ($q) => $q->where('pv.id_agence', $idAgence))
            ->when(! $isAdmin && ! $idAgence, fn ($q) => $q->where('pv.localite_user', $user->agence?->localite))
            ->orderBy('pv.id_horaire')
            ->get([
                'pv.id_programmation', 'pv.id_trajet as destination', 'pv.id_horaire as heure',
                'pv.localite_user as depart', 'pv.decolle_le', 'pv.decolle_par', 'ud.utilisateurs as decolle_par_nom',
                'c.numero_car', 'c.matriculle', 'c.nbr_place', 'c.nbr_place_reserve',
                DB::raw("(SELECT COUNT(*) FROM billets b
                    WHERE b.jourVoyage = pv.date_enregistre AND b.Heur_departs = pv.id_horaire
                      AND b.destinationId = pv.id_trajet AND b.departId = pv.localite_user
                      AND b.id_compagnie = pv.id_compagnie
                      AND (b.statut_embarquement IS NULL OR b.statut_embarquement != 'embarque')
                      AND (b.status_billets IS NULL OR b.status_billets != 'annule')
                ) as nb_restants"),
            ]);
    }

    // Cars "complets" (places réservées >= capacité) ayant encore au moins un passager non
    // traité ce jour-là — alimente la bannière d'alerte de l'écran Embarquement.
    public function getCarsComplets(Utilisateur $user, string $jour): Collection
    {
        $isAdmin = in_array($user->droit, ['Admin', 'PDG', 'secretaire'], true);
        $idAgence = ! $isAdmin ? Agence::where('localite', $user->agence?->localite)->where('numeroGare', $user->agence?->numeroGare)
            ->where('id_compagnie', $user->id_compagnie)->value('idAgence') : null;
        $maintenant = now()->format('Y-m-d H:i:s');

        return DB::table('programmation_voyage as pv')
            ->join('car as c', 'c.id_car', '=', 'pv.id_car_programmer')
            ->where('pv.date_enregistre', $jour)->where('pv.id_compagnie', $user->id_compagnie)->where('pv.statut', 'active')
            ->where('c.nbr_place', '>', 0)->whereColumn('c.nbr_place_reserve', '>=', 'c.nbr_place')
            ->whereRaw('TIMESTAMP(pv.date_enregistre, pv.id_horaire) >= ?', [$maintenant])
            ->when($idAgence, fn ($q) => $q->where('pv.id_agence', $idAgence))
            ->when(! $isAdmin && ! $idAgence, fn ($q) => $q->where('pv.localite_user', $user->agence?->localite))
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('billets as b')
                    ->whereColumn('b.jourVoyage', 'pv.date_enregistre')->whereColumn('b.Heur_departs', 'pv.id_horaire')
                    ->whereColumn('b.destinationId', 'pv.id_trajet')->whereColumn('b.departId', 'pv.localite_user')
                    ->whereColumn('b.id_compagnie', 'pv.id_compagnie')
                    ->where(fn ($qq) => $qq->whereNull('b.status_billets')->orWhere('b.status_billets', ''))
                    ->where(fn ($qq) => $qq->whereNull('b.statut_embarquement')->orWhere('b.statut_embarquement', '!=', 'embarque'));
            })
            ->orderBy('pv.id_horaire')
            ->get(['pv.id_trajet as destination', 'pv.id_horaire as heure', 'pv.localite_user as depart',
                'c.numero_car', 'c.matriculle', 'c.nbr_place', 'c.nbr_place_reserve']);
    }

    // IDOR-guard chef d'escale déjà présent dans le legacy : ne peut décoller qu'un car
    // partant de sa propre gare.
    public function decollerCar(Utilisateur $user, int $idProgrammation): array
    {
        $prog = ProgrammationVoyage::where('id_programmation', $idProgrammation)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $prog) {
            return ['ok' => false, 'message' => 'Programmation introuvable.'];
        }
        if ($prog->decolle_le) {
            return ['ok' => false, 'message' => 'Ce bus a déjà décollé.'];
        }
        if ($user->droit === 'chef_d_escale' && $prog->localite_user !== $user->agence?->localite) {
            return ['ok' => false, 'message' => 'Ce car ne part pas de votre gare.'];
        }

        $nbRestants = Billet::where('jourVoyage', $prog->date_enregistre)->where('Heur_departs', $prog->id_horaire)
            ->where('destinationId', $prog->id_trajet)->where('departId', $prog->localite_user)
            ->where('id_compagnie', $user->id_compagnie)
            ->where(fn ($q) => $q->whereNull('statut_embarquement')->orWhere('statut_embarquement', '!=', 'embarque'))
            ->where(fn ($q) => $q->whereNull('status_billets')->orWhere('status_billets', '!=', 'annule'))
            ->count();
        if ($nbRestants > 0) {
            return ['ok' => false, 'message' => "$nbRestants passager(s) non traité(s) (ni embarqué, ni annulé) : impossible de faire décoller le bus."];
        }

        // Compare-and-swap : bloque une double confirmation concurrente du décollage.
        $affecte = ProgrammationVoyage::where('id_programmation', $idProgrammation)->where('id_compagnie', $user->id_compagnie)
            ->whereNull('decolle_le')
            ->update(['decolle_le' => now(), 'decolle_par' => $user->idUser]);

        if (! $affecte) {
            return ['ok' => false, 'message' => "Ce bus a déjà été traité entre-temps par quelqu'un d'autre."];
        }

        return ['ok' => true, 'message' => 'Bus décollé avec ses passagers.'];
    }

    // ─────────────────────────────────────────────────────────────────────
    // REPORT EN 2 TEMPS — soumis à validation Admin, uniquement déclenché depuis
    // l'écran Embarquement (client non présenté). Port des méthodes homonymes de
    // Liste_du_jour.php.
    // ─────────────────────────────────────────────────────────────────────

    // Flux à deux étapes, intelligent selon qui demande : un simple Utilisateur passe
    // d'abord par le chef d'escale de sa gare ('report_demande') ; un chef d'escale (ou
    // Admin/PDG) a déjà l'autorité de transmission, sa propre demande part directement vers
    // l'Admin ('report_transmis'), sans étape intermédiaire inutile.
    public function demanderReport(Utilisateur $user, int $idBillets, string $nouvelleDate, string $nouvelleHeure): array
    {
        $billet = Billet::where('idBillets', $idBillets)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $billet) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Billet introuvable.'];
        }
        if ($billet->statut_embarquement === 'embarque') {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Ce client est déjà embarqué : aucune raison de le reporter.'];
        }
        if (! in_array($billet->status_billets, [null, ''], true)) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Ce billet est déjà annulé ou une demande est déjà en cours.'];
        }
        if ($nouvelleDate === '' || $nouvelleHeure === '') {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Veuillez choisir une nouvelle date et une heure de départ valides.'];
        }

        $nouveauJour = \Illuminate\Support\Carbon::parse($nouvelleDate)->toDateString();
        if (! in_array($nouveauJour, [now()->toDateString(), now()->addDay()->toDateString()], true)) {
            return ['ok' => false, 'type' => 'danger', 'message' => "Le report n'est possible que vers aujourd'hui ou demain."];
        }

        $sauteEtapeChef = in_array($user->droit, ['chef_d_escale', 'Admin', 'PDG', 'secretaire'], true);

        $billet->update(array_filter([
            'status_billets' => $sauteEtapeChef ? 'report_transmis' : 'report_demande',
            'nouvelle_date_demandee' => $nouveauJour,
            'nouvelle_heure_demandee' => $nouvelleHeure,
            'demande_report_par' => $user->idUser,
            'demande_report_le' => now(),
            'report_transmis_par' => $sauteEtapeChef ? $user->idUser : null,
            'report_transmis_le' => $sauteEtapeChef ? now() : null,
        ], fn ($v) => $v !== null));

        $message = $sauteEtapeChef ? "Demande de report envoyée : un Admin doit la valider." : "Demande de report envoyée à votre chef d'escale.";

        return ['ok' => true, 'type' => 'success', 'message' => $message];
    }

    // Étape 1 (chef d'escale) : demandes de sa propre gare en attente de son examen.
    public function getDemandesReportEnAttenteChef(Utilisateur $user): Collection
    {
        return DB::table('billets as b')
            ->join('client as c', 'b.id_client', '=', 'c.idClient')
            ->leftJoin('utilisateur as u', 'u.idUser', '=', 'b.demande_report_par')
            ->where('b.id_compagnie', $user->id_compagnie)->where('b.status_billets', 'report_demande')
            ->where('b.departId', $user->agence?->localite)->where('b.num_gare', $user->agence?->numeroGare)
            ->orderBy('b.demande_report_le')
            ->get(['b.idBillets', 'b.numeroBillets', 'b.jourVoyage', 'b.Heur_departs', 'b.departId', 'b.destinationId',
                'b.nouvelle_date_demandee', 'b.nouvelle_heure_demandee', 'b.demande_report_le', 'c.Client', 'u.utilisateurs as demandeur']);
    }

    // Étape 2 (Admin) : demandes déjà transmises, en attente de validation finale.
    public function getDemandesReportEnAttente(int $idCompagnie): Collection
    {
        return DB::table('billets as b')
            ->join('client as c', 'b.id_client', '=', 'c.idClient')
            ->leftJoin('utilisateur as u', 'u.idUser', '=', 'b.demande_report_par')
            ->leftJoin('utilisateur as ut', 'ut.idUser', '=', 'b.report_transmis_par')
            ->where('b.id_compagnie', $idCompagnie)->where('b.status_billets', 'report_transmis')
            ->orderBy('b.report_transmis_le')
            ->get(['b.idBillets', 'b.numeroBillets', 'b.jourVoyage', 'b.Heur_departs', 'b.departId', 'b.destinationId',
                'b.nouvelle_date_demandee', 'b.nouvelle_heure_demandee', 'b.demande_report_le',
                'c.Client', 'u.utilisateurs as demandeur', 'ut.utilisateurs as transmis_par_nom']);
    }

    // Étape 1 -> 2 (chef d'escale uniquement) : transmet une demande de sa gare à l'Admin.
    public function transmettreReport(Utilisateur $user, int $idBillets): array
    {
        if ($user->droit !== 'chef_d_escale') {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Accès refusé.'];
        }

        $billet = Billet::where('idBillets', $idBillets)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $billet || $billet->status_billets !== 'report_demande') {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Aucune demande de report en attente pour ce billet.'];
        }
        if ($billet->departId !== $user->agence?->localite || $billet->num_gare !== $user->agence?->numeroGare) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Cette demande ne concerne pas votre gare.'];
        }

        $affecte = Billet::where('idBillets', $idBillets)->where('status_billets', 'report_demande')
            ->update(['status_billets' => 'report_transmis', 'report_transmis_par' => $user->idUser, 'report_transmis_le' => now()]);

        if (! $affecte) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette demande a déjà été traitée entre-temps par quelqu\'un d\'autre.'];
        }

        return ['ok' => true, 'type' => 'success', 'message' => "Demande transmise à l'Admin pour validation."];
    }

    // Rejet, possible aux deux étapes : chef d'escale (sa gare, étape 1 uniquement) ou Admin
    // (n'importe quelle gare, aux deux étapes).
    public function rejeterReportDemande(Utilisateur $user, int $idBillets): array
    {
        $billet = Billet::where('idBillets', $idBillets)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $billet || ! in_array($billet->status_billets, ['report_demande', 'report_transmis'], true)) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Aucune demande de report en attente pour ce billet.'];
        }

        if ($user->droit === 'chef_d_escale') {
            $estSaGare = $billet->departId === $user->agence?->localite && $billet->num_gare === $user->agence?->numeroGare;
            if ($billet->status_billets !== 'report_demande' || ! $estSaGare) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Vous ne pouvez pas rejeter cette demande.'];
            }
        } elseif ($user->droit !== 'Admin') {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Accès refusé.'];
        }

        $affecte = Billet::where('idBillets', $idBillets)->where('status_billets', $billet->status_billets)
            ->update([
                'status_billets' => null, 'nouvelle_date_demandee' => null, 'nouvelle_heure_demandee' => null,
                'demande_report_par' => null, 'demande_report_le' => null,
                'report_transmis_par' => null, 'report_transmis_le' => null,
            ]);

        if (! $affecte) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette demande a déjà été traitée entre-temps par quelqu\'un d\'autre.'];
        }

        return ['ok' => true, 'type' => 'info', 'message' => 'Demande de report rejetée : le billet reste sur son départ initial.'];
    }

    // Étape finale (Admin) : valide la demande déjà transmise, applique réellement le report
    // (réutilise appliquerReport(), qui gère places/car/suivis), puis nettoie les traces de
    // la demande. Un billet re-programmé nécessite un nouvel embarquement.
    public function confirmerReportDemande(Utilisateur $user, int $idBillets): array
    {
        if ($user->droit !== 'Admin') {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Accès refusé.'];
        }

        $billet = Billet::where('idBillets', $idBillets)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $billet || $billet->status_billets !== 'report_transmis') {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Aucune demande de report transmise en attente pour ce billet.'];
        }

        // Réserve la demande de façon atomique avant de toucher aux places : sans ça, deux
        // confirmations (ou une confirmation + un rejet) lancées en même temps passeraient
        // toutes les deux le test ci-dessus et appliquerReport() serait appelé deux fois.
        $claim = Billet::where('idBillets', $idBillets)->where('status_billets', 'report_transmis')
            ->update(['status_billets' => 'report_en_validation']);
        if (! $claim) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette demande a déjà été traitée entre-temps par quelqu\'un d\'autre.'];
        }

        $resultat = $this->appliquerReport($idBillets, $user->id_compagnie, $billet->nouvelle_date_demandee->toDateString(), $billet->nouvelle_heure_demandee);

        if (! $resultat['ok']) {
            // appliquerReport() a déjà son propre message d'erreur (places insuffisantes,
            // car introuvable...) : on remet la demande en attente pour que l'Admin puisse
            // réessayer, plutôt que de la laisser bloquée sur le marqueur transitoire.
            Billet::where('idBillets', $idBillets)->update(['status_billets' => 'report_transmis']);

            return $resultat;
        }

        Billet::where('idBillets', $idBillets)->update([
            'status_billets' => null, 'nouvelle_date_demandee' => null, 'nouvelle_heure_demandee' => null,
            'demande_report_par' => null, 'demande_report_le' => null,
            'report_transmis_par' => null, 'report_transmis_le' => null,
            'statut_embarquement' => null, 'embarque_le' => null, 'embarque_par' => null,
        ]);

        return ['ok' => true, 'type' => 'success', 'message' => 'Report validé : le billet est désormais programmé à la nouvelle date.'];
    }
}
