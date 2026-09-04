<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camion;
use App\Models\Car;
use App\Models\Chauffeur;
use App\Models\Employe;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ChauffeurController extends Controller
{
    private const NUMERO_RULES = ['required', 'regex:/^[6789]\d{7}$/'];

    public function store(Request $request): RedirectResponse
    {
        // Un chauffeur conduit soit un car, soit un camion (jamais les deux) — la checkbox
        // "est_camion" du formulaire détermine lequel des deux champs fait foi (voir
        // GESTION_CAMIONS_COLIS.md).
        $estCamion = $request->boolean('est_camion');

        $data = $request->validate([
            'nom_prenom' => ['required', 'string', 'max:200'],
            'numero' => self::NUMERO_RULES,
            'id_car' => [Rule::requiredIf(! $estCamion), 'nullable', 'integer', 'exists:car,id_car'],
            'id_camion' => [Rule::requiredIf($estCamion), 'nullable', 'integer', 'exists:camion,id_camion'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ], [
            'numero.regex' => 'Le numéro de téléphone doit contenir exactement 8 chiffres et commencer par 6, 7, 8 ou 9.',
            'id_car.required' => 'Le car qu\'il conduit est obligatoire.',
            'id_camion.required' => 'Le camion qu\'il conduit est obligatoire.',
        ]);

        // Le chauffeur hérite de la compagnie du véhicule auquel il est rattaché (plutôt
        // que de celle de l'utilisateur connecté) : un super_admin, qui n'a pas de
        // compagnie propre, doit pouvoir ajouter un chauffeur sans que id_compagnie soit
        // NULL en base.
        $idCompagnie = $estCamion
            ? Camion::where('id_camion', $data['id_camion'])->value('id_compagnie')
            : Car::where('id_car', $data['id_car'])->value('id_compagnie');

        $photoName = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profiles', 'public');
            $photoName = basename($path);
        }

        $chauffeur = Chauffeur::create([
            'nom_prenom' => $data['nom_prenom'],
            'numero' => $data['numero'],
            'id_car' => $estCamion ? null : $data['id_car'],
            'id_camion' => $estCamion ? $data['id_camion'] : null,
            'type_vehicule' => $estCamion ? 'camion' : 'car',
            'id_compagnie' => $idCompagnie,
            'photo' => $photoName,
        ]);

        Employe::creerEmployePourChauffeur($chauffeur->id_chauffeur, $idCompagnie);

        Flash::set('Chauffeur ajouté avec succès.', 'success');

        return redirect()->route('admin.car.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        $estCamion = $request->boolean('est_camion');

        $data = $request->validate([
            'nom_prenom' => ['required', 'string', 'max:200'],
            'numero' => self::NUMERO_RULES,
            'id_car' => [Rule::requiredIf(! $estCamion), 'nullable', 'integer', 'exists:car,id_car'],
            'id_camion' => [Rule::requiredIf($estCamion), 'nullable', 'integer', 'exists:camion,id_camion'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ], [
            'numero.regex' => 'Le numéro de téléphone doit contenir exactement 8 chiffres et commencer par 6, 7, 8 ou 9.',
            'id_car.required' => 'Le car qu\'il conduit est obligatoire.',
            'id_camion.required' => 'Le camion qu\'il conduit est obligatoire.',
        ]);

        $query = Chauffeur::where('id_chauffeur', $request->input('id_chauffeur'));
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $chauffeur = $query->first();

        if (! $chauffeur) {
            Flash::set('Échec de la modification.', 'danger');

            return redirect()->route('admin.car.index');
        }

        $idCompagnie = $estCamion
            ? Camion::where('id_camion', $data['id_camion'])->value('id_compagnie')
            : Car::where('id_car', $data['id_car'])->value('id_compagnie');

        $updateData = [
            'nom_prenom' => $data['nom_prenom'],
            'numero' => $data['numero'],
            'id_car' => $estCamion ? null : $data['id_car'],
            'id_camion' => $estCamion ? $data['id_camion'] : null,
            'type_vehicule' => $estCamion ? 'camion' : 'car',
            'id_compagnie' => $idCompagnie,
        ];

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profiles', 'public');
            $updateData['photo'] = basename($path);

            if ($chauffeur->photo) {
                Storage::disk('public')->delete('profiles/'.$chauffeur->photo);
            }
        }

        $chauffeur->update($updateData);

        Flash::set('Chauffeur mis à jour avec succès.', 'success');

        return redirect()->route('admin.car.index');
    }

    public function destroy(int $idChauffeur): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $query = Chauffeur::where('id_chauffeur', $idChauffeur);
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $supprime = $query->delete();

        Flash::set($supprime ? 'Chauffeur supprimé avec succès.' : 'Erreur lors de la suppression du chauffeur.', $supprime ? 'success' : 'danger');

        return redirect()->route('admin.car.index');
    }
}
