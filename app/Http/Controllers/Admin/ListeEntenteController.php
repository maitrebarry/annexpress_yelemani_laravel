<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CaisseUtilisateurService;
use App\Services\ListeEntenteService;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Liste_ententes.php — validation des
 * réservations en ligne. Confirmation en modale sur la page liste (voir
 * admin/liste-entente/index.blade.php) plutôt que la page dédiée du legacy, même
 * traitement modal-first que le reste des écrans Billets (Annulation/Report).
 */
class ListeEntenteController extends Controller
{
    public function index(ListeEntenteService $service): View
    {
        $user = Auth::guard('staff')->user();

        return view('admin.liste-entente.index', [
            'liste' => $service->listeEnAttente($user),
        ]);
    }

    public function valider(Request $request, int $id, ListeEntenteService $service, CaisseUtilisateurService $caisseService): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->valider($user, $id, (string) $request->input('confirme', ''), $caisseService);

        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.entente.index');
    }
}
