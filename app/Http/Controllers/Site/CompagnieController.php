<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Compagnie;
use App\Models\Programme;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/site/Compagnies.php — catalogue public des
 * compagnies partenaires.
 *
 * Le site public est désormais dédié à une seule compagnie (voir
 * App\Models\Compagnie::site() / config/site.php, décision du 2026-08-24) : le catalogue
 * multi-compagnies n'a donc plus lieu d'être — index() redirige vers la page de la
 * compagnie unique (garde le nom de route/URL `/compagnies` valide pour tous les liens
 * existants) et show() refuse l'accès à toute autre compagnie.
 */
class CompagnieController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('site.compagnie.trajets', Compagnie::site());
    }

    // Port de Projets_licence/app/controllers/site/Programmer.php::show() — tous les
    // trajets programmés d'une compagnie. Le legacy encodait l'id en base64 dans l'URL ;
    // inutile ici, ce n'est pas une donnée sensible, on utilise le binding de route standard.
    public function show(Compagnie $compagnie): View
    {
        abort_unless($compagnie->id_compagnie === Compagnie::site()->id_compagnie, 404);

        $programmes = Programme::pourCompagnieAvecEscales($compagnie->id_compagnie);

        $villesDepart = $programmes->pluck('departLocalite')->filter()->unique()->sort()->values();
        $villesDestination = $programmes->pluck('destinationLocalite')->filter()->unique()->sort()->values();
        $nbAgences = $villesDepart->merge($villesDestination)->unique()->count();

        return view('site.compagnie-trajets', [
            'compagnie' => $compagnie,
            'programmes' => $programmes,
            'programmesParDepart' => $programmes->groupBy(fn ($p) => $p->departLocalite.' ('.$p->numeroGare1.')'),
            'villesDepart' => $villesDepart,
            'villesDestination' => $villesDestination,
            'nbAgences' => $nbAgences,
        ]);
    }
}
