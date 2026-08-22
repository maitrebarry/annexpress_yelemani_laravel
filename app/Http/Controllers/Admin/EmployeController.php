<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EmployeService;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Employes.php.
 */
class EmployeController extends Controller
{
    public function index(EmployeService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $peutVoirUtilisateurs = $user->userHasPermission('utilisateur_apercu');
        $peutVoirChauffeurs = $user->userHasPermission('Configuration_gestion_car/chauffeur');

        if (! $peutVoirUtilisateurs && ! $peutVoirChauffeurs) {
            Flash::set("Accès refusé : vous n'avez pas la permission nécessaire.", 'danger');

            return redirect()->route('admin.home');
        }

        return view('admin.employe.index', [
            'employes' => $service->buildListe($user, $peutVoirUtilisateurs, $peutVoirChauffeurs),
            'peutVoirUtilisateurs' => $peutVoirUtilisateurs,
            'peutVoirChauffeurs' => $peutVoirChauffeurs,
        ]);
    }

    // Liste imprimable (HTML + window.print(), pas de PDF — dompdf non installé dans
    // cette app, même écart déjà assumé pour la facture de location/le reçu de billet).
    public function listeImprimable(EmployeService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $peutVoirUtilisateurs = $user->userHasPermission('utilisateur_apercu');
        $peutVoirChauffeurs = $user->userHasPermission('Configuration_gestion_car/chauffeur');

        if (! $peutVoirUtilisateurs && ! $peutVoirChauffeurs) {
            Flash::set("Accès refusé : vous n'avez pas la permission nécessaire.", 'danger');

            return redirect()->route('admin.home');
        }

        return view('admin.employe.liste-imprimable', [
            'employes' => $service->buildListe($user, $peutVoirUtilisateurs, $peutVoirChauffeurs),
            'compagnie' => $user->compagnie,
        ]);
    }

    public function printCard(string $type, int $id, EmployeService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $employe = $service->resolveEmploye($type, $id, $user);
        if (! $employe) {
            Flash::set('Employé introuvable.', 'danger');

            return redirect()->route('admin.employe.index');
        }

        // Seul le format 1 (Corporate Horizontal) existe pour l'instant, comme le legacy.
        return view('admin.employe.print-card', [
            'employes' => [$employe],
            'format' => 1,
            'compagnie' => $user->compagnie,
        ]);
    }

    // Impression groupée : depuis la sélection multiple de la liste des employés,
    // imprime jusqu'à 4 badges par feuille A4 (pagination automatique au-delà).
    public function printSelection(Request $request, EmployeService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $selection = $request->input('selection', []);
        $format = 1;

        $employes = [];
        if (is_array($selection)) {
            foreach ($selection as $item) {
                if (! is_string($item) || ! str_contains($item, ':')) {
                    continue;
                }
                [$type, $id] = explode(':', $item, 2);
                if (! in_array($type, ['Utilisateur', 'Chauffeur'], true) || ! ctype_digit((string) $id)) {
                    continue;
                }
                $employe = $service->resolveEmploye($type, (int) $id, $user);
                if ($employe) {
                    $employes[] = $employe;
                }
            }
        }

        if (empty($employes)) {
            Flash::set('Aucun employé sélectionné.', 'danger');

            return redirect()->route('admin.employe.index');
        }

        return view('admin.employe.print-card', [
            'employes' => $employes,
            'format' => $format,
            'compagnie' => $user->compagnie,
        ]);
    }
}
