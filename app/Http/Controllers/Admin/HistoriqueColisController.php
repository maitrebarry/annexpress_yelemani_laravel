<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Colis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Historiques.php +
 * app/views/admin/{historique_colis_enregistrer,historique_colis_livre}.view.php.
 * Consolidé en une seule page à deux onglets (même traitement que Cars & Chauffeurs et
 * Billets Liste/Historique) au lieu des deux pages séparées du legacy. Le filtrage par date se
 * fait par rechargement GET classique (même convention que admin/billet/historique.blade.php)
 * plutôt que le va-et-vient jQuery AJAX du legacy qui réécrivait le tbody à la main.
 */
class HistoriqueColisController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::guard('staff')->user()->load('agence');

        $dateDebut = $request->query('date_debut');
        $dateFin = $request->query('date_fin');
        $filtrer = $dateDebut && $dateFin;

        $enregistres = Colis::query()
            ->avecDetails()
            ->visiblePar($user)
            ->when($filtrer, fn ($q) => $q->whereBetween('colis.date_enregistrement', [$dateDebut, $dateFin]))
            ->orderByDesc('colis.id_colis')
            ->get();

        $livres = Colis::query()
            ->avecDetails()
            ->visibleALaLivraison($user)
            ->where('colis.status', 'livre')
            ->when($filtrer, fn ($q) => $q->whereBetween('colis.date_enregistrement', [$dateDebut, $dateFin]))
            ->orderByDesc('colis.id_colis')
            ->get();

        return view('admin.colis.historique.index', [
            'enregistres' => $enregistres,
            'livres' => $livres,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
        ]);
    }
}
