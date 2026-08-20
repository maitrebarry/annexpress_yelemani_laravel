<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Services\BanqueService;
use App\Services\DepotBanqueService;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Depots_banque.php. Voir
 * App\Services\DepotBanqueService pour la logique métier et le calcul du solde
 * disponible (adapté, la caisse de gare legacy n'existant pas dans cette app).
 */
class DepotBanqueController extends Controller
{
    public function index(BanqueService $banqueService, DepotBanqueService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['chef_d_escale', 'Admin', 'PDG'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.home');
        }

        $idAgence = $user->droit === 'chef_d_escale' ? $user->id_agence : null;

        return view('admin.depot-banque.index', [
            'listeBanques' => $banqueService->getBanquesActives($user->id_compagnie),
            'listeAgences' => $user->droit === 'Admin' ? Agence::where('id_compagnie', $user->id_compagnie)->orderBy('localite')->get() : collect(),
            'listeDemandes' => $service->getHistorique($user),
            'soldeDisponible' => $idAgence ? $service->soldeDisponible($idAgence) : null,
        ]);
    }

    public function store(Request $request, DepotBanqueService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit === 'PDG') {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.depot-banque.index');
        }

        $resultat = $service->creerDemande($user, $request->only(['id_agence', 'id_banque', 'montant', 'reference']));
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.depot-banque.index');
    }

    public function enAttente(DepotBanqueService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['Admin', 'PDG'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.home');
        }

        return view('admin.depot-banque.en-attente', [
            'listeDemandes' => $service->getDemandesEnAttente($user->id_compagnie),
        ]);
    }

    public function confirmer(int $id, DepotBanqueService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->confirmerDemande($id, $user);
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.depot-banque.en-attente');
    }

    public function rejeter(Request $request, int $id, DepotBanqueService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->rejeterDemande($id, $user, $request->input('motif_rejet'));
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.depot-banque.en-attente');
    }

    public function historique(DepotBanqueService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['chef_d_escale', 'Admin', 'PDG'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.home');
        }

        return view('admin.depot-banque.historique', [
            'listeDemandes' => $service->getHistorique($user),
        ]);
    }
}
