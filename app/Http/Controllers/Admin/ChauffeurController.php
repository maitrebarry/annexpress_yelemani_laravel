<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Chauffeur;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChauffeurController extends Controller
{
    private const NUMERO_RULES = ['required', 'regex:/^[6789]\d{7}$/'];

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_prenom' => ['required', 'string', 'max:200'],
            'numero' => self::NUMERO_RULES,
            'id_car' => ['required', 'integer', 'exists:car,id_car'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ], [
            'numero.regex' => 'Le numéro de téléphone doit contenir exactement 8 chiffres et commencer par 6, 7, 8 ou 9.',
        ]);

        // Le chauffeur hérite de la compagnie du car auquel il est rattaché (plutôt que de
        // celle de l'utilisateur connecté) : un super_admin, qui n'a pas de compagnie propre,
        // doit pouvoir ajouter un chauffeur sans que id_compagnie soit NULL en base.
        $idCompagnie = Car::where('id_car', $data['id_car'])->value('id_compagnie');

        $photoName = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profiles', 'public');
            $photoName = basename($path);
        }

        Chauffeur::create([
            'nom_prenom' => $data['nom_prenom'],
            'numero' => $data['numero'],
            'id_car' => $data['id_car'],
            'id_compagnie' => $idCompagnie,
            'photo' => $photoName,
        ]);

        Flash::set('Chauffeur ajouté avec succès.', 'success');

        return redirect()->route('admin.car.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $data = $request->validate([
            'nom_prenom' => ['required', 'string', 'max:200'],
            'numero' => self::NUMERO_RULES,
            'id_car' => ['required', 'integer', 'exists:car,id_car'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ], [
            'numero.regex' => 'Le numéro de téléphone doit contenir exactement 8 chiffres et commencer par 6, 7, 8 ou 9.',
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

        $updateData = [
            'nom_prenom' => $data['nom_prenom'],
            'numero' => $data['numero'],
            'id_car' => $data['id_car'],
            'id_compagnie' => Car::where('id_car', $data['id_car'])->value('id_compagnie'),
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
