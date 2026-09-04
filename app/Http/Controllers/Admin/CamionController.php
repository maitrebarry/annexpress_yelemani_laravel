<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camion;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Port de Projets_licence/app/models/Camion.php + app/controllers/admin/Camions.php —
 * voir GESTION_CAMIONS_COLIS.md. Même principe que CarController, sans nbr_place (un
 * camion n'a pas de notion de places passagers) et avec un statut actif/inactif à la
 * place de programmer_car (qui n'a pas de sens pour un camion). L'index (liste) reste
 * porté par CarController::index(), qui rend le même écran à 3 onglets (Cars/Camions/
 * Chauffeurs) que le legacy — pas d'action index() séparée ici.
 */
class CamionController extends Controller
{
    // Port de Camion::saveCamion() : le formulaire envoie numero_camion[]/
    // matriculle_camion[] ("add to row", plusieurs lignes ajoutées dynamiquement) — chaque
    // ligne est validée et insérée indépendamment, une erreur sur l'une n'empêche pas
    // les autres.
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $request->validate([
            'id_compagnie' => [Rule::requiredIf($user->isSuperAdmin()), 'nullable', 'integer'],
        ]);
        $idCompagnie = $user->isSuperAdmin() ? $request->input('id_compagnie') : $user->id_compagnie;

        $numeros = (array) $request->input('numero_camion', []);
        $matriculles = (array) $request->input('matriculle_camion', []);

        $nbAjoutes = 0;
        $erreurs = [];

        foreach ($numeros as $i => $numeroCamion) {
            $numeroCamion = trim((string) $numeroCamion);
            $matriculle = trim((string) ($matriculles[$i] ?? ''));

            if ($numeroCamion === '' && $matriculle === '') {
                continue;
            }

            if ($numeroCamion === '') {
                $erreurs[] = 'Ligne '.($i + 1).' : le numéro du camion est obligatoire.';

                continue;
            }
            if ($matriculle === '') {
                $erreurs[] = 'Ligne '.($i + 1).' : le matricule est obligatoire.';

                continue;
            }
            if (Camion::where('numero_camion', $numeroCamion)->exists()) {
                $erreurs[] = "Le camion « $numeroCamion » existe déjà.";

                continue;
            }

            Camion::create([
                'numero_camion' => $numeroCamion,
                'matriculle' => $matriculle,
                'actif' => 'on',
                'id_compagnie' => $idCompagnie,
            ]);
            $nbAjoutes++;
        }

        $this->flashResultatAjoutMultiple($nbAjoutes, $erreurs, 'camion', 'camions');

        return redirect()->route('admin.car.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $query = Camion::where('id_camion', $request->input('id_camion'));
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $modifie = $query->update([
            'numero_camion' => $request->input('numero_camion'),
            'matriculle' => $request->input('matriculle'),
            'actif' => $request->input('actif', 'on'),
        ]);

        Flash::set($modifie ? 'Camion mis à jour avec succès.' : 'Échec de la modification.', $modifie ? 'success' : 'danger');

        return redirect()->route('admin.car.index');
    }

    public function destroy(int $idCamion): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $query = Camion::where('id_camion', $idCamion);
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $supprime = $query->delete();

        Flash::set($supprime ? 'Camion supprimé avec succès.' : 'Erreur lors de la suppression du camion.', $supprime ? 'success' : 'danger');

        return redirect()->route('admin.car.index');
    }
}
