<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camion;
use App\Models\Car;
use App\Models\Chauffeur;
use App\Models\Compagnie;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CarController extends Controller
{
    public function index()
    {
        $user = Auth::guard('staff')->user();

        // LEFT JOIN double (et non INNER JOIN) : un chauffeur affecté à un camion n'a pas
        // de ligne `car` à joindre (id_car est NULL pour lui), et inversement pour un
        // chauffeur de car côté `camion`. Avec un INNER JOIN (comme avant l'ajout des
        // camions, quand id_car était NOT NULL), ces chauffeurs disparaîtraient
        // silencieusement de la liste — voir GESTION_CAMIONS_COLIS.md.
        $chauffeurQuery = Chauffeur::query()
            ->leftJoin('car', function ($join) {
                $join->on('car.id_car', '=', 'chauffeur.id_car')->where('chauffeur.type_vehicule', 'car');
            })
            ->leftJoin('camion', function ($join) {
                $join->on('camion.id_camion', '=', 'chauffeur.id_camion')->where('chauffeur.type_vehicule', 'camion');
            })
            ->select('chauffeur.*', 'car.numero_car', 'camion.numero_camion')
            ->orderBy('chauffeur.nom_prenom');

        if (in_array($user->droit, ['Admin', 'PDG', 'secretaire'], true) && $user->id_compagnie) {
            $listeCar = Car::where('id_compagnie', $user->id_compagnie)->orderBy('numero_car')->get();
            $listeCamion = Camion::where('id_compagnie', $user->id_compagnie)->orderBy('numero_camion')->get();
            $listeChauffeur = $chauffeurQuery->where('chauffeur.id_compagnie', $user->id_compagnie)->get();
        } else {
            $listeCar = Car::orderBy('numero_car')->get();
            $listeCamion = Camion::orderBy('numero_camion')->get();
            $listeChauffeur = $chauffeurQuery->get();
        }

        $listeCompagnie = $user->isSuperAdmin() ? Compagnie::orderBy('nom_compagnie')->get() : collect();

        return view('admin.car.index', [
            'listeCar' => $listeCar,
            'listeCamion' => $listeCamion,
            'listeChauffeur' => $listeChauffeur,
            'listeCompagnie' => $listeCompagnie,
        ]);
    }

    // Port de Cars_chauffeur::saveCare() : le formulaire envoie numero_car[]/matriculle[]/
    // nbr_place[] ("add to row", plusieurs lignes ajoutées dynamiquement, les 3 champs
    // alignés par index) — chaque ligne est validée et insérée indépendamment, une
    // erreur sur l'une n'empêche pas les autres.
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $request->validate([
            'id_compagnie' => [Rule::requiredIf($user->isSuperAdmin()), 'nullable', 'integer'],
        ]);
        $idCompagnie = $user->isSuperAdmin() ? $request->input('id_compagnie') : $user->id_compagnie;

        $numeros = (array) $request->input('numero_car', []);
        $matriculles = (array) $request->input('matriculle', []);
        $nbrPlaces = (array) $request->input('nbr_place', []);

        $nbAjoutes = 0;
        $erreurs = [];

        foreach ($numeros as $i => $numeroCar) {
            $numeroCar = trim((string) $numeroCar);
            $matriculle = trim((string) ($matriculles[$i] ?? ''));
            $nbrPlace = trim((string) ($nbrPlaces[$i] ?? ''));

            // Ligne vide (pas remplie par l'agent) : ignorée silencieusement, ce n'est pas
            // une erreur en soi si d'autres lignes sont valides.
            if ($numeroCar === '' && $matriculle === '' && $nbrPlace === '') {
                continue;
            }

            if ($numeroCar === '') {
                $erreurs[] = 'Ligne '.($i + 1).' : le numéro du car est obligatoire.';

                continue;
            }
            if ($matriculle === '') {
                $erreurs[] = 'Ligne '.($i + 1).' : le matricule est obligatoire.';

                continue;
            }
            if ($nbrPlace === '' || ! is_numeric($nbrPlace)) {
                $erreurs[] = 'Ligne '.($i + 1).' : le nombre de places doit être un nombre.';

                continue;
            }
            if (Car::where('numero_car', $numeroCar)->exists()) {
                $erreurs[] = "Le car « $numeroCar » existe déjà.";

                continue;
            }

            Car::create([
                'numero_car' => $numeroCar,
                'matriculle' => $matriculle,
                'nbr_place' => $nbrPlace,
                'nbr_place_reserve' => 0,
                'programmer_car' => 'off',
                'id_compagnie' => $idCompagnie,
            ]);
            $nbAjoutes++;
        }

        $this->flashResultatAjoutMultiple($nbAjoutes, $erreurs, 'car', 'cars');

        return redirect()->route('admin.car.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $query = Car::where('id_car', $request->input('id_car'));
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $modifiee = $query->update([
            'numero_car' => $request->input('numero_car'),
            'matriculle' => $request->input('matricule'),
            'nbr_place' => $request->input('nbr_place'),
        ]);

        Flash::set($modifiee ? 'Car mis à jour avec succès.' : 'Échec de la modification.', $modifiee ? 'success' : 'danger');

        return redirect()->route('admin.car.index');
    }

    public function destroy(int $idCar): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $query = Car::where('id_car', $idCar);
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $supprime = $query->delete();

        Flash::set($supprime ? 'Véhicule supprimé avec succès.' : 'Erreur lors de la suppression du véhicule.', $supprime ? 'success' : 'danger');

        return redirect()->route('admin.car.index');
    }
}
