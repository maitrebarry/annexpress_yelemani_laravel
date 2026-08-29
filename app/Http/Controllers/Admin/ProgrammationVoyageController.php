<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Car;
use App\Models\LiaisonCarTrajet;
use App\Models\Programme;
use App\Models\ProgrammationVoyage;
use App\Models\Utilisateur;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Programmation_voyages.php +
 * app/models/Programmation_voyage.php — écran "Trajets programmés" du menu
 * G-programme (affectation quotidienne d'un car à un créneau, suivi transit/arrivée).
 *
 * Hors scope pour l'instant (voir le plan) : le bouton "Transférer les passagers"
 * (dépend du futur module Transferts), "Désactiver" (mort dans le legacy aussi),
 * decollerCar()/getEtatFlotte() (jamais appelés depuis cet écran côté legacy).
 */
class ProgrammationVoyageController extends Controller
{
    public function dashboard(): View
    {
        $user = Auth::guard('staff')->user();
        $idCompagnie = $user->id_compagnie;
        $ville = $user->agence?->localite;

        $listeCarDisponible = $this->carsDisponibles($user, $idCompagnie, $ville);
        $tousLesTrajets = $this->tousLesTrajets($user, $idCompagnie, $ville);

        $localiteFiltre = $user->droit === 'chef_d_escale' ? $ville : null;
        $aujourdhui = now()->toDateString();
        $derniereDate = ProgrammationVoyage::where('id_compagnie', $idCompagnie)
            ->where('date_enregistre', '<', $aujourdhui)
            ->when($localiteFiltre, fn ($q) => $q->where('localite_user', $localiteFiltre))
            ->max('date_enregistre');

        $programmationVeille = $derniereDate
            ? ProgrammationVoyage::where('id_compagnie', $idCompagnie)
                ->where('date_enregistre', $derniereDate)
                ->when($localiteFiltre, fn ($q) => $q->where('localite_user', $localiteFiltre))
                ->get()
                ->keyBy('id_car_programmer')
                ->map(fn ($p) => ['id_horaire' => $p->id_horaire, 'id_trajet' => $p->id_trajet])
            : collect();

        return view('admin.programmation-voyage.dashboard', [
            'listeCarDisponible' => $listeCarDisponible,
            'tousLesTrajets' => $tousLesTrajets,
            'derniereDate' => $derniereDate,
            'programmationVeille' => $programmationVeille,
            'carsEnTransit' => $this->carsEnTransit($user, $idCompagnie, $ville),
            'carsBloques' => $this->carsBloques($user, $idCompagnie),
        ]);
    }

    // Le formulaire permet de cocher plusieurs cars à la fois : chacun reçoit la
    // destination/horaire choisis sur sa propre ligne.
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->estLectureSeule()) {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.programmation-voyage.dashboard');
        }

        $selectCar = (array) $request->input('select_car', []);
        $idCare = (array) $request->input('id_care', []);
        $idHoraire = (array) $request->input('id_horaire', []);
        $idDestination = (array) $request->input('id_destination', []);
        $idDepart = (array) $request->input('id_depart', []);
        $idDepartAgence = (array) $request->input('id_depart_agence', []);
        $idDestinationAgence = (array) $request->input('id_destination_agence', []);

        $dateEnregistre = now()->toDateString();
        $errors = [];
        $reussies = 0;

        foreach ($selectCar as $index) {
            $destination = $idDestination[$index] ?? null;

            // Une ligne cochée mais jamais renseignée (aucune destination choisie) n'est pas
            // une erreur de saisie, juste un car resté sélectionné sans intention réelle.
            if (empty($destination)) {
                continue;
            }

            $car = $idCare[$index] ?? null;
            $horaire = $idHoraire[$index] ?? null;
            $agenceDestination = $idDestinationAgence[$index] ?? null;
            $depart = $idDepart[$index] ?? null;
            $agenceDepart = $idDepartAgence[$index] ?? null;

            if (! $car || ! $horaire || ! $agenceDestination) {
                $errors[] = 'Veuillez remplir tous les champs pour la ligne choisie.';

                continue;
            }
            if ($user->droit === 'Admin' && empty($depart)) {
                $errors[] = 'Veuillez choisir une destination pour renseigner le départ de la ligne choisie.';

                continue;
            }

            if ($this->insertProgrammation($user->id_compagnie, $user->agence?->localite, $user->id_agence, $car, $horaire, $destination, $dateEnregistre, $depart, $agenceDepart, $agenceDestination)) {
                Car::where('id_car', $car)->update(['status_car' => 'En_transit_'.$destination]);
                $reussies++;
            } else {
                $errors[] = "Erreur lors de l'insertion de la programmation pour le car $car.";
            }
        }

        if (! empty($errors)) {
            Flash::set(implode(' ', $errors), 'danger');
        } elseif ($reussies > 0) {
            Flash::set('Programmation générée avec succès !', 'success');
        } else {
            Flash::set('Veuillez cocher au moins un car et choisir sa destination.', 'danger');
        }

        return redirect()->route('admin.programmation-voyage.dashboard');
    }

    public function validerArrivee(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        $idCar = $request->input('id_car_arrivee');

        if ($user->estLectureSeule() || ! $idCar) {
            return redirect()->route('admin.programmation-voyage.dashboard');
        }

        $car = Car::where('id_car', $idCar)->where('id_compagnie', $user->id_compagnie)->first();
        $status = $car?->status_car;

        if (! $status || ! str_starts_with($status, 'En_transit_')) {
            Flash::set("Erreur lors de la validation de l'arrivée.", 'danger');

            return redirect()->route('admin.programmation-voyage.dashboard');
        }

        $destination = substr($status, 11);
        $prog = $this->programmationActivePourCar((int) $idCar, $destination);

        // Défense en profondeur : même si carsEnTransit() ne liste plus ce car normalement,
        // on revalide ici qu'il a réellement décollé avant de le "faire arriver".
        if (! $prog || empty($prog->decolle_le)) {
            Flash::set("Ce bus n'a pas encore décollé : impossible de valider son arrivée.", 'warning');

            return redirect()->route('admin.programmation-voyage.dashboard');
        }

        $car->update(['status_car' => $destination]);
        Flash::set("L'arrivée du car a été validée avec succès !", 'success');

        return redirect()->route('admin.programmation-voyage.dashboard');
    }

    public function debloquerArrive(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        if (! in_array($user->droit, ['Admin', 'super_admin'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.programmation-voyage.dashboard');
        }

        $idProgrammation = $request->input('id_programmation_bloque');
        $prog = ProgrammationVoyage::where('id_programmation', $idProgrammation)
            ->where('id_compagnie', $user->id_compagnie)->where('statut', 'active')->first();

        if (! $prog) {
            Flash::set('Programmation introuvable.', 'danger');

            return redirect()->route('admin.programmation-voyage.dashboard');
        }

        $car = Car::where('id_car', $prog->id_car_programmer)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $car || $car->status_car !== 'En_transit_'.$prog->id_trajet) {
            Flash::set("Ce car n'est plus dans l'état bloqué attendu.", 'warning');

            return redirect()->route('admin.programmation-voyage.dashboard');
        }

        DB::transaction(function () use ($prog, $car) {
            $prog->update(['decolle_le' => now(), 'decolle_par' => Auth::guard('staff')->id()]);
            $car->update(['status_car' => $prog->id_trajet]);
        });

        Flash::set('Car débloqué : marqué arrivé à destination.', 'success');

        return redirect()->route('admin.programmation-voyage.dashboard');
    }

    public function debloquerJamaisParti(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        if (! in_array($user->droit, ['Admin', 'super_admin'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.programmation-voyage.dashboard');
        }

        $idProgrammation = $request->input('id_programmation_bloque');
        $prog = ProgrammationVoyage::where('id_programmation', $idProgrammation)
            ->where('id_compagnie', $user->id_compagnie)->where('statut', 'active')->first();

        if (! $prog) {
            Flash::set('Programmation introuvable.', 'danger');

            return redirect()->route('admin.programmation-voyage.dashboard');
        }
        if (! empty($prog->decolle_le)) {
            Flash::set('Ce bus a déjà décollé : impossible de le remettre à sa gare de départ.', 'warning');

            return redirect()->route('admin.programmation-voyage.dashboard');
        }

        $car = Car::where('id_car', $prog->id_car_programmer)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $car || $car->status_car !== 'En_transit_'.$prog->id_trajet) {
            Flash::set("Ce car n'est plus dans l'état bloqué attendu.", 'warning');

            return redirect()->route('admin.programmation-voyage.dashboard');
        }

        DB::transaction(function () use ($prog, $car) {
            $prog->update(['statut' => 'annulee']);
            $car->update(['status_car' => $prog->localite_user, 'nbr_place_reserve' => 0]);
        });

        Flash::set('Car débloqué : remis disponible à sa gare de départ.', 'success');

        return redirect()->route('admin.programmation-voyage.dashboard');
    }

    public function listeJournaliere(): View
    {
        $user = Auth::guard('staff')->user();

        $liste = ProgrammationVoyage::query()
            ->join('car', 'programmation_voyage.id_car_programmer', '=', 'car.id_car')
            ->where('programmation_voyage.id_compagnie', $user->id_compagnie)
            ->where('programmation_voyage.statut', 'active')
            ->whereDate('programmation_voyage.date_enregistre', now()->toDateString())
            ->when($user->droit !== 'Admin', fn ($q) => $q->where('programmation_voyage.localite_user', $user->agence?->localite))
            ->orderBy('programmation_voyage.id_horaire')
            ->get([
                'programmation_voyage.*',
                'car.numero_car', 'car.nbr_place', 'car.nbr_place_reserve',
                DB::raw('(car.nbr_place - car.nbr_place_reserve) AS place_disponible'),
            ]);

        $liste->each(function ($p) {
            $p->numeroGareDestination = $this->numeroGareDestinationPour($p);
        });

        return view('admin.programmation-voyage.liste-journaliere', ['liste' => $liste]);
    }

    public function edit(int $idProgrammation): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['Admin', 'chef_d_escale'], true)) {
            Flash::set('Accès refusé ou session invalide.', 'danger');

            return redirect()->route('admin.programmation-voyage.dashboard');
        }

        $programmation = ProgrammationVoyage::query()
            ->join('car', 'programmation_voyage.id_car_programmer', '=', 'car.id_car')
            ->where('programmation_voyage.id_programmation', $idProgrammation)
            ->where('programmation_voyage.id_compagnie', $user->id_compagnie)
            ->first(['programmation_voyage.*', 'car.numero_car', 'car.nbr_place', 'car.nbr_place_reserve']);

        if (! $programmation) {
            Flash::set('Programmation introuvable !', 'danger');

            return redirect()->route('admin.programmation-voyage.liste-journaliere');
        }

        return view('admin.programmation-voyage.edit', [
            'programmation' => $programmation,
            'destinations' => $this->destinationsPourCar($programmation->id_car_programmer, $programmation->localite_user, $user->id_compagnie),
        ]);
    }

    public function update(Request $request, int $idProgrammation): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $idHoraire = $request->input('id_horaire');
        $idDestination = $request->input('id_destination');
        $action = $request->input('action_reservations');
        $idCarRemplacement = $request->input('id_car_remplacement') ?: null;

        if (! $idHoraire || ! $idDestination) {
            Flash::set('Veuillez remplir tous les champs.', 'danger');

            return redirect()->route('admin.programmation-voyage.liste-journaliere');
        }

        $appartientALaCompagnie = ProgrammationVoyage::where('id_programmation', $idProgrammation)
            ->where('id_compagnie', $user->id_compagnie)
            ->exists();

        if (! $appartientALaCompagnie) {
            Flash::set('Programmation introuvable.', 'danger');

            return redirect()->route('admin.programmation-voyage.liste-journaliere');
        }

        $resultat = $this->updateProgrammation($idProgrammation, $idHoraire, $idDestination, $action, $idCarRemplacement);

        if (is_array($resultat) && ! empty($resultat['needs_choice'])) {
            $programmation = ProgrammationVoyage::query()
                ->join('car', 'programmation_voyage.id_car_programmer', '=', 'car.id_car')
                ->where('programmation_voyage.id_programmation', $idProgrammation)
                ->first(['programmation_voyage.*', 'car.numero_car', 'car.nbr_place', 'car.nbr_place_reserve']);

            return view('admin.programmation-voyage.edit', [
                'programmation' => $programmation,
                'destinations' => $this->destinationsPourCar($programmation->id_car_programmer, $programmation->localite_user, $user->id_compagnie),
                'besoinChoix' => $resultat,
                'carsRemplacement' => Car::where('id_compagnie', $user->id_compagnie)
                    ->where('id_car', '!=', $programmation->id_car_programmer)
                    ->where(fn ($q) => $q->whereNull('status_car')->orWhere('status_car', 'not like', 'En\\_transit\\_%'))
                    ->orderBy('numero_car')->get(),
                'horaireSoumis' => $idHoraire,
                'destinationSoumise' => $idDestination,
            ]);
        }

        if (is_array($resultat) && ! empty($resultat['error'])) {
            $messages = [
                'introuvable' => 'Programmation introuvable.',
                'horaire_invalide' => "Cette heure n'existe pas pour ce trajet (départ/destination).",
                'car_remplacement_requis' => "Veuillez choisir un car de remplacement pour l'ancien créneau.",
                'car_remplacement_invalide' => "Ce car de remplacement n'est pas disponible.",
            ];
            Flash::set($messages[$resultat['error']] ?? 'Erreur lors de la modification.', 'danger');

            return redirect()->route('admin.programmation-voyage.edit', $idProgrammation);
        }

        Flash::set(
            $resultat ? 'La programmation a été modifiée avec succès !' : 'Erreur lors de la modification de la programmation.',
            $resultat ? 'success' : 'danger'
        );

        return redirect()->route('admin.programmation-voyage.liste-journaliere');
    }

    // ---------------------------------------------------------------------
    // Helpers privés (port des méthodes du modèle legacy Programmation_voyage)
    // ---------------------------------------------------------------------

    private function carsDisponibles(Utilisateur $user, ?int $idCompagnie, ?string $ville)
    {
        // Un car ne peut être programmé ici que s'il a déjà des trajets affectés via
        // l'écran "Cars" (liaison_car_trajet) — sinon on ne saurait pas quelles
        // destinations lui proposer.
        $idsAvecTrajet = LiaisonCarTrajet::where('id_compagnie', $idCompagnie)->distinct()->pluck('id_car');

        if ($user->droit === 'Admin') {
            // L'Admin fait la toute première affectation d'un car (status_car encore NULL).
            return Car::whereIn('id_car', $idsAvecTrajet)->where('id_compagnie', $idCompagnie)
                ->whereNull('status_car')->orderBy('numero_car')->get();
        }
        if ($user->droit === 'chef_d_escale' && $ville) {
            return Car::whereIn('id_car', $idsAvecTrajet)->where('id_compagnie', $idCompagnie)
                ->where('status_car', $ville)->orderBy('numero_car')->get();
        }

        return collect();
    }

    private function tousLesTrajets(Utilisateur $user, ?int $idCompagnie, ?string $ville)
    {
        return Programme::query()
            ->select('programmer.idDepart', 'programmer.idDestination', 'programmer.heureDepart', 'a1.localite as departLocalite', 'a1.numeroGare as numeroGareDepart', 'a2.localite as destinationLocalite', 'a2.numeroGare as numeroGareDestination')
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->where('programmer.id_compagnie', $idCompagnie)
            ->when($user->droit === 'chef_d_escale', fn ($q) => $q->where('a1.localite', $ville))
            ->orderBy('a1.localite')->orderBy('a2.localite')->orderBy('programmer.heureDepart')
            ->get();
    }

    private function carsEnTransit(Utilisateur $user, ?int $idCompagnie, ?string $ville)
    {
        $query = Car::where('status_car', 'like', 'En\\_transit\\_%');

        if ($user->droit === 'Admin') {
            $query->where('id_compagnie', $idCompagnie);
        } elseif ($user->droit === 'chef_d_escale' && $ville) {
            $query->where('status_car', 'En_transit_'.$ville)->where('id_compagnie', $idCompagnie);
        } elseif (! in_array($user->droit, ['super_admin'], true)) {
            return collect();
        }

        $cars = $query->orderBy('numero_car')->get();

        // "En transit" ne s'affiche ici que si le bus a réellement décollé (decolle_le
        // rempli sur sa dernière programmation active vers cette destination), pas dès la
        // simple programmation du voyage.
        return $cars->filter(function ($car) {
            $destination = substr($car->status_car, 11);
            $prog = $this->programmationActivePourCar($car->id_car, $destination);
            $car->numeroGareDestination = $prog->numeroGareDestination ?? null;

            return $prog && ! empty($prog->decolle_le);
        })->values();
    }

    // Réservé Admin/super_admin — correction d'état incohérent, pas un flux courant.
    private function carsBloques(Utilisateur $user, ?int $idCompagnie)
    {
        if (! in_array($user->droit, ['Admin', 'super_admin'], true)) {
            return collect();
        }

        return DB::table('car as c')
            ->join('programmation_voyage as pv', function ($join) {
                $join->on('pv.id_car_programmer', '=', 'c.id_car')
                    ->on('pv.id_trajet', '=', DB::raw('SUBSTRING(c.status_car, 12)'))
                    ->where('pv.statut', 'active');
            })
            ->where('c.status_car', 'like', 'En\\_transit\\_%')
            ->whereNull('pv.decolle_le')
            ->where('c.id_compagnie', $idCompagnie)
            ->orderBy('c.numero_car')
            ->get(['c.id_car', 'c.numero_car', 'c.status_car', 'pv.id_programmation', 'pv.date_enregistre', 'pv.id_horaire', 'pv.id_trajet as destination', 'pv.localite_user as origine']);
    }

    private function programmationActivePourCar(int $idCar, string $destination): ?object
    {
        return ProgrammationVoyage::query()
            ->leftJoin('agence as a', 'a.idAgence', '=', 'programmation_voyage.id_agence_destination')
            ->where('programmation_voyage.id_car_programmer', $idCar)
            ->where('programmation_voyage.id_trajet', $destination)
            ->where('programmation_voyage.statut', 'active')
            ->orderByDesc('programmation_voyage.date_enregistre')->orderByDesc('programmation_voyage.id_programmation')
            ->first(['programmation_voyage.id_programmation', 'programmation_voyage.date_enregistre', 'programmation_voyage.id_horaire', 'programmation_voyage.decolle_le', 'programmation_voyage.localite_user', 'a.numeroGare as numeroGareDestination']);
    }

    private function numeroGareDestinationPour(ProgrammationVoyage $p): ?string
    {
        if ($p->id_agence_destination) {
            return Agence::where('idAgence', $p->id_agence_destination)->value('numeroGare');
        }

        // Repli pour les lignes historiques pas encore rattachées à une gare précise.
        return DB::table('programmer')
            ->join('agence as aDest', 'programmer.idDestination', '=', 'aDest.idAgence')
            ->where('programmer.idDepart', $p->id_agence)
            ->where('programmer.heureDepart', $p->id_horaire)
            ->where('programmer.id_compagnie', $p->id_compagnie)
            ->where('aDest.localite', $p->id_trajet)
            ->value('aDest.numeroGare');
    }

    // Places déjà vendues (billets) pour un créneau exact — inclut les billets vers une
    // escale du trajet, enregistrés avec le nom de l'escale comme destinationId.
    private function countPlacesVendues($idHoraire, $idDestination, $localiteUser, $jourVoyage, $idCompagnie, $idAgence = null): int
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
    private function insertProgrammation($idCompagnie, ?string $localiteFallback, ?int $idAgenceFallback, $idCare, $idHoraire, $idDestination, $dateEnregistre, $idDepart, $idAgenceDepart, $idAgenceDestination): bool
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
            ->where('heureDepart', $idHoraire)->where('id_compagnie', $idCompagnie)->exists();
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

    private function destinationsPourCar($idCar, $localiteDepart, $idCompagnie)
    {
        return Programme::query()
            ->select('programmer.idProgrammer', 'programmer.heureDepart', 'a1.localite as departLocalite', 'a2.localite as destinationLocalite')
            ->join('liaison_car_trajet', 'liaison_car_trajet.id_trajets', '=', 'programmer.idProgrammer')
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->where('liaison_car_trajet.id_car', $idCar)
            ->where('a1.localite', $localiteDepart)
            ->where('liaison_car_trajet.id_compagnie', $idCompagnie)
            ->get();
    }

    private function updateProgrammation($idProgrammation, $idHoraire, $idDestination, $action = null, $idCarRemplacement = null)
    {
        $prog = ProgrammationVoyage::find($idProgrammation);
        if (! $prog) {
            return ['error' => 'introuvable'];
        }

        $idCompagnie = $prog->id_compagnie;
        $localiteUser = $prog->localite_user;
        $idAgence = $prog->id_agence;

        $trajetValide = $idAgence
            ? Programme::query()->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
                ->where('programmer.idDepart', $idAgence)->where('a2.localite', $idDestination)
                ->where('programmer.heureDepart', $idHoraire)->where('programmer.id_compagnie', $idCompagnie)
                ->first(['programmer.idDestination'])
            : Programme::query()->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
                ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
                ->where('a1.localite', $localiteUser)->where('a2.localite', $idDestination)
                ->where('programmer.heureDepart', $idHoraire)->where('programmer.id_compagnie', $idCompagnie)
                ->first(['programmer.idDestination']);

        if (! $trajetValide) {
            return ['error' => 'horaire_invalide'];
        }
        $nouvelIdAgenceDestination = $trajetValide->idDestination;

        $memeCreneau = $idHoraire === $prog->id_horaire && $idDestination === $prog->id_trajet;

        if (! $memeCreneau) {
            $dejaReserve = $this->countPlacesVendues($prog->id_horaire, $prog->id_trajet, $localiteUser, $prog->date_enregistre, $idCompagnie, $idAgence);

            if ($dejaReserve > 0) {
                $destinationChange = $idDestination !== $prog->id_trajet;

                if ($action === null || ($destinationChange && $action !== 'nouveau_car')) {
                    return ['needs_choice' => true, 'count' => $dejaReserve, 'destination_change' => $destinationChange];
                }

                if ($action === 'nouveau_car') {
                    if (! $idCarRemplacement) {
                        return ['error' => 'car_remplacement_requis'];
                    }
                    // $localiteUser/$idAgence sont toujours fournis ici (non-null), donc les
                    // repères "chef_d_escale" (agence de session) ne sont jamais utilisés.
                    $ok = $this->insertProgrammation(
                        $idCompagnie, null, null, $idCarRemplacement, $prog->id_horaire, $prog->id_trajet,
                        $prog->date_enregistre, $localiteUser, $idAgence, $prog->id_agence_destination
                    );
                    if (! $ok) {
                        return ['error' => 'car_remplacement_invalide'];
                    }
                } elseif ($action === 'suivre') {
                    $destinationsAncien = ProgrammationVoyage::destinationsPourCreneau($prog->id_horaire, $prog->id_trajet, $localiteUser, $idCompagnie, $idAgence);
                    DB::table('billets')
                        ->where('departId', $localiteUser)->where('Heur_departs', $prog->id_horaire)
                        ->where('jourVoyage', $prog->date_enregistre)->where('id_compagnie', $idCompagnie)
                        ->whereIn('destinationId', $destinationsAncien)
                        ->update(['Heur_departs' => $idHoraire]);
                }
            }
        }

        $prog->update([
            'id_horaire' => $idHoraire,
            'id_trajet' => $idDestination,
            'id_agence_destination' => $nouvelIdAgenceDestination,
        ]);

        // Le car d'origine sert maintenant le nouveau créneau : son compteur de places
        // réservées doit refléter ce nouveau créneau, pas l'ancien.
        $placesActuelles = $this->countPlacesVendues($idHoraire, $idDestination, $localiteUser, $prog->date_enregistre, $idCompagnie, $idAgence);
        Car::where('id_car', $prog->id_car_programmer)->update(['nbr_place_reserve' => $placesActuelles]);

        return true;
    }
}
