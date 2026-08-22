<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Compagnie;
use App\Models\Programme;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/site/Compagnies.php — catalogue public des
 * compagnies partenaires, avec de vrais chiffres (trajets/destinations) au lieu des
 * rand() fictifs du legacy.
 */
class CompagnieController extends Controller
{
    public function index(): View
    {
        $compagnies = Compagnie::orderBy('nom_compagnie')->get();

        $statsParCompagnie = [];
        foreach ($compagnies as $compagnie) {
            $programmes = Programme::pourVitrine($compagnie->id_compagnie);

            $statsParCompagnie[$compagnie->id_compagnie] = [
                'trajets' => $programmes->count(),
                'destinations' => $programmes->pluck('destinationLocalite')->unique()->count(),
            ];
        }

        return view('site.compagnies', compact('compagnies', 'statsParCompagnie'));
    }

    // Port de Projets_licence/app/controllers/site/Programmer.php::show() — tous les
    // trajets programmés d'une compagnie. Le legacy encodait l'id en base64 dans l'URL ;
    // inutile ici, ce n'est pas une donnée sensible, on utilise le binding de route standard.
    public function show(Compagnie $compagnie): View
    {
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
