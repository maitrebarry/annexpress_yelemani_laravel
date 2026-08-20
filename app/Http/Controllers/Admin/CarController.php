<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        if (in_array($user->droit, ['Admin', 'PDG'], true) && $user->id_compagnie) {
            $listeCar = Car::where('id_compagnie', $user->id_compagnie)->orderBy('numero_car')->get();
            $listeChauffeur = Chauffeur::query()
                ->join('car', 'car.id_car', '=', 'chauffeur.id_car')
                ->where('car.id_compagnie', $user->id_compagnie)
                ->select('chauffeur.*', 'car.numero_car')
                ->orderBy('chauffeur.nom_prenom')
                ->get();
        } else {
            $listeCar = Car::orderBy('numero_car')->get();
            $listeChauffeur = Chauffeur::query()
                ->join('car', 'car.id_car', '=', 'chauffeur.id_car')
                ->select('chauffeur.*', 'car.numero_car')
                ->orderBy('chauffeur.nom_prenom')
                ->get();
        }

        $listeCompagnie = $user->isSuperAdmin() ? Compagnie::orderBy('nom_compagnie')->get() : collect();

        return view('admin.car.index', [
            'listeCar' => $listeCar,
            'listeChauffeur' => $listeChauffeur,
            'listeCompagnie' => $listeCompagnie,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $data = $request->validate([
            'numero_car' => ['required', 'integer'],
            'matriculle' => ['required', 'string', 'max:100'],
            'nbr_place' => ['required', 'integer', 'min:1'],
            'id_compagnie' => [Rule::requiredIf($user->isSuperAdmin()), 'nullable', 'integer'],
        ]);

        $idCompagnie = $user->isSuperAdmin() ? $data['id_compagnie'] : $user->id_compagnie;

        if (Car::where('numero_car', $data['numero_car'])->exists()) {
            return back()->withErrors(['numero_car' => "Le car « {$data['numero_car']} » existe déjà."])->withInput();
        }

        Car::create([
            'numero_car' => $data['numero_car'],
            'matriculle' => $data['matriculle'],
            'nbr_place' => $data['nbr_place'],
            'nbr_place_reserve' => 0,
            'programmer_car' => 'off',
            'id_compagnie' => $idCompagnie,
        ]);

        Flash::set('Car ajouté avec succès.', 'success');

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
