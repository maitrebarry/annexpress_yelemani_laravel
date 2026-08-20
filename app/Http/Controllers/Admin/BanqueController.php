<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BanqueService;
use App\Support\Flash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Banques.php. Gestion des comptes banque
 * de la compagnie — Admin/PDG uniquement (pas de permission dédiée en base, gating par
 * rôle comme dans le legacy ; chef_d_escale ne choisit un compte que depuis Dépôts_banque).
 */
class BanqueController extends Controller
{
    public function index(BanqueService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['Admin', 'PDG'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.home');
        }

        return view('admin.banque.index', [
            'listeBanques' => $service->getBanques($user->id_compagnie),
        ]);
    }

    public function store(Request $request, BanqueService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit !== 'Admin') {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.banque.index');
        }

        $resultat = $service->creerBanque($user->id_compagnie, $request->only(['nom', 'numero_compte']));
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.banque.index');
    }

    public function update(Request $request, int $id, BanqueService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit !== 'Admin') {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.banque.index');
        }

        $resultat = $service->modifierBanque($id, $user->id_compagnie, $request->only(['nom', 'numero_compte', 'statut']));
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.banque.index');
    }

    public function mouvements(int $id, BanqueService $service): JsonResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['Admin', 'PDG'], true)) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $banque = $service->getBanque($id, $user->id_compagnie);
        if (! $banque) {
            return response()->json(['error' => 'Compte banque introuvable.'], 404);
        }

        $mouvements = $service->getMouvementsBanque($id, $user->id_compagnie);

        return response()->json([
            'banque' => [
                'nom' => $banque->nom,
                'numero_compte' => $banque->numero_compte,
                'solde' => (float) $banque->solde,
                'statut' => $banque->statut,
            ],
            'mouvements' => $mouvements->map(fn ($m) => [
                'date' => optional($m->date_validation)->format('d/m/Y H:i'),
                'localite' => $m->localite,
                'numeroGare' => $m->numeroGare,
                'montant' => (float) $m->montant,
                'reference' => $m->reference,
                'demandeur' => $m->demandeur,
                'validateur' => $m->validateur,
            ]),
        ]);
    }
}
