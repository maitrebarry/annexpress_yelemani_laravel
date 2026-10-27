<?php

namespace App\Services;

use App\Models\Agence;
use App\Models\Car;
use App\Models\Programme;
use App\Models\ProgrammationVoyage;
use Illuminate\Support\Facades\DB;

/**
 * Logique d'affectation d'un car à un créneau, extraite de
 * ProgrammationVoyageController (qui restait l'unique appelant) pour être réutilisable
 * depuis BilletService::creerReservation() — vente d'un billet du jour sans
 * programmation préalable (cf. Projets_licence/app/models/Add_billet.php::saveBillets()).
 * Comportement inchangé pour les deux appels déjà existants du contrôleur.
 */
class ProgrammationVoyageService
{
    // Cars physiquement présents à une gare (status_car = cette localité) : utilisés par
    // BilletService::creerReservation()/ajaxEtatCreneau() pour proposer un car à
    // programmer à la volée quand aucune programmation n'existe encore pour le créneau.
    public function carsDisponiblesGare(string $localite, int $idCompagnie)
    {
        return Car::where('status_car', $localite)->where('id_compagnie', $idCompagnie)
            ->orderBy('numero_car')->get(['id_car', 'numero_car', 'matriculle', 'nbr_place', 'nbr_place_reserve']);
    }

    // Places déjà vendues (billets) pour un créneau exact — inclut les billets vers une
    // escale du trajet, enregistrés avec le nom de l'escale comme destinationId.
    public function countPlacesVendues($idHoraire, $idDestination, $localiteUser, $jourVoyage, $idCompagnie, $idAgence = null): int
    {
        $destinations = ProgrammationVoyage::destinationsPourCreneau($idHoraire, $idDestination, $localiteUser, $idCompagnie, $idAgence);

        $query = DB::table('billets')
            ->where('jourVoyage', $jourVoyage)->where('Heur_departs', $idHoraire)
            ->where('departId', $localiteUser)->where('id_compagnie', $idCompagnie)
            ->whereIn('destinationId', $destinations);

        if ($idAgence) {
            $numeroGare = Agence::where('idAgence', $idAgence)->value('numeroGare');
            if ($numeroGare !== null) {
                $query->where('num_gare', $numeroGare);
            }
        }

        return (int) $query->sum('nombrePassages');
    }

    // $localiteFallback/$idAgenceFallback ne servent que pour un chef d'escale (pas de
    // $idDepart/$idAgenceDepart fournis) : sa propre gare de session fait alors foi.
    //
    // Validation de trajet : depart+destination seulement (pas d'heure exacte), pour
    // supporter une heure saisie librement ("Heure non listée" côté vente, cf.
    // BilletService) en plus des heures catalguées par l'écran de programmation.
    public function insertProgrammation($idCompagnie, ?string $localiteFallback, ?int $idAgenceFallback, $idCare, $idHoraire, $idDestination, $dateEnregistre, $idDepart, $idAgenceDepart, $idAgenceDestination): bool
    {
        $localiteUser = $idDepart ?: $localiteFallback;

        if ($idDestination == $localiteUser) {
            return false;
        }

        if ($idAgenceDepart) {
            $agenceDepart = Agence::where('idAgence', $idAgenceDepart)->where('id_compagnie', $idCompagnie)->first();
            if (! $agenceDepart || $agenceDepart->localite !== $localiteUser) {
                return false;
            }
            $idAgence = $agenceDepart->idAgence;
        } else {
            $idAgence = $idAgenceFallback;
        }
        if (! $idAgence) {
            return false;
        }

        if (! $idAgenceDestination) {
            return false;
        }
        $agenceDestination = Agence::where('idAgence', $idAgenceDestination)->where('id_compagnie', $idCompagnie)->first();
        if (! $agenceDestination || $agenceDestination->localite !== $idDestination) {
            return false;
        }

        $trajetValide = Programme::where('idDepart', $idAgence)->where('idDestination', $idAgenceDestination)
            ->where('id_compagnie', $idCompagnie)->exists();
        if (! $trajetValide) {
            return false;
        }

        // Verrouillé (FOR UPDATE) le temps de la transaction : sans ça, deux programmations du
        // même car soumises à quelques millisecondes d'intervalle pourraient toutes deux lire
        // "disponible" avant qu'aucune n'écrive, affectant un seul car à deux trajets à la fois.
        return DB::transaction(function () use ($idCare, $idCompagnie, $idHoraire, $dateEnregistre, $idDepart, $localiteUser, $idDestination, $idAgence, $idAgenceDestination) {
            $car = Car::where('id_car', $idCare)->where('id_compagnie', $idCompagnie)->lockForUpdate()->first();
            if (! $car) {
                return false;
            }
            if ($car->status_car !== null && str_starts_with($car->status_car, 'En_transit_')) {
                return false;
            }
            // chef_d_escale (pas d'id_depart fourni) : le car doit être physiquement dans sa
            // gare. Admin (id_depart fourni) : peut réaffecter un car présent ailleurs.
            if ($idDepart === null && $car->status_car !== $localiteUser) {
                return false;
            }

            $doublon = ProgrammationVoyage::where('id_car_programmer', $idCare)->where('id_horaire', $idHoraire)
                ->where('date_enregistre', $dateEnregistre)->where('id_compagnie', $idCompagnie)->exists();
            if ($doublon) {
                return false;
            }

            ProgrammationVoyage::create([
                'id_car_programmer' => $idCare,
                'id_horaire' => $idHoraire,
                'id_trajet' => $idDestination,
                'localite_user' => $localiteUser,
                'id_agence' => $idAgence,
                'id_agence_destination' => $idAgenceDestination,
                'date_enregistre' => $dateEnregistre,
                'id_compagnie' => $idCompagnie,
            ]);

            // Recalculer (jamais remettre à 0 aveuglément) le nombre de places déjà réservées
            // sur ce créneau : une reprogrammation ne doit jamais faire "disparaître" des
            // billets déjà vendus.
            $placesDejaVendues = $this->countPlacesVendues($idHoraire, $idDestination, $localiteUser, $dateEnregistre, $idCompagnie, $idAgence);
            $car->update(['nbr_place_reserve' => $placesDejaVendues]);

            return true;
        });
    }

    // Annule une programmation qui n'a encore reçu AUCUNE vente (places_reserve = 0),
    // en remettant le car "physiquement à sa gare". Utilisée par
    // ProgrammationVoyageController::desactiver() (bouton "Désactiver" côté UI) et par
    // BilletService (anti-course : la programmation perdante créée lors d'une vente
    // concurrente doit disparaître proprement).
    public function desactiverSansVente(int $idProgrammation, int $idCompagnie): bool
    {
        return DB::transaction(function () use ($idProgrammation, $idCompagnie) {
            $prog = ProgrammationVoyage::where('id_programmation', $idProgrammation)
                ->where('id_compagnie', $idCompagnie)
                ->first();
            if (! $prog || $prog->statut !== 'active') {
                return false;
            }

            $car = Car::where('id_car', $prog->id_car_programmer)
                ->where('id_compagnie', $idCompagnie)
                ->lockForUpdate()
                ->first();
            if (! $car || (int) $car->nbr_place_reserve > 0) {
                return false;
            }

            $misAJour = ProgrammationVoyage::where('id_programmation', $prog->id_programmation)
                ->where('id_compagnie', $idCompagnie)
                ->where('statut', 'active')
                ->update(['statut' => 'annulee']);
            if ($misAJour === 0) {
                return false;
            }

            $car->update(['status_car' => $prog->localite_user, 'nbr_place_reserve' => 0]);

            return true;
        });
    }
}
