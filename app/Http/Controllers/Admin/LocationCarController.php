<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\LocationCar;
use App\Services\LocationCarService;
use App\Support\Flash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Locations_cars.php. Réservé à
 * Admin/chef_d_escale/PDG (même audience que la sidebar) — permission Location_gestion
 * seule ne suffit pas, sinon un Utilisateur simple à qui elle serait assignée par erreur
 * y aurait quand même accès.
 */
class LocationCarController extends Controller
{
    public function index(LocationCarService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['Admin', 'chef_d_escale', 'PDG'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.home');
        }

        return view('admin.location-car.index', [
            'listeLocations' => $service->getLocations($user),
            'listeAgences' => $user->droit === 'Admin' ? Agence::where('id_compagnie', $user->id_compagnie)->orderBy('localite')->get() : collect(),
        ]);
    }

    public function store(Request $request, LocationCarService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit === 'PDG') {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.location-car.index');
        }

        $resultat = $service->saveLocation($user, $request->only([
            'id_agence_depart', 'destination', 'id_car', 'nom_client', 'prenom_client',
            'telephone_client', 'date_depart', 'date_retour_prevu', 'frais_location',
        ]));

        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.location-car.index');
    }

    public function ajaxCarsDisponibles(Request $request, LocationCarService $service): JsonResponse
    {
        $user = Auth::guard('staff')->user();

        $idAgenceDepart = $user->droit === 'chef_d_escale' ? $user->id_agence : (int) $request->input('id_agence_depart');
        $dateDepart = (string) $request->input('date_depart');
        $dateRetourPrevu = (string) $request->input('date_retour_prevu');

        if (! $idAgenceDepart || ! $dateDepart || ! $dateRetourPrevu) {
            return response()->json(['error' => 'Gare de départ et dates requises.']);
        }
        if ($dateRetourPrevu < $dateDepart) {
            return response()->json(['error' => 'La date de retour prévu ne peut pas être avant la date de départ.']);
        }

        $limiterAGare = $user->droit === 'chef_d_escale' && $dateDepart === now()->toDateString();
        $cars = $service->carsDisponibles($user->id_compagnie, $idAgenceDepart, $dateDepart, $dateRetourPrevu, $limiterAGare);

        return response()->json([
            'cars' => $cars->map(fn ($c) => [
                'id_car' => (int) $c->id_car,
                'numero_car' => $c->numero_car,
                'matriculle' => $c->matriculle,
            ])->values(),
        ]);
    }

    public function valider(int $id, LocationCarService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit !== 'Admin') {
            Flash::set("Accès réservé à l'Admin de la compagnie.", 'danger');

            return redirect()->route('admin.home');
        }

        $resultat = $service->validerLocation($id, $user);
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.location-car.index');
    }

    public function rejeter(int $id, LocationCarService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit !== 'Admin') {
            Flash::set("Accès réservé à l'Admin de la compagnie.", 'danger');

            return redirect()->route('admin.home');
        }

        $resultat = $service->rejeterLocation($id, $user);
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.location-car.index');
    }

    // Facture imprimable (HTML + window.print(), pas de PDF — dompdf non installé dans
    // cette app, même écart déjà assumé pour le reçu de billet thermique).
    public function facture(int $id, LocationCarService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $location = $service->getById($id, $user);
        if (! $location) {
            Flash::set('Location introuvable.', 'danger');

            return redirect()->route('admin.location-car.index');
        }

        return view('admin.location-car.facture', [
            'location' => $location,
            'compagnie' => $user->compagnie,
        ]);
    }
}
