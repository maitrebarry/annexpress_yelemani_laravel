<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Compagnie;
use App\Models\Escale;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EscaleController extends Controller
{
    public function index()
    {
        $user = Auth::guard('staff')->user();

        if (in_array($user->droit, ['Admin', 'PDG', 'secretaire'], true) && $user->id_compagnie) {
            $liste = Escale::where('id_compagnie', $user->id_compagnie)->orderBy('escales')->get();
        } else {
            $liste = Escale::orderBy('escales')->get();
        }

        $listeCompagnie = $user->isSuperAdmin() ? Compagnie::orderBy('nom_compagnie')->get() : collect();

        return view('admin.escale.index', ['liste' => $liste, 'listeCompagnie' => $listeCompagnie]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        // Un super_admin n'a pas de compagnie propre (id_compagnie NULL) : il doit choisir
        // pour quelle compagnie il ajoute ces escales, sinon l'INSERT échoue (colonne NOT NULL).
        if ($user->isSuperAdmin()) {
            $idCompagnie = $request->input('id_compagnie');
            if (! $idCompagnie) {
                Flash::set('Veuillez choisir une compagnie.', 'danger');

                return redirect()->route('admin.escale.index');
            }
        } else {
            $idCompagnie = $user->id_compagnie;
        }

        $noms = array_values(array_filter(array_map('trim', (array) $request->input('escales', []))));

        if (empty($noms)) {
            Flash::set('Aucune escale à ajouter.', 'danger');

            return redirect()->route('admin.escale.index');
        }

        $nbAjoutes = 0;
        $erreurs = [];
        $vues = [];

        foreach ($noms as $nom) {
            if (in_array($nom, $vues, true)) {
                $erreurs[] = "« $nom » est en double dans les lignes saisies.";

                continue;
            }
            $vues[] = $nom;

            if (Escale::where('escales', $nom)->where('id_compagnie', $idCompagnie)->exists()) {
                $erreurs[] = "« $nom » existe déjà.";

                continue;
            }

            Escale::create(['escales' => $nom, 'id_compagnie' => $idCompagnie]);
            $nbAjoutes++;
        }

        if ($nbAjoutes > 0) {
            Flash::set($nbAjoutes > 1 ? "$nbAjoutes escales ajoutées avec succès." : 'Escale ajoutée avec succès.', 'success');
        }
        if (! empty($erreurs)) {
            Flash::set(implode(' ', $erreurs), 'danger');
        }

        return redirect()->route('admin.escale.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        $id = $request->input('id_escale');
        $nom = trim((string) $request->input('escales'));

        if (! $id || $nom === '') {
            Flash::set('Veuillez remplir tous les champs.', 'danger');

            return redirect()->route('admin.escale.index');
        }

        $query = Escale::where('id_escale', $id);
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $modifiee = $query->update(['escales' => $nom]);

        Flash::set($modifiee ? 'Escale mise à jour avec succès.' : 'Échec de la modification.', $modifiee ? 'success' : 'danger');

        return redirect()->route('admin.escale.index');
    }

    public function destroy(int $idEscale): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $query = Escale::where('id_escale', $idEscale);
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $supprimee = $query->delete();

        Flash::set($supprimee ? 'Escale supprimée avec succès.' : 'Erreur lors de la suppression.', $supprimee ? 'success' : 'danger');

        return redirect()->route('admin.escale.index');
    }
}
