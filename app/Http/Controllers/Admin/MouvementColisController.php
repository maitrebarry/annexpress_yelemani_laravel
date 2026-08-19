<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Colis;
use App\Models\Utilisateur;
use App\Support\Flash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Mouvement_colis.php +
 * app/models/Mouvements_colis.php.
 */
class MouvementColisController extends Controller
{
    public function index(): View
    {
        $user = Auth::guard('staff')->user()->load('agence');

        return view('admin.colis.mouvement.index', [
            'listeColis' => $this->colisParStatut($user, 'en_cours'),
            'listeColisRecue' => $this->colisParStatut($user, 'recu', avecNumeroGareRetrait: true),
            'listeColisLivre' => $this->colisParStatut($user, 'livre'),
        ]);
    }

    public function receive(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        // Le garde-fou central legacy (App::isEcritureBloqueePourPDG()) bloque toute requête
        // POST pour un PDG avant même d'atteindre le contrôleur — répliqué ici explicitement,
        // avec le même message et la même redirection que ce garde-fou central.
        if ($user->droit === 'PDG') {
            Flash::set('Votre rôle est en lecture seule : vous ne pouvez ni créer ni modifier de données.', 'danger');

            return redirect()->route('admin.home');
        }

        $ids = array_map('intval', (array) $request->input('selected_colis', []));

        if (empty($ids)) {
            Flash::set('Aucun colis sélectionné !', 'danger');

            return redirect()->route('admin.colis.mouvement.index');
        }

        Colis::whereIn('id_colis', $ids)
            ->where('id_compagnie', $user->id_compagnie)
            ->update(['status' => 'recu']);

        Flash::set(
            "Colis marqués « reçu ». Utilisez le bouton WhatsApp dans l'onglet « Colis reçu » pour notifier chaque destinataire.",
            'success'
        );

        return redirect()->route('admin.colis.mouvement.index');
    }

    private function colisParStatut(Utilisateur $user, string $status, bool $avecNumeroGareRetrait = false): Collection
    {
        $query = Colis::query()
            ->avecDetails()
            ->visibleALaLivraison($user)
            ->where('colis.status', $status);

        if ($avecNumeroGareRetrait) {
            $query->addSelect('a.numeroGare as numero_gare_retrait');
        }

        return $query->orderByDesc('colis.id_colis')->get();
    }
}
