<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Colis;
use App\Models\LigneEnvoi;
use App\Services\EnvoiColisService;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Envoi_colis.php + app/models/Envoie_colis.php.
 * Seules les actions réellement reliées à un lien (sidebar/vues) sont portées : l'action
 * index() legacy (et sa vue envoi_colis.view.php) est du code mort, jamais liée nulle part.
 */
class EnvoiColisController extends Controller
{
    public function __construct(private readonly EnvoiColisService $service)
    {
    }

    public function create(Request $request): View
    {
        $user = Auth::guard('staff')->user()->load('agence');

        $listeColis = Colis::query()
            ->visiblePar($user)
            ->avecDetails()
            ->orderByDesc('colis.id_colis')
            ->get();

        $listeCars = $this->service->getCarsDisponiblesAujourdhui(
            $user->id_compagnie,
            $user->droit,
            $user->agence?->localite,
            $user->id_agence
        );

        $idCar = $request->query('id_car');
        $carSelectionne = $idCar ? $this->service->getCarById((int) $idCar, $user->id_compagnie) : null;

        return view('admin.colis.envoi.create', [
            'listeColis' => $listeColis,
            'listeCars' => $listeCars,
            'carSelectionne' => $carSelectionne,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit === 'PDG') {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.colis.envoi.create');
        }

        $colisIds = array_map('intval', (array) $request->input('selected_colis', []));
        $idCar = $request->input('id_car_selectionner');

        if (! empty($colisIds) && $idCar) {
            $this->service->traiterEnvoi1($colisIds, (int) $idCar, $user->id_compagnie);
            Flash::set('Colis envoyés avec succès', 'primary');
        } else {
            Flash::set('Veuillez sélectionner au moins un colis et un car.', 'danger');
        }

        return redirect()->route('admin.colis.envoi.create');
    }

    public function index(): View
    {
        $user = Auth::guard('staff')->user();

        $listeColisEnvoyer = LigneEnvoi::where('id_compagnie', $user->id_compagnie)
            ->orderByDesc('id_ligne_envoi')
            ->limit(10)
            ->get();

        return view('admin.colis.envoi.index', [
            'listeColisEnvoyer' => $listeColisEnvoyer,
        ]);
    }

    public function details(Request $request): View|RedirectResponse
    {
        if (! $request->filled('id_car') || ! $request->filled('date')) {
            Flash::set('Aucun car sélectionné', 'danger');

            return redirect()->route('admin.colis.envoi.index');
        }

        $user = Auth::guard('staff')->user()->load('agence');
        $idCar = (int) $request->query('id_car');
        $dateEnvoi = (string) $request->query('date');

        $listeColis = $this->service->getColisParCarEtDate($idCar, $dateEnvoi);
        $listeCars = $this->service->getCarsDisponiblesAujourdhui(
            $user->id_compagnie,
            $user->droit,
            $user->agence?->localite,
            $user->id_agence
        );

        return view('admin.colis.envoi.details', [
            'listeColis' => $listeColis,
            'idCar' => $idCar,
            'dateEnvoi' => $dateEnvoi,
            'listeCars' => $listeCars,
        ]);
    }

    public function changerCar(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit === 'PDG') {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.colis.envoi.index');
        }

        if (! $request->filled('id_colis') || ! $request->filled('ancien_id_car')
            || ! $request->filled('ancienne_date') || ! $request->filled('nouveau_id_car')) {
            Flash::set('Données invalides pour le changement de car.', 'danger');

            return redirect()->route('admin.colis.envoi.index');
        }

        $ok = $this->service->changerCarColis(
            (int) $request->input('id_colis'),
            (int) $request->input('ancien_id_car'),
            (string) $request->input('ancienne_date'),
            (int) $request->input('nouveau_id_car'),
            $user->id_compagnie
        );

        Flash::set(
            $ok ? "Le car d'envoi du colis a été modifié avec succès." : 'Erreur lors du changement de car.',
            $ok ? 'success' : 'danger'
        );

        return redirect()->route('admin.colis.envoi.index');
    }

    public function annuler(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit === 'PDG') {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.colis.envoi.index');
        }

        if (! $request->filled('id_car') || ! $request->filled('date')) {
            Flash::set('Aucun envoi sélectionné.', 'danger');

            return redirect()->route('admin.colis.envoi.index');
        }

        $ok = $this->service->annulerEnvoi(
            (int) $request->query('id_car'),
            (string) $request->query('date'),
            $user->id_compagnie
        );

        Flash::set(
            $ok ? "L'envoi a été annulé, les colis sont de nouveau disponibles." : "Erreur lors de l'annulation de l'envoi.",
            $ok ? 'success' : 'danger'
        );

        return redirect()->route('admin.colis.envoi.index');
    }
}
