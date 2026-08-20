<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\LiaisonCarTrajet;
use App\Models\Programme;
use App\Models\ReferenceCar;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Programmation_cars.php +
 * app/models/Programmation_car.php — écran "Cars" du menu G-programme
 * (affectation des trajets programmés à un car).
 *
 * Simplification par rapport au legacy : l'écran "Détails" (route séparée
 * `/details/{id}`) devient une modale sur la même page, comme le reste de
 * l'appli — pas de nouvelle page pour une simple consultation.
 */
class ProgrammationCarController extends Controller
{
    public function index(): View
    {
        $user = Auth::guard('staff')->user();
        $idCompagnie = $user->id_compagnie;

        $carsQuery = Car::query()->when($idCompagnie, fn ($q) => $q->where('id_compagnie', $idCompagnie));

        $listeCarProgramme = (clone $carsQuery)->where('programmer_car', 'on')->orderBy('numero_car')->get();
        $listeCarDisponible = (clone $carsQuery)->where('programmer_car', 'off')->orderBy('numero_car')->get();

        $listeTrajet = Programme::query()
            ->select('programmer.idProgrammer', 'programmer.heureDepart', 'a1.localite as depart', 'a1.numeroGare as gareDepart', 'a2.localite as destination', 'a2.numeroGare as gareDestination')
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->when($idCompagnie, fn ($q) => $q->where('programmer.id_compagnie', $idCompagnie))
            ->orderBy('a1.localite')->orderBy('a2.localite')->orderBy('programmer.heureDepart')
            ->get();

        // Trajets déjà assignés à chaque car : sert à griser (au lieu de laisser cliquable
        // puis rejeter en silence) les trajets déjà liés quand on rouvre "Ajouter un trajet"
        // pour un car précis, et à afficher le détail de sa programmation.
        $liaisons = LiaisonCarTrajet::query()
            ->when($idCompagnie, fn ($q) => $q->where('id_compagnie', $idCompagnie))
            ->get();
        $trajetsParCar = $liaisons->groupBy('id_car')->map(fn ($g) => $g->pluck('id_trajets')->values());

        $trajetsById = $listeTrajet->keyBy('idProgrammer');
        $detailsParCar = $trajetsParCar->map(
            fn ($ids) => $ids->map(fn ($id) => $trajetsById->get($id))->filter()->values()
        );

        return view('admin.programmation-car.index', [
            'listeCarProgramme' => $listeCarProgramme,
            'listeCarDisponible' => $listeCarDisponible,
            'listeTrajet' => $listeTrajet,
            'trajetsParCar' => $trajetsParCar,
            'detailsParCar' => $detailsParCar,
        ]);
    }

    // Le formulaire permet de cocher plusieurs cars à la fois : les mêmes trajets
    // sélectionnés une fois sont assignés à chacun, pour ne pas avoir à resaisir les
    // trajets pour chaque car.
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $idCars = array_values(array_unique(array_filter(
            (array) $request->input('id_car', []),
            fn ($id) => trim((string) $id) !== ''
        )));
        $idTrajets = array_filter((array) $request->input('idTrajet', []));

        $errors = [];
        if (empty($idCars)) {
            $errors[] = 'Veuillez cocher au moins un car.';
        }
        if (empty($idTrajets)) {
            $errors[] = 'Le trajet est obligatoire.';
        }
        foreach ($idCars as $idCar) {
            if (! $this->carAppartientCompagnie($user, $idCar)) {
                $errors[] = "Le car #$idCar n'appartient pas à votre compagnie.";
            }
        }

        if (! empty($errors)) {
            Flash::set(implode(' ', $errors), 'warning');

            return redirect()->route('admin.programmation-car.index');
        }

        $idCompagnie = $user->id_compagnie;
        $nbProgrammes = 0;

        foreach ($idCars as $idCar) {
            ReferenceCar::create(['id_car' => $idCar]);
            Car::where('id_car', $idCar)->update(['programmer_car' => 'on']);
            $this->lierTrajetsAuCar((int) $idCar, $idTrajets, $idCompagnie);
            $nbProgrammes++;
        }

        $nbTrajets = count($idTrajets);
        Flash::set(
            "$nbProgrammes car".($nbProgrammes > 1 ? 's' : '').' programmé'.($nbProgrammes > 1 ? 's' : '')
                ." avec $nbTrajets trajet".($nbTrajets > 1 ? 's' : '').' chacun (aller-retour inclus automatiquement).',
            'success'
        );

        return redirect()->route('admin.programmation-car.index');
    }

    // Ajoute un ou plusieurs trajets supplémentaires à un car déjà programmé.
    public function ajouterTrajet(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $idCar = $request->input('id_car');
        $idTrajets = array_filter((array) $request->input('idTrajet', []));

        if (empty($idCar) || empty($idTrajets)) {
            Flash::set('Le car et le trajet sont obligatoires.', 'danger');

            return redirect()->route('admin.programmation-car.index');
        }

        if (! $this->carAppartientCompagnie($user, $idCar)) {
            Flash::set("Ce car n'appartient pas à votre compagnie.", 'danger');

            return redirect()->route('admin.programmation-car.index');
        }

        $this->lierTrajetsAuCar((int) $idCar, $idTrajets, $user->id_compagnie);

        Flash::set('Trajet ajouté au car avec succès.', 'success');

        return redirect()->route('admin.programmation-car.index');
    }

    // Déprogramme un car : retire ses trajets affectés et sa référence, le remet disponible.
    public function destroy(int $idCar): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! $this->carAppartientCompagnie($user, $idCar)) {
            Flash::set("Ce car n'appartient pas à votre compagnie.", 'danger');

            return redirect()->route('admin.programmation-car.index');
        }

        DB::transaction(function () use ($idCar) {
            LiaisonCarTrajet::where('id_car', $idCar)->delete();
            ReferenceCar::where('id_car', $idCar)->delete();
            Car::where('id_car', $idCar)->update(['programmer_car' => 'off']);
        });

        Flash::set('La programmation du car a été supprimée avec succès.', 'success');

        return redirect()->route('admin.programmation-car.index');
    }

    private function carAppartientCompagnie($user, $idCar): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return Car::where('id_car', $idCar)->where('id_compagnie', $user->id_compagnie)->exists();
    }

    // Relie un ou plusieurs trajets (et leur sens inverse) à un car, sans dupliquer les
    // liaisons existantes — pour qu'un car ait toujours une destination valide au retour.
    private function lierTrajetsAuCar(int $idCar, array $idsTrajet, $idCompagnie): void
    {
        foreach ($idsTrajet as $idTrajet) {
            $idsALier = [(int) $idTrajet];
            $idRetour = $this->trajetRetourId((int) $idTrajet, $idCompagnie);
            if ($idRetour && ! in_array($idRetour, $idsALier, true)) {
                $idsALier[] = $idRetour;
            }

            foreach ($idsALier as $idALier) {
                $dejaAffecte = LiaisonCarTrajet::where('id_car', $idCar)->where('id_trajets', $idALier)->exists();
                if (! $dejaAffecte) {
                    LiaisonCarTrajet::create([
                        'id_car' => $idCar,
                        'id_trajets' => $idALier,
                        'id_compagnie' => $idCompagnie,
                    ]);
                }
            }
        }
    }

    // Trouve le trajet-programme qui fait le sens inverse (destination -> départ) d'un trajet donné.
    private function trajetRetourId(int $idTrajet, $idCompagnie): ?int
    {
        $trajet = Programme::find($idTrajet);
        if (! $trajet) {
            return null;
        }

        $retour = Programme::where('idDepart', $trajet->idDestination)
            ->where('idDestination', $trajet->idDepart)
            ->where('id_compagnie', $idCompagnie)
            ->first();

        return $retour?->idProgrammer;
    }
}
