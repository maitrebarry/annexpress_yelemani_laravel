<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BilletService;
use App\Services\CaisseUtilisateurService;
use App\Models\Billet;
use App\Models\Compagnie;
use App\Support\Flash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Add_billets.php (création) +
 * une partie de app/controllers/admin/Liste_du_jours.php (index/historique/impression
 * thermique uniquement — voir App\Services\BilletService pour le détail du périmètre).
 */
class BilletController extends Controller
{
    public function create(BilletService $service): View
    {
        $user = Auth::guard('staff')->user();

        return view('admin.billet.create', $service->getFormData($user));
    }

    public function store(Request $request, BilletService $service, CaisseUtilisateurService $caisseService): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->creerReservation($user, $request->only([
            'idDepart', 'destinationId', 'Client', 'jourVoyage', 'programme', 'escale', 'nombrePassages',
        ]), $caisseService);

        Flash::set($resultat['message'], $resultat['type']);

        $params = [];
        if ($user->droit === 'Admin' && $request->filled('idDepart')) {
            $params['idDepart'] = $request->input('idDepart');
        }
        // Le ticket est la preuve de paiement : imprimé automatiquement dès le chargement
        // de la page de résultat (cf. thermal-print.js), sans clic supplémentaire.
        if ($resultat['ok']) {
            $params['billetImprime'] = $resultat['idBillet'];
        }

        return redirect()->route('admin.billet.create', $params);
    }

    public function index(Request $request, BilletService $service): View
    {
        $user = Auth::guard('staff')->user();

        // La réservation est possible jusqu'à J+config('billets.jours_reservation_avance')
        // (voir BilletService::creerReservation) : l'onglet "Autre jour" permet de retrouver
        // les billets pris pour n'importe quel jour de cette fenêtre, pas seulement demain.
        $joursAvance = (int) config('billets.jours_reservation_avance', 6);
        $dateMin = now()->addDay()->toDateString();
        $dateMax = now()->addDays($joursAvance)->toDateString();

        $dateAutre = $request->query('date', $dateMin);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateAutre) || $dateAutre < $dateMin || $dateAutre > $dateMax) {
            $dateAutre = $dateMin;
        }

        return view('admin.billet.index', [
            'listeAujourdhui' => $service->getListeParDate($user, now()->toDateString()),
            'listeAutreJour' => $service->getListeParDate($user, $dateAutre),
            'dateAutre' => $dateAutre,
            'dateMin' => $dateMin,
            'dateMax' => $dateMax,
        ]);
    }

    public function historique(Request $request, BilletService $service): View
    {
        $user = Auth::guard('staff')->user();

        $date = $request->query('date', now()->subDay()->toDateString());
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date > now()->toDateString()) {
            $date = now()->subDay()->toDateString();
        }

        $filtres = [
            'date' => $date,
            'destination' => $request->query('destination'),
            'heure' => $request->query('heure'),
        ];

        return view('admin.billet.historique', [
            'listeHistorique' => $service->getHistorique($user, $filtres),
            'destinations' => $service->getDestinationsPourFiltre($user),
            'date' => $date,
            'destinationSelectionnee' => $filtres['destination'],
            'heureSelectionnee' => $filtres['heure'],
        ]);
    }

    public function donneesTicketThermique(int $id, BilletService $service): JsonResponse
    {
        $user = Auth::guard('staff')->user();

        return response()->json($service->getDonneesTicketThermique($id, $user));
    }

    // chef_d_escale ne peut que demander l'annulation ; Admin annule directement. Tout autre
    // rôle (dont PDG) n'a droit à aucune des deux branches, comme dans le legacy.
    public function annuler(Request $request, BilletService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        $idBillets = (int) $request->input('idBillets');
        $motif = $request->input('motif_annulation');

        $resultat = match ($user->droit) {
            'chef_d_escale' => $service->demanderAnnulation($user, $idBillets, $motif),
            'Admin' => $service->annulerDirect($user, $idBillets, $motif),
            default => ['ok' => false, 'type' => 'danger', 'message' => "Vous n'avez pas le droit d'annuler un billet."],
        };

        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.billet.index');
    }

    public function demandesAnnulation(BilletService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['Admin', 'PDG', 'secretaire'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.home');
        }

        return view('admin.billet.demandes-annulation', [
            'listeDemandes' => $service->getDemandesAnnulationEnAttente($user->id_compagnie),
        ]);
    }

    public function confirmerAnnulation(int $id, BilletService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->confirmerAnnulation($user, $id);
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.billet.demandes-annulation');
    }

    public function rejeterAnnulation(int $id, BilletService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->rejeterAnnulation($user, $id);
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.billet.demandes-annulation');
    }

    public function reporter(Request $request, BilletService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->reporter(
            $user,
            (int) $request->input('idBillets'),
            (string) $request->input('nouvelle_date'),
            (string) $request->input('nouvelle_heure')
        );

        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.billet.index');
    }

    public function embarquement(Request $request, BilletService $service): View
    {
        $user = Auth::guard('staff')->user();

        $jour = $request->query('date', now()->toDateString());
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $jour)) {
            $jour = now()->toDateString();
        }
        $destination = $request->query('destination') ?: null;
        $heure = $request->query('heure') ?: null;

        return view('admin.billet.embarquement', [
            'liste' => $service->getBilletsPourEmbarquement($user, $jour, $destination, $heure),
            'carsComplets' => $service->getCarsComplets($user, $jour),
            'carsDuJour' => $service->getCarsDuJourPourEmbarquement($user, $jour),
            'destinations' => $service->getDestinationsPourFiltre($user),
            'date' => $jour,
            'destinationSelectionnee' => $destination,
            'heureSelectionnee' => $heure,
        ]);
    }

    public function decollerCar(Request $request, BilletService $service): JsonResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->decollerCar($user, (int) $request->input('idProgrammation'));

        return response()->json($resultat);
    }

    public function marquerEmbarque(Request $request, BilletService $service): JsonResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->embarquer($user, (int) $request->input('idBillets'));

        return response()->json(['ok' => $resultat['ok'], 'message' => $resultat['message']]);
    }

    public function annulerEmbarquement(Request $request, BilletService $service): JsonResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->annulerEmbarquement($user, (int) $request->input('idBillets'));

        return response()->json(['ok' => $resultat['ok'], 'message' => $resultat['message']]);
    }

    public function marquerEmbarqueLot(Request $request, BilletService $service): JsonResponse
    {
        $user = Auth::guard('staff')->user();
        $ids = (array) $request->input('idsBillets', []);

        if (empty($ids)) {
            return response()->json(['ok' => false, 'message' => 'Aucun billet sélectionné.']);
        }

        $resultat = $service->embarquerLot($user, $ids);

        return response()->json($resultat);
    }

    public function demanderReport(Request $request, BilletService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->demanderReport(
            $user,
            (int) $request->input('idBillets'),
            (string) $request->input('nouvelle_date'),
            (string) $request->input('nouvelle_heure')
        );

        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.billet.embarquement');
    }

    public function demandesReport(BilletService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (in_array($user->droit, ['Admin', 'PDG', 'secretaire'], true)) {
            return view('admin.billet.demandes-report', [
                'listeDemandes' => $service->getDemandesReportEnAttente($user->id_compagnie),
                'estAdmin' => true,
            ]);
        }

        if ($user->droit === 'chef_d_escale') {
            return view('admin.billet.demandes-report', [
                'listeDemandes' => $service->getDemandesReportEnAttenteChef($user),
                'estAdmin' => false,
            ]);
        }

        Flash::set('Accès refusé.', 'danger');

        return redirect()->route('admin.home');
    }

    public function transmettreReport(int $id, BilletService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->transmettreReport($user, $id);
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.billet.demandes-report');
    }

    public function confirmerReportDemande(int $id, BilletService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->confirmerReportDemande($user, $id);
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.billet.demandes-report');
    }

    public function rejeterReportDemande(int $id, BilletService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->rejeterReportDemande($user, $id);
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.billet.demandes-report');
    }

    public function recu(int $id)
    {
        $user = Auth::guard('staff')->user();

        $billet = Billet::query()
            ->join('client', 'billets.id_client', '=', 'client.idClient')
            ->leftJoin('utilisateur', 'utilisateur.idUser', '=', 'billets.idUser')
            ->where('billets.idBillets', $id)
            ->where('billets.id_compagnie', $user->id_compagnie)
            ->first(['billets.*', 'client.Client', 'client.montant_payer', 'utilisateur.utilisateurs']);

        $compagnie = Compagnie::find($user->id_compagnie);

        if (! $billet || ! $compagnie) {
            Flash::set('Billet ou compagnie introuvable.', 'danger');

            return redirect()->route('admin.billet.index');
        }

        $logoPath = null;
        if ($compagnie->logo) {
            $logoPath = public_path('images/logos/' . $compagnie->logo);
            if (! is_file($logoPath)) {
                $logoPath = null;
            }
        }

        $html = view('admin.pdf.ticket', compact('billet', 'compagnie', 'logoPath'))->render();

        $this->streamThermalPdf($html, "ticket_{$id}.pdf");
    }
}
