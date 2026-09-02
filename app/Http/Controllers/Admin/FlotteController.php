<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgrammationVoyage;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Flotte.php +
 * ProgrammationVoyage::getEtatFlotte() (app/models/Programmation_voyage.php). Vue de
 * supervision globale de tous les cars de la compagnie (disponibles ou en transit) —
 * contrairement au dashboard "Trajets programmés" qui ne montre qu'un sous-ensemble filtré
 * (cars en transit décollés, cars bloqués) pour le suivi opérationnel du jour. Écran
 * purement en lecture, aucune action.
 *
 * Réservé Admin/super_admin/PDG, comme dans le legacy — gardé en vérification de rôle
 * explicite plutôt qu'un `permission:` de catalogue, car ce garde-fou n'a jamais eu de
 * permission dédiée côté legacy non plus.
 */
class FlotteController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['Admin', 'super_admin', 'PDG', 'secretaire'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.home');
        }

        return view('admin.flotte.index', [
            'cars' => ProgrammationVoyage::etatFlotte($user->id_compagnie),
        ]);
    }
}
