<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Depense;
use App\Services\DepenseService;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Depenses.php. Réservé à
 * Admin/chef_d_escale/PDG (même audience que la sidebar) — permission Depenses_gestion
 * seule ne suffit pas, sinon un Utilisateur simple à qui elle serait assignée par erreur
 * y aurait quand même accès.
 */
class DepenseController extends Controller
{
    public function index(DepenseService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['Admin', 'chef_d_escale', 'PDG'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.home');
        }

        return view('admin.depense.index', [
            'listeDepenses' => $service->getDepenses($user),
            'listeAgences' => $user->droit === 'Admin' ? Agence::where('id_compagnie', $user->id_compagnie)->orderBy('localite')->get() : collect(),
            'categories' => Depense::CATEGORIES,
        ]);
    }

    public function store(Request $request, DepenseService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit === 'PDG') {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.depense.index');
        }

        $resultat = $service->saveDepense($user, $request->only(['portee', 'id_agence', 'categorie', 'libelle', 'montant', 'date_depense']));
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.depense.index');
    }

    public function benefice(Request $request, DepenseService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['Admin', 'PDG'], true)) {
            Flash::set("Accès réservé à l'Admin de la compagnie.", 'danger');

            return redirect()->route('admin.home');
        }

        $periode = in_array($request->query('periode'), ['jour', 'mois', 'tout'], true) ? $request->query('periode') : 'jour';

        return view('admin.depense.benefice', [
            'periode' => $periode,
            'benefice' => $service->getBenefice($user->id_compagnie, $periode),
        ]);
    }

    public function valider(int $id, DepenseService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit !== 'Admin') {
            Flash::set("Accès réservé à l'Admin de la compagnie.", 'danger');

            return redirect()->route('admin.home');
        }

        $resultat = $service->validerDepense($id, $user->id_compagnie);
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.depense.index');
    }

    public function rejeter(int $id, DepenseService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit !== 'Admin') {
            Flash::set("Accès réservé à l'Admin de la compagnie.", 'danger');

            return redirect()->route('admin.home');
        }

        $resultat = $service->rejeterDepense($id, $user->id_compagnie);
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.depense.index');
    }
}
