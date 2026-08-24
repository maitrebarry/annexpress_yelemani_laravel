<?php

namespace App\Services;

use App\Mail\ReservationConfirmee;
use App\Models\Billet;
use App\Models\Car;
use App\Models\Client;
use App\Models\Compagnie;
use App\Models\LigneTrajet;
use App\Models\PlaceMinimale;
use App\Models\Programme;
use App\Models\ProgrammationVoyage;
use App\Models\Suivis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Port de Projets_licence/app/controllers/site/Reservation_formulaire.php::index()
 * (partie POST) — réservation en ligne, sans opérateur/caisse (contrairement à
 * App\Services\BilletService, utilisé par l'achat au guichet). Le billet créé reste
 * `validation_billets = 'en_attente'` / `status_reservation = 'en_ligne'` jusqu'à
 * validation manuelle côté admin (écran "Ticket en entente", pas encore construit —
 * voir mémoire projet).
 */
class ReservationEnLigneService
{
    public function creerReservation(array $data): array
    {
        $programme = Programme::trouverAvecEscales((int) ($data['id_programme'] ?? 0));
        // Le site public est dédié à une seule compagnie (voir Compagnie::site() /
        // config/site.php) : un trajet d'une autre compagnie est rejeté même si son id
        // existe en base — défense en profondeur, une requête forgée à la main ne doit pas
        // pouvoir créer une réservation pour une autre compagnie.
        if (! $programme || $programme->id_compagnie !== Compagnie::site()->id_compagnie) {
            return ['ok' => false, 'message' => 'Trajet invalide.'];
        }

        $departId = trim((string) ($data['departId'] ?? ''));
        $destinationId = trim((string) ($data['destinationId'] ?? ''));
        $idCompagnie = (int) ($data['id_compagnie'] ?? 0);
        $numeroGare = trim((string) ($data['numeroGare'] ?? ''));
        $escaleFinale = trim((string) ($data['escale_finale'] ?? '')) ?: null;
        $destinationAEnregistrer = $escaleFinale ?: $destinationId;
        $heureDepart = trim((string) ($data['heureDepart'] ?? ''));
        $nomClient = trim((string) ($data['Client'] ?? ''));
        $nbPassagers = (int) ($data['nombrePassages'] ?? 0);
        $telephone = trim((string) ($data['numeroClient'] ?? ''));
        $numeroPaiement = trim((string) ($data['numeroPaiement'] ?? ''));
        $emailClient = trim((string) ($data['emailClient'] ?? ''));

        if ($nomClient === '' || $telephone === '' || $numeroPaiement === '' || $nbPassagers <= 0) {
            return ['ok' => false, 'message' => 'Tous les champs obligatoires doivent être remplis.'];
        }

        $jourVoyage = $data['jourVoyage'] ?? '';
        $aujourdhui = now()->toDateString();
        $demain = now()->addDay()->toDateString();
        if (! in_array($jourVoyage, [$aujourdhui, $demain], true)) {
            return ['ok' => false, 'message' => 'Date invalide : choisissez aujourd\'hui ou demain.'];
        }

        // Prix recalculé côté serveur : jamais confiance dans un montant posté par le
        // client (falsifiable via les DevTools), fidèle au legacy.
        $prixUnitaire = (int) $programme->prix;
        if ($escaleFinale) {
            $prixEscale = LigneTrajet::query()
                ->join('escale as e', 'e.id_escale', '=', 'ligneTrajet.id_escales')
                ->where('ligneTrajet.id_trajets', $programme->idProgrammer)
                ->where('ligneTrajet.type_trajet', 'programmer')
                ->where('e.escales', $escaleFinale)
                ->value('ligneTrajet.prix_escale');
            if ($prixEscale !== null) {
                $prixUnitaire = (int) $prixEscale;
            }
        }
        $prixTotal = $prixUnitaire * $nbPassagers;

        try {
            return DB::transaction(function () use (
                $departId, $destinationId, $idCompagnie, $numeroGare, $destinationAEnregistrer,
                $heureDepart, $nomClient, $nbPassagers, $telephone, $numeroPaiement, $emailClient,
                $jourVoyage, $aujourdhui, $prixTotal
            ) {
                $numPlace = '-';
                $idCarProgrammer = null;

                if ($jourVoyage === $aujourdhui) {
                    $prog = ProgrammationVoyage::where('id_horaire', $heureDepart)
                        ->where('date_enregistre', $jourVoyage)
                        ->where('id_trajet', $destinationId)
                        ->where('localite_user', $departId)
                        ->where('id_compagnie', $idCompagnie)
                        ->first();

                    if (! $prog) {
                        throw new \RuntimeException('AUCUN_CAR_PROGRAMME');
                    }
                    $idCarProgrammer = $prog->id_car_programmer;

                    // Verrou anti-surbooking : deux réservations simultanées ne doivent
                    // jamais lire le même nbr_place_reserve avant qu'aucune n'écrive.
                    $car = Car::where('id_car', $idCarProgrammer)->lockForUpdate()->first();
                    if (! $car) {
                        throw new \RuntimeException('CAR_INTROUVABLE');
                    }

                    $placesDispo = $car->nbr_place - $car->nbr_place_reserve;
                    if ($nbPassagers > $placesDispo) {
                        throw new \RuntimeException("PLACES_INSUFFISANTES:$placesDispo");
                    }

                    $start = (int) $car->nbr_place_reserve + 1;
                    $end = $start + $nbPassagers - 1;
                    $numPlace = $nbPassagers === 1 ? (string) $start : "$start-$end";
                } else {
                    // Demain : le legacy ne limitait pas du tout les places pour la
                    // réservation en ligne (contrairement à l'achat au guichet). Corrigé
                    // ici en réutilisant le même mécanisme `suivis`/`PlaceMinimale` que
                    // App\Services\BilletService, pour éviter un surbooking illimité.
                    $placeTotale = (int) (PlaceMinimale::where('id_compagnie', $idCompagnie)->value('place_minumale') ?? 0);
                    if ($placeTotale <= 0) {
                        throw new \RuntimeException('PLACE_MINIMALE_NON_DEFINIE');
                    }

                    $suivi = Suivis::where('depart', $departId)
                        ->where('destination', $destinationAEnregistrer)
                        ->where('heur_depart', $heureDepart)
                        ->where('date_reservation', $jourVoyage)
                        ->where('id_compagnie', $idCompagnie)
                        ->lockForUpdate()
                        ->first();

                    if ($suivi) {
                        $placesDispo = $suivi->place_totals - $suivi->place_reserve;
                        if ($nbPassagers > $placesDispo) {
                            throw new \RuntimeException("PLACES_INSUFFISANTES_DEMAIN:$placesDispo");
                        }
                        $suivi->increment('place_reserve', $nbPassagers);
                    } else {
                        if ($nbPassagers > $placeTotale) {
                            throw new \RuntimeException("PLACES_INSUFFISANTES_DEMAIN:$placeTotale");
                        }
                        Suivis::create([
                            'place_reserve' => $nbPassagers,
                            'place_totals' => $placeTotale,
                            'depart' => $departId,
                            'destination' => $destinationAEnregistrer,
                            'heur_depart' => $heureDepart,
                            'date_reservation' => $jourVoyage,
                            'id_compagnie' => $idCompagnie,
                        ]);
                    }
                }

                $client = Client::create([
                    'Client' => $nomClient,
                    'numeroClient' => $telephone,
                    'emailClient' => $emailClient ?: null,
                    'date_enregistrement' => now()->toDateString(),
                    'montant_payer' => $prixTotal,
                    'numeroPaiement' => $numeroPaiement,
                    'id_compagnie' => $idCompagnie,
                ]);

                $numeroBillets = $this->genererNumeroBilletUnique();

                $billet = Billet::create([
                    'id_client' => $client->idClient,
                    'numeroBillets' => $numeroBillets,
                    'jourVoyage' => $jourVoyage,
                    'Heur_departs' => $heureDepart,
                    'nombrePassages' => $nbPassagers,
                    'destinationId' => $destinationAEnregistrer,
                    'departId' => $departId,
                    'date_expiration' => \Illuminate\Support\Carbon::parse($jourVoyage)->addWeek()->toDateString(),
                    'numeroPlace' => $numPlace,
                    'date_reservation' => now()->toDateString(),
                    'status_reservation' => 'en_ligne',
                    'validation_billets' => 'en_attente',
                    'id_compagnie' => $idCompagnie,
                    'num_gare' => $numeroGare,
                    'delait_reservation' => now()->addMinutes(30),
                ]);

                if ($jourVoyage === $aujourdhui) {
                    Car::where('id_car', $idCarProgrammer)
                        ->update(['nbr_place_reserve' => DB::raw('nbr_place_reserve + '.$nbPassagers)]);
                }

                $emailEnvoye = null;
                if ($emailClient !== '') {
                    $emailEnvoye = $this->envoyerEmailConfirmation($billet, $nomClient, $idCompagnie);
                }

                return [
                    'ok' => true,
                    'numeroBillets' => $numeroBillets,
                    'email_envoye' => $emailEnvoye,
                ];
            });
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'message' => $this->messagePourErreur($e->getMessage())];
        }
    }

    // Même format que App\Services\BilletService::genererNumeroBilletUnique() — cohérence
    // du format d'un numéro de billet, qu'il soit vendu au guichet ou en ligne. Le legacy
    // faisait confiance au numéro affiché côté formulaire (posté tel quel, jamais
    // revérifié) : corrigé ici, jamais généré ni fait confiance côté client.
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

    private function envoyerEmailConfirmation(Billet $billet, string $nomClient, int $idCompagnie): bool
    {
        try {
            $nomCompagnie = Compagnie::where('id_compagnie', $idCompagnie)->value('nom_compagnie') ?? 'Billetterie';

            Mail::to($billet->client->emailClient)->send(new ReservationConfirmee($billet, $nomClient, $nomCompagnie));

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function messagePourErreur(string $code): string
    {
        [$type, $valeur] = array_pad(explode(':', $code, 2), 2, null);

        return match ($type) {
            'AUCUN_CAR_PROGRAMME' => 'Aucun car programmé pour cette heure et ce trajet.',
            'CAR_INTROUVABLE' => 'Car introuvable.',
            'PLACES_INSUFFISANTES' => "Places insuffisantes : $valeur restantes.",
            'PLACE_MINIMALE_NON_DEFINIE' => 'Erreur : nombre de places minimales non défini pour cette compagnie.',
            'PLACES_INSUFFISANTES_DEMAIN' => "Places insuffisantes pour demain : $valeur restantes.",
            default => 'Erreur lors de la réservation.',
        };
    }
}
