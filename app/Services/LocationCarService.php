<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CaisseUtilisateur;
use App\Models\LocationCar;
use App\Models\Utilisateur;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Location_car.php. Chaque méthode mutante retourne
 * ['ok' => bool, 'type' => 'success'|'danger'|'warning', 'message' => string] — même
 * convention que DepenseService/CaisseUtilisateurService.
 */
class LocationCarService
{
    /**
     * Cars disponibles pour une gare de départ et une période données.
     * - $limiterAGare (chef d'escale, location pour aujourd'hui) : uniquement les cars
     *   normalement affectés à un trajet au départ de cette gare (présence physique).
     * - Sinon (Admin, ou date future) : tous les cars de la compagnie.
     * Dans tous les cas, un car déjà réservé (en_attente/valide) sur une période qui
     * chevauche celle demandée est exclu.
     */
    public function carsDisponibles(int $idCompagnie, int $idAgenceDepart, string $dateDepart, string $dateRetour, bool $limiterAGare): Collection
    {
        return Car::query()
            ->where('car.id_compagnie', $idCompagnie)
            ->when($limiterAGare, fn ($q) => $q->whereIn('car.id_car', function ($sub) use ($idAgenceDepart) {
                $sub->select('lct.id_car')->from('liaison_car_trajet as lct')
                    ->join('programmer as p', 'p.idProgrammer', '=', 'lct.id_trajets')
                    ->where('p.idDepart', $idAgenceDepart);
            }))
            ->whereNotIn('car.id_car', function ($sub) use ($dateDepart, $dateRetour) {
                $sub->select('lc.id_car')->from('location_car as lc')
                    ->whereIn('lc.statut', ['en_attente', 'valide'])
                    ->where('lc.date_depart', '<=', $dateRetour)
                    ->where('lc.date_retour_prevu', '>=', $dateDepart);
            })
            ->orderBy('car.numero_car')
            ->get(['car.id_car', 'car.numero_car', 'car.matriculle']);
    }

    public function saveLocation(Utilisateur $user, array $data): array
    {
        $droit = $user->droit;

        if ($droit === 'chef_d_escale') {
            $idAgenceDepart = $user->id_agence;
        } else {
            $idAgenceDepart = $data['id_agence_depart'] ?? null;
            if (empty($idAgenceDepart)) {
                return ['ok' => false, 'type' => 'danger', 'message' => 'Veuillez choisir la gare de départ.'];
            }
        }

        $destination = trim((string) ($data['destination'] ?? ''));
        $idCar = $data['id_car'] ?? null;
        $nomClient = trim((string) ($data['nom_client'] ?? ''));
        $prenomClient = trim((string) ($data['prenom_client'] ?? ''));
        $telephoneClient = trim((string) ($data['telephone_client'] ?? ''));
        $dateDepart = $data['date_depart'] ?? null;
        $dateRetourPrevu = $data['date_retour_prevu'] ?? null;
        $fraisLocation = $data['frais_location'] ?? null;

        if ($destination === '') {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Veuillez indiquer la destination.'];
        }
        if (empty($idCar)) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Veuillez choisir un car.'];
        }
        if ($nomClient === '' || $prenomClient === '' || $telephoneClient === '') {
            return ['ok' => false, 'type' => 'danger', 'message' => "Les coordonnées du client (nom, prénom, téléphone) sont obligatoires."];
        }
        if (empty($dateDepart) || empty($dateRetourPrevu)) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Veuillez indiquer les dates de départ et de retour prévu.'];
        }
        if ($dateRetourPrevu < $dateDepart) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'La date de retour prévu ne peut pas être avant la date de départ.'];
        }
        if (! is_numeric($fraisLocation) || (float) $fraisLocation <= 0) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Le frais de location doit être un nombre positif.'];
        }

        // IDOR guard : un Admin ne peut louer que les cars de sa propre compagnie.
        $carAppartientCompagnie = Car::where('id_car', $idCar)->where('id_compagnie', $user->id_compagnie)->exists();
        if (! $carAppartientCompagnie) {
            return ['ok' => false, 'type' => 'danger', 'message' => "Ce car n'appartient pas à votre compagnie."];
        }

        $aujourdhui = now()->toDateString();
        $limiterAGare = $droit === 'chef_d_escale' && $dateDepart === $aujourdhui;

        // Re-vérification serveur de la disponibilité : ne jamais faire confiance à la
        // liste proposée côté client (formulaire trafiqué, race condition...).
        $disponibles = $this->carsDisponibles($user->id_compagnie, $idAgenceDepart, $dateDepart, $dateRetourPrevu, $limiterAGare);
        if (! $disponibles->contains('id_car', (int) $idCar)) {
            return ['ok' => false, 'type' => 'danger', 'message' => "Ce car n'est plus disponible sur la période demandée. Veuillez en choisir un autre."];
        }

        try {
            $resultat = DB::transaction(function () use (
                $user, $droit, $idAgenceDepart, $destination, $idCar, $nomClient, $prenomClient,
                $telephoneClient, $dateDepart, $dateRetourPrevu, $fraisLocation
            ) {
                // Verrouille ce car précis : sans ça, deux locations pour LE MEME car sur des
                // périodes qui se chevauchent, soumises à quelques millisecondes d'intervalle,
                // peuvent toutes les deux le lire comme disponible avant qu'aucune n'écrive.
                DB::table('car')->where('id_car', $idCar)->lockForUpdate()->first();

                $conflit = DB::table('location_car')
                    ->where('id_car', $idCar)
                    ->whereIn('statut', ['en_attente', 'valide'])
                    ->where('date_depart', '<=', $dateRetourPrevu)
                    ->where('date_retour_prevu', '>=', $dateDepart)
                    ->exists();
                if ($conflit) {
                    return ['ok' => false, 'type' => 'danger', 'message' => "Ce car vient d'être réservé sur cette période par quelqu'un d'autre. Veuillez en choisir un autre."];
                }

                [$idCaisse, $idCaisseUser, $erreurCaisse] = $this->resoudreCaisse($user, $droit, $idAgenceDepart);
                if ($erreurCaisse !== null) {
                    return ['ok' => false, 'type' => 'danger', 'message' => $erreurCaisse];
                }

                $statut = in_array($droit, ['Admin', 'super_admin'], true) ? 'valide' : 'en_attente';

                $location = LocationCar::create([
                    'id_compagnie' => $user->id_compagnie,
                    'id_agence_depart' => $idAgenceDepart,
                    'destination' => $destination,
                    'id_car' => $idCar,
                    'id_caisse' => $idCaisse,
                    'id_caisse_user' => $idCaisseUser,
                    'nom_client' => $nomClient,
                    'prenom_client' => $prenomClient,
                    'telephone_client' => $telephoneClient,
                    'date_depart' => $dateDepart,
                    'date_retour_prevu' => $dateRetourPrevu,
                    'frais_location' => $fraisLocation,
                    'statut' => $statut,
                    'id_utilisateur' => $user->idUser,
                ]);

                if ($statut === 'valide') {
                    $this->crediterCaisse($idCaisse, $idCaisseUser, (float) $fraisLocation);

                    return ['ok' => true, 'type' => 'success', 'message' => 'Location enregistrée avec succès et créditée à la caisse.'];
                }

                return ['ok' => true, 'type' => 'success', 'message' => "Location enregistrée avec succès. Elle est en attente de validation par l'administrateur."];
            });
        } catch (\Throwable $e) {
            return ['ok' => false, 'type' => 'danger', 'message' => "Erreur lors de l'enregistrement de la location."];
        }

        return $resultat;
    }

    /**
     * Même logique à deux niveaux que DepenseService::saveDepense() : un chef d'escale
     * crédite toujours SA PROPRE caisse individuelle du jour ; un Admin utilise la caisse
     * de gare (ancien système) si elle est ouverte, sinon une caisse individuelle ouverte
     * pour cette gare aujourd'hui.
     *
     * @return array{0: ?int, 1: ?int, 2: ?string}
     */
    private function resoudreCaisse(Utilisateur $user, string $droit, int $idAgenceDepart): array
    {
        if ($droit === 'chef_d_escale') {
            $caisseUser = CaisseUtilisateur::where('id_utilisateur', $user->idUser)
                ->whereDate('date_service', now()->toDateString())->where('statut', 'ouverte')->first();
            if (! $caisseUser) {
                return [null, null, 'Vous devez avoir votre propre caisse ouverte pour enregistrer une location.'];
            }

            return [null, $caisseUser->id_caisse_user, null];
        }

        $caisse = DB::table('caisse')->where('id_agence', $idAgenceDepart)->where('status_caisse', 1)->first();
        if ($caisse) {
            return [$caisse->id_caisse, null, null];
        }

        $caisseUser = CaisseUtilisateur::where('id_agence', $idAgenceDepart)
            ->whereDate('date_service', now()->toDateString())->where('statut', 'ouverte')->first();
        if ($caisseUser) {
            return [null, $caisseUser->id_caisse_user, null];
        }

        return [null, null, "Aucune caisse ouverte pour cette gare : impossible d'enregistrer la location."];
    }

    private function crediterCaisse(?int $idCaisse, ?int $idCaisseUser, float $montant): void
    {
        if ($idCaisse) {
            DB::table('caisse')->where('id_caisse', $idCaisse)->update(['montant_location' => DB::raw('montant_location + '.$montant)]);
        } elseif ($idCaisseUser) {
            CaisseUtilisateur::where('id_caisse_user', $idCaisseUser)->update(['montant_location' => DB::raw('montant_location + '.$montant)]);
        }
    }

    /**
     * Locations visibles selon le rôle : le chef d'escale ne voit que celles de sa propre
     * gare de départ, l'Admin/PDG voit tout.
     */
    public function getLocations(Utilisateur $user): Collection
    {
        return LocationCar::query()
            ->leftJoin('agence as a', 'location_car.id_agence_depart', '=', 'a.idAgence')
            ->leftJoin('car as c', 'location_car.id_car', '=', 'c.id_car')
            ->leftJoin('utilisateur as u', 'location_car.id_utilisateur', '=', 'u.idUser')
            ->where('location_car.id_compagnie', $user->id_compagnie)
            ->when($user->droit === 'chef_d_escale', fn ($q) => $q->where('location_car.id_agence_depart', $user->id_agence))
            ->orderByDesc('location_car.date_depart')->orderByDesc('location_car.id_location')
            ->get(['location_car.*', 'a.localite', 'a.numeroGare', 'c.numero_car', 'c.matriculle', 'u.utilisateurs as agent']);
    }

    /**
     * Une seule location, pour la facture. Filtrée par compagnie (IDOR) et, pour un chef
     * d'escale, par sa propre gare de départ (comme getLocations()).
     */
    public function getById(int $id, Utilisateur $user): ?object
    {
        return LocationCar::query()
            ->leftJoin('agence as a', 'location_car.id_agence_depart', '=', 'a.idAgence')
            ->leftJoin('car as c', 'location_car.id_car', '=', 'c.id_car')
            ->leftJoin('utilisateur as u', 'location_car.id_utilisateur', '=', 'u.idUser')
            ->leftJoin('utilisateur as v', 'location_car.id_valide_par', '=', 'v.idUser')
            ->where('location_car.id_location', $id)
            ->where('location_car.id_compagnie', $user->id_compagnie)
            ->when($user->droit === 'chef_d_escale', fn ($q) => $q->where('location_car.id_agence_depart', $user->id_agence))
            ->first([
                'location_car.*', 'a.localite', 'a.numeroGare', 'c.numero_car', 'c.matriculle',
                'u.utilisateurs as agent', 'u.droit as agent_droit', 'v.utilisateurs as valide_par_nom',
            ]);
    }

    public function validerLocation(int $id, Utilisateur $user): array
    {
        $location = LocationCar::where('id_location', $id)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $location) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Location introuvable.'];
        }
        if ($location->statut !== 'en_attente') {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette location a déjà été traitée (validée ou rejetée).'];
        }

        // Compare-and-swap : évite qu'une validation et un rejet concurrents créditent
        // tous les deux la caisse pour la même location.
        $affectee = LocationCar::where('id_location', $id)->where('statut', 'en_attente')
            ->update(['statut' => 'valide', 'id_valide_par' => $user->idUser]);
        if (! $affectee) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette location a déjà été traitée entre-temps par quelqu\'un d\'autre.'];
        }

        $this->crediterCaisse($location->id_caisse, $location->id_caisse_user, (float) $location->frais_location);

        return ['ok' => true, 'type' => 'success', 'message' => 'Location validée avec succès et créditée à la caisse.'];
    }

    public function rejeterLocation(int $id, Utilisateur $user): array
    {
        $location = LocationCar::where('id_location', $id)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $location) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Location introuvable.'];
        }
        if ($location->statut !== 'en_attente') {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette location a déjà été traitée.'];
        }

        $affectee = LocationCar::where('id_location', $id)->where('statut', 'en_attente')->update(['statut' => 'rejete']);
        if (! $affectee) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Cette location a déjà été traitée entre-temps par quelqu\'un d\'autre.'];
        }

        return ['ok' => true, 'type' => 'success', 'message' => 'Location rejetée avec succès.'];
    }
}
