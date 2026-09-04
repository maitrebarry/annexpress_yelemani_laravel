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

        // Camions actifs de la compagnie, sans notion de programmation du jour
        // (contrairement aux cars ci-dessus) : voir GESTION_CAMIONS_COLIS.md.
        $listeCamions = $this->service->getCamionsActifs($user->id_compagnie);

        $idCar = $request->query('id_car');
        $carSelectionne = $idCar ? $this->service->getCarById((int) $idCar, $user->id_compagnie) : null;

        $idCamion = $request->query('id_camion');
        $camionSelectionne = $idCamion ? $this->service->getCamionById((int) $idCamion, $user->id_compagnie) : null;

        return view('admin.colis.envoi.create', [
            'listeColis' => $listeColis,
            'listeCars' => $listeCars,
            'listeCamions' => $listeCamions,
            'carSelectionne' => $carSelectionne,
            'camionSelectionne' => $camionSelectionne,
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
        $idCamion = $request->input('id_camion_selectionner');

        if (! empty($colisIds) && ! empty($idCamion)) {
            $this->service->traiterEnvoiCamion($colisIds, (int) $idCamion, $user->id_compagnie);
            Flash::set('Colis envoyés avec succès', 'primary');
        } elseif (! empty($colisIds) && ! empty($idCar)) {
            $this->service->traiterEnvoi1($colisIds, (int) $idCar, $user->id_compagnie);
            Flash::set('Colis envoyés avec succès', 'primary');
        } else {
            Flash::set('Veuillez sélectionner au moins un colis et un véhicule (car ou camion).', 'danger');
        }

        return redirect()->route('admin.colis.envoi.create');
    }

    public function index(): View
    {
        $user = Auth::guard('staff')->user();

        // Discriminant calculé : numero_car/numero_camion stockent en réalité un
        // id_car/id_camion (nom trompeur, convention historique) ; l'un des deux est
        // toujours NULL selon que le lot a été envoyé par car ou par camion — voir
        // GESTION_CAMIONS_COLIS.md.
        $listeColisEnvoyer = LigneEnvoi::where('id_compagnie', $user->id_compagnie)
            ->selectRaw("*, CASE WHEN numero_car IS NOT NULL THEN 'car' ELSE 'camion' END AS type_vehicule, COALESCE(numero_car, numero_camion) AS id_vehicule")
            ->orderByDesc('id_ligne_envoi')
            ->limit(10)
            ->get();

        return view('admin.colis.envoi.index', [
            'listeColisEnvoyer' => $listeColisEnvoyer,
        ]);
    }

    public function details(Request $request): View|RedirectResponse
    {
        // type=camion pour un lot camion ; repli sur 'car' par défaut (compat. avec
        // d'anciens liens ?id_car=...&date=... générés avant l'ajout des camions).
        $type = $request->query('type', 'car');
        $idVehicule = $request->query('id_vehicule', $request->query('id_car'));

        if (! $idVehicule || ! $request->filled('date')) {
            Flash::set('Aucun véhicule sélectionné', 'danger');

            return redirect()->route('admin.colis.envoi.index');
        }

        $user = Auth::guard('staff')->user()->load('agence');
        $idVehicule = (int) $idVehicule;
        $dateEnvoi = (string) $request->query('date');

        if ($type === 'camion') {
            $listeColis = $this->service->getColisParCamionEtDate($idVehicule, $dateEnvoi);
            $listeVehicules = $this->service->getCamionsActifs($user->id_compagnie);
        } else {
            $listeColis = $this->service->getColisParCarEtDate($idVehicule, $dateEnvoi);
            $listeVehicules = $this->service->getCarsDisponiblesAujourdhui(
                $user->id_compagnie,
                $user->droit,
                $user->agence?->localite,
                $user->id_agence
            );
        }

        return view('admin.colis.envoi.details', [
            'listeColis' => $listeColis,
            'typeVehicule' => $type,
            'idVehicule' => $idVehicule,
            'dateEnvoi' => $dateEnvoi,
            'listeVehicules' => $listeVehicules,
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

    // Déplace un colis déjà envoyé vers un autre camion (miroir de changerCar()). Le
    // changement reste intra-type — voir GESTION_CAMIONS_COLIS.md.
    public function changerCamion(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit === 'PDG') {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.colis.envoi.index');
        }

        if (! $request->filled('id_colis') || ! $request->filled('ancien_id_camion')
            || ! $request->filled('ancienne_date') || ! $request->filled('nouveau_id_camion')) {
            Flash::set('Données invalides pour le changement de camion.', 'danger');

            return redirect()->route('admin.colis.envoi.index');
        }

        $ok = $this->service->changerCamionColis(
            (int) $request->input('id_colis'),
            (int) $request->input('ancien_id_camion'),
            (string) $request->input('ancienne_date'),
            (int) $request->input('nouveau_id_camion'),
            $user->id_compagnie
        );

        Flash::set(
            $ok ? "Le camion d'envoi du colis a été modifié avec succès." : 'Erreur lors du changement de camion.',
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

        $type = $request->query('type', 'car');
        $idVehicule = $request->query('id_vehicule', $request->query('id_car'));

        if (! $idVehicule || ! $request->filled('date')) {
            Flash::set('Aucun envoi sélectionné.', 'danger');

            return redirect()->route('admin.colis.envoi.index');
        }

        $ok = $type === 'camion'
            ? $this->service->annulerEnvoiCamion((int) $idVehicule, (string) $request->query('date'), $user->id_compagnie)
            : $this->service->annulerEnvoi((int) $idVehicule, (string) $request->query('date'), $user->id_compagnie);

        Flash::set(
            $ok ? "L'envoi a été annulé, les colis sont de nouveau disponibles." : "Erreur lors de l'annulation de l'envoi.",
            $ok ? 'success' : 'danger'
        );

        return redirect()->route('admin.colis.envoi.index');
    }
}
