<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Compagnie;
use App\Models\Employe;
use App\Models\Permission;
use App\Models\Utilisateur;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ConfigurationController extends Controller
{
    private const MOT_DE_PASSE_PAR_DEFAUT = '123456';

    private const TELEPHONE_REGEX = '/^[0-9+\s.\-]{6,20}$/';

    // Seuls super_admin et Admin gèrent les comptes utilisateurs (PDG, bien que
    // superviseur lecture seule d'une compagnie, n'a pas accès à cet écran).
    public function index(Request $request)
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['super_admin', 'Admin'], true)) {
            Flash::set('Accès restreint.', 'danger');

            return redirect()->route('admin.home');
        }

        $query = Utilisateur::query()
            ->leftJoin('agence', 'agence.idAgence', '=', 'utilisateur.id_agence')
            ->where('utilisateur.droit', '!=', 'super_admin')
            ->select('utilisateur.*', 'agence.numeroGare');

        if (! $user->isSuperAdmin()) {
            $query->where('agence.id_compagnie', $user->id_compagnie);
        }

        $listes = $query->orderBy('utilisateur.utilisateurs')->get();

        if ($user->isSuperAdmin()) {
            $listeGares = Agence::orderBy('localite')->get();
        } else {
            $listeGares = Agence::where('id_compagnie', $user->id_compagnie)->orderBy('localite')->get();
        }

        $listeCompagnie = $user->isSuperAdmin() ? Compagnie::orderBy('nom_compagnie')->get() : collect();

        return view('admin.configuration.index', [
            'liste' => $listes,
            'listeGares' => $listeGares,
            'listeCompagnie' => $listeCompagnie,
            'droitsAutorises' => $this->droitsAutorisesPour($user->droit),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('utilisateur_creation');
        $user = Auth::guard('staff')->user();

        $droitsAutorises = $this->droitsAutorisesPour($user->droit);

        $data = $request->validate([
            'utilisateurs' => ['required', 'string', 'max:250'],
            'emailUser' => ['required', 'email', 'max:250', 'unique:utilisateur,emailUser'],
            'telephone' => ['nullable', 'regex:'.self::TELEPHONE_REGEX],
            'droit' => ['required', Rule::in($droitsAutorises)],
            'id_agence' => ['nullable', 'string'],
            'id_compagnie' => ['nullable', 'string'],
            'profile' => ['nullable', Rule::in(['billet', 'colis'])],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        // Un chef d'escale ou un simple Utilisateur doit être rattaché à une gare ; seuls
        // Admin et PDG (rattachés à une compagnie entière) échappent à cette règle.
        if (! in_array($data['droit'], ['Admin', 'PDG', 'secretaire'], true) && empty($data['id_agence'])) {
            return back()->withErrors(['id_agence' => 'La gare est obligatoire pour ce type de compte.'])->withInput();
        }

        $idCompagnie = in_array($data['droit'], ['Admin', 'PDG', 'secretaire'], true)
            ? ($data['id_compagnie'] ?? null)
            : $user->id_compagnie;

        $profile = $data['droit'] === 'Utilisateur' ? ($data['profile'] ?? null) : null;

        $photoName = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profiles', 'public');
            $photoName = basename($path);
        }

        $nouvelUtilisateur = Utilisateur::create([
            'utilisateurs' => $data['utilisateurs'],
            'emailUser' => $data['emailUser'],
            'telephone' => $data['telephone'] ?: null,
            'motPasse' => Hash::make(self::MOT_DE_PASSE_PAR_DEFAUT),
            'status' => 1,
            'id_agence' => $data['id_agence'] ?? null,
            'id_compagnie' => $idCompagnie,
            'droit' => $data['droit'],
            'profile' => $profile,
            'photo' => $photoName,
        ]);

        Permission::assignPermissionsParDefautPourRole($nouvelUtilisateur->idUser, $data['droit'], $profile);

        // Hook additif du module Salaire (voir GESTION_SALAIRES.md) : crée directement la
        // fiche de paie du nouveau compte (salaire à 0, à renseigner ensuite par l'Admin
        // depuis "Salaires") — rien à faire manuellement pour le personnel qui vient de
        // recevoir un compte.
        Employe::creerEmployePourUtilisateur($nouvelUtilisateur->idUser, $data['droit'], $data['id_agence'] ?? null, $idCompagnie);

        Flash::set(
            'Utilisateur ajouté avec succès. Mot de passe par défaut : '.self::MOT_DE_PASSE_PAR_DEFAUT.' (à communiquer, modifiable après la première connexion).',
            'success'
        );

        return redirect()->route('admin.configuration.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $this->requirePermission('utilisateur_modifier');
        $user = Auth::guard('staff')->user();

        $cible = Utilisateur::findOrFail($request->input('idUser'));

        if ($user->droit === 'Admin' && (string) $cible->id_compagnie !== (string) $user->id_compagnie) {
            Flash::set('Action non autorisée.', 'danger');

            return redirect()->route('admin.configuration.index');
        }

        $droitsAutorises = $this->droitsAutorisesPour($user->droit);

        $data = $request->validate([
            'utilisateurs' => ['required', 'string', 'max:250'],
            'emailUser' => ['required', 'email', 'max:250', Rule::unique('utilisateur', 'emailUser')->ignore($cible->idUser, 'idUser')],
            'telephone' => ['nullable', 'regex:'.self::TELEPHONE_REGEX],
            'droit' => ['required', Rule::in($droitsAutorises)],
            'id_agence' => ['nullable', 'string'],
            'profile' => ['nullable', Rule::in(['billet', 'colis'])],
            'motPasse' => ['nullable', 'string', 'min:6'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        // Même règle qu'à la création : un chef d'escale ou un simple Utilisateur doit être
        // rattaché à une gare ; seuls Admin/PDG/secrétaire général (compagnie entière) y
        // échappent. Sans ce champ dans le formulaire de modification (avant ce correctif),
        // la gare d'un chef d'escale n'était ni visible ni modifiable une fois le compte créé.
        if (! in_array($data['droit'], ['Admin', 'PDG', 'secretaire'], true) && empty($data['id_agence'])) {
            return back()->withErrors(['id_agence' => 'La gare est obligatoire pour ce type de compte.'])->withInput();
        }

        $profile = $data['droit'] === 'Utilisateur' ? ($data['profile'] ?? null) : null;

        $cible->utilisateurs = $data['utilisateurs'];
        $cible->emailUser = $data['emailUser'];
        $cible->telephone = $data['telephone'] ?: null;
        $cible->droit = $data['droit'];
        $cible->id_agence = in_array($data['droit'], ['Admin', 'PDG', 'secretaire'], true) ? null : $data['id_agence'];
        $cible->profile = $profile;

        if (! empty($data['motPasse'])) {
            $cible->motPasse = Hash::make($data['motPasse']);
        }

        if ($request->hasFile('photo')) {
            if ($cible->photo) {
                Storage::disk('public')->delete('profiles/'.$cible->photo);
            }
            $path = $request->file('photo')->store('profiles', 'public');
            $cible->photo = basename($path);
        }

        $cible->save();

        // Attribue automatiquement le jeu de permissions par défaut du (nouveau) rôle/service
        // — additif uniquement (insertOrIgnore), ne retire jamais une permission déjà
        // accordée manuellement via l'écran "Assigner les permissions". Sans cet appel, un
        // utilisateur promu chef d'escale ou dont le service (billet/colis) change via ce
        // formulaire d'édition n'obtenait aucune des permissions correspondantes — seule la
        // création (store()) le faisait jusqu'ici.
        Permission::assignPermissionsParDefautPourRole($cible->idUser, $data['droit'], $profile);

        Flash::set('Utilisateur modifié avec succès.', 'success');

        return redirect()->route('admin.configuration.index');
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $this->requirePermission('utilisateur_active/desactive');
        $user = Auth::guard('staff')->user();

        $data = $request->validate([
            'idUser' => ['required', 'integer'],
            'newStatut' => ['required', 'integer', Rule::in([0, 1])],
        ]);

        $cible = Utilisateur::findOrFail($data['idUser']);

        if ($user->droit === 'Admin' && (string) $cible->id_compagnie !== (string) $user->id_compagnie) {
            Flash::set('Action non autorisée.', 'danger');

            return redirect()->route('admin.configuration.index');
        }

        $cible->update(['status' => $data['newStatut']]);

        Flash::set('Statut mis à jour avec succès.', 'success');

        return redirect()->route('admin.configuration.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! $user->isSuperAdmin()) {
            Flash::set('Action non autorisée.', 'danger');

            return redirect()->route('admin.configuration.index');
        }

        $cible = Utilisateur::findOrFail($request->input('idUser'));

        if ($cible->idUser === $user->idUser) {
            Flash::set('Vous ne pouvez pas supprimer votre propre compte.', 'danger');

            return redirect()->route('admin.configuration.index');
        }

        $confirmation = trim((string) $request->input('confirmation'));
        if ($confirmation === '' || $confirmation !== $cible->emailUser) {
            Flash::set("Confirmation incorrecte : l'email saisi ne correspond pas au compte.", 'danger');

            return redirect()->route('admin.configuration.index');
        }

        if ($cible->photo) {
            Storage::disk('public')->delete('profiles/'.$cible->photo);
        }

        $cible->delete();

        Flash::set('Utilisateur supprimé avec succès.', 'success');

        return redirect()->route('admin.configuration.index');
    }

    // Liste des droits qu'un rôle a le droit d'attribuer (création ou modification).
    // Un Admin ne peut jamais s'attribuer, ni attribuer, Admin/PDG/super_admin.
    private function droitsAutorisesPour(string $role): array
    {
        return $role === 'super_admin'
            ? ['super_admin', 'Admin', 'PDG', 'secretaire', 'Utilisateur', 'chef_d_escale']
            : ['Utilisateur', 'chef_d_escale', 'secretaire'];
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(Auth::guard('staff')->user()?->userHasPermission($permission), 403);
    }
}
