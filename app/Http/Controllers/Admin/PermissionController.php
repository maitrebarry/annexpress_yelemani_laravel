<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Utilisateur;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    // Catalogue des permissions (super_admin uniquement) : ajoute de nouvelles permissions
    // au système. Accessible via l'onglet "Permission" du menu Configuration.
    public function catalogue()
    {
        $liste = Permission::orderBy('nom_permission')->get();

        return view('admin.permission.catalogue', ['liste' => $liste]);
    }

    public function storeCatalogue(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_permission' => ['required', 'string', 'max:240', 'unique:permision,nom_permission'],
        ], [
            'nom_permission.unique' => 'Cette permission existe déjà.',
        ]);

        Permission::create(['nom_permission' => $data['nom_permission']]);

        Flash::set('Permission ajoutée avec succès.', 'success');

        return redirect()->route('admin.permission.catalogue');
    }

    // Assignation des permissions à un utilisateur donné (Admin de sa compagnie, ou
    // super_admin). Un Admin ne peut agir que sur un utilisateur de sa propre compagnie.
    public function assigner(Request $request, int $idUtilisateur)
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['super_admin', 'Admin'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.home');
        }

        $utilisateur = Utilisateur::find($idUtilisateur);

        if (! $user->isSuperAdmin() && (! $utilisateur || (string) $utilisateur->id_compagnie !== (string) $user->id_compagnie)) {
            Flash::set('Utilisateur introuvable.', 'danger');

            return redirect()->route('admin.configuration.index');
        }

        if ($request->isMethod('post')) {
            Permission::syncUserPermissions($idUtilisateur, (array) $request->input('permissions', []));

            Flash::set('Permissions enregistrées avec succès.', 'success');

            return redirect()->route('admin.configuration.index');
        }

        $allPermissions = Permission::orderBy('nom_permission')->get();
        $userPermissions = Permission::getUserPermissionIds($idUtilisateur);

        return view('admin.permission.assigner', [
            'utilisateur' => $utilisateur,
            'allPermissions' => $allPermissions,
            'userPermissions' => $userPermissions,
        ]);
    }
}
