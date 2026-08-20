<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Compagnie;
use App\Models\Horaire;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HoraireController extends Controller
{
    public function index()
    {
        $user = Auth::guard('staff')->user();

        if (in_array($user->droit, ['Admin', 'PDG'], true) && $user->id_compagnie) {
            $liste = Horaire::where('id_compagnie', $user->id_compagnie)->orderBy('heuredepart')->get();
        } else {
            $liste = Horaire::orderBy('heuredepart')->get();
        }

        $listeCompagnie = $user->isSuperAdmin() ? Compagnie::orderBy('nom_compagnie')->get() : collect();

        return view('admin.horaire.index', ['liste' => $liste, 'listeCompagnie' => $listeCompagnie]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        // Un super_admin n'a pas de compagnie propre (id_compagnie NULL) : il doit choisir
        // pour quelle compagnie il ajoute ces horaires, sinon l'INSERT échoue (colonne NOT NULL).
        if ($user->isSuperAdmin()) {
            $idCompagnie = $request->input('id_compagnie');
            if (! $idCompagnie) {
                Flash::set('Veuillez choisir une compagnie.', 'danger');

                return redirect()->route('admin.horaire.index');
            }
        } else {
            $idCompagnie = $user->id_compagnie;
        }

        $heures = array_values(array_filter(array_map('trim', (array) $request->input('heuredepart', []))));

        if (empty($heures)) {
            Flash::set('Aucun horaire à ajouter.', 'danger');

            return redirect()->route('admin.horaire.index');
        }

        $nbAjoutes = 0;
        $erreurs = [];
        $vues = [];

        foreach ($heures as $heure) {
            if (in_array($heure, $vues, true)) {
                $erreurs[] = "« $heure » est en double dans les lignes saisies.";

                continue;
            }
            $vues[] = $heure;

            if (Horaire::where('heuredepart', $heure)->where('id_compagnie', $idCompagnie)->exists()) {
                $erreurs[] = "« $heure » existe déjà.";

                continue;
            }

            Horaire::create(['heuredepart' => $heure, 'id_compagnie' => $idCompagnie]);
            $nbAjoutes++;
        }

        if ($nbAjoutes > 0) {
            Flash::set($nbAjoutes > 1 ? "$nbAjoutes horaires ajoutés avec succès." : 'Heure ajoutée avec succès.', 'success');
        }
        if (! empty($erreurs)) {
            Flash::set(implode(' ', $erreurs), 'danger');
        }

        return redirect()->route('admin.horaire.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        $id = $request->input('id_heure');
        $heure = trim((string) $request->input('heuredepart'));

        if (! $id || $heure === '') {
            Flash::set('Veuillez remplir tous les champs.', 'danger');

            return redirect()->route('admin.horaire.index');
        }

        $query = Horaire::where('id_heure', $id);
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $modifiee = $query->update(['heuredepart' => $heure]);

        Flash::set($modifiee ? 'Heure modifiée avec succès.' : 'Échec de la modification de l\'heure.', $modifiee ? 'success' : 'danger');

        return redirect()->route('admin.horaire.index');
    }

    public function destroy(int $idHeure): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $query = Horaire::where('id_heure', $idHeure);
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $supprimee = $query->delete();

        Flash::set($supprimee ? 'Heure supprimée avec succès.' : 'Échec de la suppression de l\'heure.', $supprimee ? 'success' : 'danger');

        return redirect()->route('admin.horaire.index');
    }
}
