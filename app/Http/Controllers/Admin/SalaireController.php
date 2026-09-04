<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\BulletinPaie;
use App\Models\Employe;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Salaires.php — voir GESTION_SALAIRES.md.
 * La permission Salaire_apercu (gérée par le middleware `permission:` sur les routes,
 * voir routes/web.php) gouverne toute la visibilité du module, y compris son propre
 * salaire ; elle ne donne que la LECTURE. Créer un employé hors-système, modifier un
 * salaire ou générer un bulletin reste réservé à Admin/PDG/super_admin (requireGestionnaire()
 * ci-dessous), même pour un compte qui n'a reçu que la lecture.
 */
class SalaireController extends Controller
{
    private function requireGestionnaire(): ?RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['Admin', 'PDG', 'super_admin'], true)) {
            Flash::set("Action réservée à l'administration.", 'danger');

            return redirect()->route('admin.salaire.index');
        }

        return null;
    }

    public function index(): View
    {
        $user = Auth::guard('staff')->user();
        $peutGerer = in_array($user->droit, ['Admin', 'PDG', 'super_admin'], true);

        return view('admin.salaire.index', [
            'listeEmployes' => Employe::getEmployesVisibles($user),
            'listeAgences' => $peutGerer ? Agence::where('id_compagnie', $user->id_compagnie)->orderBy('localite')->get() : collect(),
            'peutGerer' => $peutGerer,
        ]);
    }

    // Ajout d'un employé hors-système (gardien, balayeur...) — réservé Admin/PDG/super_admin.
    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireGestionnaire()) {
            return $redirect;
        }

        $user = Auth::guard('staff')->user();

        $data = $request->validate([
            'nom_prenom' => ['required', 'string', 'max:200'],
            'poste' => ['required', 'string', 'max:100'],
            'salaire_base' => ['required', 'numeric', 'min:0'],
            'id_agence' => ['nullable', 'integer'],
        ]);

        Employe::create([
            'nom_prenom' => $data['nom_prenom'],
            'poste' => $data['poste'],
            'id_agence' => $data['id_agence'] ?: null,
            'id_compagnie' => $user->id_compagnie,
            'salaire_base' => $data['salaire_base'],
            'statut' => 'actif',
            'date_creation' => now()->toDateString(),
        ]);

        Flash::set('Employé ajouté avec succès.', 'success');

        return redirect()->route('admin.salaire.index');
    }

    // Modification du poste/salaire/gare/statut -- réservée Admin/PDG/super_admin. Le nom
    // n'est éditable ici que pour le personnel hors-système (sinon il vient de
    // utilisateur/chauffeur) : le champ n'est présent dans le POST que dans ce cas (voir
    // admin/salaire/index.blade.php).
    public function update(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireGestionnaire()) {
            return $redirect;
        }

        $user = Auth::guard('staff')->user();

        $data = $request->validate([
            'id_employe' => ['required', 'integer'],
            'poste' => ['required', 'string', 'max:100'],
            'salaire_base' => ['required', 'numeric', 'min:0'],
            'id_agence' => ['nullable', 'integer'],
            'statut' => ['required', 'in:actif,inactif'],
            'nom_prenom' => ['nullable', 'string', 'max:200'],
        ]);

        $query = Employe::where('id_employe', $data['id_employe']);
        if ($user->droit !== 'super_admin') {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $updateData = [
            'poste' => $data['poste'],
            'salaire_base' => $data['salaire_base'],
            'id_agence' => $data['id_agence'] ?: null,
            'statut' => $data['statut'],
        ];
        if ($request->filled('nom_prenom')) {
            $updateData['nom_prenom'] = $data['nom_prenom'];
        }

        $modifie = $query->update($updateData);

        Flash::set($modifie ? 'Fiche employé mise à jour avec succès.' : 'Échec de la modification.', $modifie ? 'success' : 'danger');

        return redirect()->route('admin.salaire.index');
    }

    // Génère un bulletin pour un employé (bouton par ligne, id_employe seul) OU pour
    // plusieurs d'un coup (cases à cocher + "Générer pour la sélection", ids_employes[]) --
    // même endpoint, une seule période pour tout le lot.
    public function genererBulletin(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireGestionnaire()) {
            return $redirect;
        }

        $user = Auth::guard('staff')->user();

        $data = $request->validate([
            'periode' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'id_employe' => ['nullable', 'integer'],
            'ids_employes' => ['nullable', 'array'],
            'ids_employes.*' => ['integer'],
        ]);

        $ids = ! empty($data['ids_employes']) ? $data['ids_employes'] : (! empty($data['id_employe']) ? [$data['id_employe']] : []);

        if (empty($ids)) {
            Flash::set('Veuillez sélectionner au moins un employé.', 'danger');

            return redirect()->route('admin.salaire.index');
        }

        $nbGeneres = 0;
        foreach ($ids as $idEmploye) {
            // getEmployeVisibleById() re-vérifie l'IDOR (compagnie/gare) pour CHAQUE id :
            // un id trafiqué dans la requête (hors scope de l'appelant) est simplement ignoré.
            $employe = Employe::getEmployeVisibleById($user, (int) $idEmploye);
            if ($employe) {
                BulletinPaie::genererBulletin($employe->id_employe, $data['periode'], $employe->salaire_base, $user->idUser, $employe->id_compagnie);
                $nbGeneres++;
            }
        }

        Flash::set(
            $nbGeneres > 0
                ? ($nbGeneres > 1 ? "$nbGeneres bulletins générés avec succès." : 'Bulletin généré avec succès.')
                : 'Aucun employé valide trouvé pour la génération.',
            $nbGeneres > 0 ? 'success' : 'danger'
        );

        return redirect()->route('admin.salaire.liste-bulletins');
    }

    public function listeBulletins(): View
    {
        $user = Auth::guard('staff')->user();

        return view('admin.salaire.liste-bulletins', [
            'listeBulletins' => BulletinPaie::getBulletinsVisibles($user),
        ]);
    }

    public function telechargerBulletin(int $id): Response|RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        $bulletin = BulletinPaie::getBulletinVisibleById($user, $id);

        if (! $bulletin) {
            Flash::set('Bulletin introuvable.', 'danger');

            return redirect()->route('admin.salaire.liste-bulletins');
        }

        $logoPath = null;
        if (! empty($bulletin->logo)) {
            $candidat = public_path('images/logos/'.$bulletin->logo);
            if (is_file($candidat)) {
                $logoPath = $candidat;
            }
        }

        $html = view('admin.pdf.bulletin-paie', ['bulletin' => $bulletin, 'logoPath' => $logoPath])->render();

        $options = new \Dompdf\Options();
        $options->setChroot(public_path());
        $options->setIsRemoteEnabled(true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="bulletin_paie_'.$bulletin->periode.'.pdf"',
        ]);
    }
}
