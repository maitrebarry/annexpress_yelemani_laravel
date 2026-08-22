<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RapportBilletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Rapport_billets.php.
 */
class RapportBilletController extends Controller
{
    public function mensuel(RapportBilletService $service): View
    {
        $user = Auth::guard('staff')->user();
        $idCompagnie = $user->id_compagnie;
        $mois = now()->format('Y-m');

        return view('admin.rapport_billet.mensuel', [
            'totalPresentiel' => $service->getTotalParType($idCompagnie, 'presentiel'),
            'totalEnLigne' => $service->getTotalParType($idCompagnie, 'en_ligne'),
            'totalRepporte' => $service->getTotalParType($idCompagnie, 'repporte'),
            'statsParMois' => $service->getParMois($idCompagnie),
            'billetsParGare' => $this->billetsParGare($service, $user, $mois),
        ]);
    }

    public function annuel(RapportBilletService $service): View
    {
        $user = Auth::guard('staff')->user();
        $idCompagnie = $user->id_compagnie;
        $annee = (int) now()->format('Y');

        return view('admin.rapport_billet.annuel', [
            'annee' => $annee,
            'totalPresentiel' => $service->getTotalParTypeAnnuel($idCompagnie, 'presentiel', $annee),
            'totalEnLigne' => $service->getTotalParTypeAnnuel($idCompagnie, 'en_ligne', $annee),
            'totalRepporte' => $service->getTotalParTypeAnnuel($idCompagnie, 'repporte', $annee),
            'statsParAnnee' => $service->getParAnnee($idCompagnie, $annee),
            'billetsParGare' => $this->billetsParGareAnnuel($service, $user, $annee),
        ]);
    }

    private function billetsParGare(RapportBilletService $service, $user, string $mois)
    {
        if (in_array($user->droit, ['Admin', 'PDG'], true)) {
            return $service->getSommeBilletsParLocaliteEtGare($user->id_compagnie, null, $mois);
        }

        if ($user->droit === 'chef_d_escale') {
            return $service->getSommeBilletsParLocaliteEtGare($user->id_compagnie, $user->agence?->localite, $mois);
        }

        return collect();
    }

    private function billetsParGareAnnuel(RapportBilletService $service, $user, int $annee)
    {
        if (in_array($user->droit, ['Admin', 'PDG'], true)) {
            return $service->getSommeBilletsParLocaliteEtGareAnnuel($user->id_compagnie, null, $annee);
        }

        if ($user->droit === 'chef_d_escale') {
            return $service->getSommeBilletsParLocaliteEtGareAnnuel($user->id_compagnie, $user->agence?->localite, $annee);
        }

        return collect();
    }
}
