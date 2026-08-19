<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HomeStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Homes.php::home().
 */
class HomeController extends Controller
{
    public function __construct(private readonly HomeStatsService $stats)
    {
    }

    public function index(Request $request): View
    {
        $utilisateur = Auth::guard('staff')->user()->load('agence');
        $droit = $utilisateur->droit;

        if ($droit === 'super_admin') {
            return view('admin.home', [
                'mode' => 'plateforme',
                'platformStats' => $this->stats->getPlatformStats(),
                'compagniesOverview' => $this->stats->getCompagniesOverview(),
            ]);
        }

        $idCompagnie = $utilisateur->id_compagnie;
        $ville = $utilisateur->agence?->localite;
        $numGare = $utilisateur->agence?->numeroGare;

        $listeGares = collect();
        $gareId = null;
        $gareLabel = null;

        if ($droit === 'Admin') {
            $listeGares = $this->stats->getGaresCompagnie($idCompagnie);

            if ($request->filled('gare')) {
                foreach ($listeGares as $gare) {
                    if ((string) $gare->idAgence === (string) $request->query('gare')) {
                        $gareId = $gare->idAgence;
                        $gareLabel = $gare->localite;
                        break;
                    }
                }
            }
        }

        $profile = $utilisateur->profile;

        $today = now()->toDateString();
        $date = $request->query('date') ?? $today;
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date > $today) {
            $date = $today;
        }

        $showBillets = $droit !== 'Utilisateur' || $profile === 'billet';
        $showColis = $droit !== 'Utilisateur' || $profile === 'colis';
        $showVoyages = $droit !== 'Utilisateur';
        $showTopGares = in_array($droit, ['Admin', 'PDG'], true) && ! $gareId;

        $data = [
            'mode' => 'compagnie',
            'showBillets' => $showBillets,
            'showColis' => $showColis,
            'showVoyages' => $showVoyages,
            'showTopGares' => $showTopGares,
            'listeGares' => $listeGares,
            'gareId' => $gareId,
            'gareLabel' => $gareLabel,
            'date' => $date,
        ];

        if ($showBillets) {
            $data['billetsJour'] = $this->stats->getBilletsJournalier($droit, $idCompagnie, $ville, $utilisateur->idUser, $gareLabel, $date);
        }

        if ($showVoyages) {
            $data['voyagesJour'] = $this->stats->getVoyagesProgrammes($droit, $idCompagnie, $ville, $utilisateur->id_agence, $gareLabel, $gareId, $date);
        }

        if ($showColis) {
            $data['colisJour'] = $this->stats->getColisJournalier($droit, $idCompagnie, $ville, $numGare, $gareLabel, $date);
        }

        if ($showTopGares) {
            // Le legacy Home::getTopGares() ne calcule quelque chose que pour le rôle Admin
            // (retour [] pour PDG même si le panneau est théoriquement affichable pour ce rôle).
            $data['topGares'] = $droit === 'Admin' ? $this->stats->getTopGares($idCompagnie) : [];
        }

        if (in_array($droit, ['Admin', 'PDG'], true)) {
            $data['beneficeJour'] = $this->stats->getBeneficeJour($idCompagnie, $gareLabel, $date);
        }

        if ($droit === 'chef_d_escale') {
            $data['beneficeJour'] = $this->stats->getBeneficeJour($idCompagnie, $ville, $date);
            $data['caisseGare'] = $utilisateur->id_agence ? $this->stats->getCaisseGare($utilisateur->id_agence) : null;
        }

        $data['activiteRecente'] = $this->stats->getActiviteRecente($droit, $idCompagnie, $ville, $utilisateur->idUser, $profile, $gareLabel, 6);

        return view('admin.home', $data);
    }
}
