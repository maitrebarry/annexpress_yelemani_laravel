<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Compagnie;
use App\Models\Programme;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/site/Accueil.php — page d'accueil du site
 * public (vitrine, pas d'authentification requise).
 */
class HomeController extends Controller
{
    public function index(): View
    {
        $compagnies = Compagnie::orderBy('nom_compagnie')->get();

        // Un onglet "Destinations populaires" par compagnie : une même destination
        // desservie à plusieurs heures ne donne qu'une seule carte (toutes les heures
        // affichées dessus), avec le prix le plus bas parmi ces horaires.
        $programmesParCompagnie = [];
        $destinationsGlobales = [];
        foreach ($compagnies as $compagnie) {
            $destinationsUniques = [];
            foreach (Programme::pourVitrine($compagnie->id_compagnie) as $p) {
                $cle = $p->departLocalite.'→'.$p->destinationLocalite;
                if (! isset($destinationsUniques[$cle])) {
                    $destinationsUniques[$cle] = (object) [
                        'departLocalite' => $p->departLocalite,
                        'destinationLocalite' => $p->destinationLocalite,
                        'prix' => $p->prix,
                        'heures' => [$p->heureDepart],
                    ];
                } else {
                    $destinationsUniques[$cle]->heures[] = $p->heureDepart;
                    if ($p->prix < $destinationsUniques[$cle]->prix) {
                        $destinationsUniques[$cle]->prix = $p->prix;
                    }
                }
                $destinationsGlobales[$p->destinationLocalite] = true;
            }

            foreach ($destinationsUniques as $d) {
                $d->heures = array_values(array_unique($d->heures));
                sort($d->heures);
            }

            $programmesParCompagnie[$compagnie->id_compagnie] = array_values($destinationsUniques);
        }

        $heroStats = [
            'destinations' => count($destinationsGlobales),
            'compagnies' => $compagnies->count(),
            'clients' => Client::count(),
            'trajets' => Programme::count(),
        ];

        // Slider du hero : toutes les images de public/assets_site/img/hero-slides/,
        // dans l'ordre alphabétique. Déposer un nouveau fichier suffit à l'ajouter.
        $heroSlides = collect(glob(public_path('assets_site/img/hero-slides/*.{jpg,jpeg,png,webp}'), GLOB_BRACE))
            ->sort()
            ->map(fn (string $path) => asset('assets_site/img/hero-slides/'.basename($path)))
            ->values();

        return view('site.home', [
            'compagnies' => $compagnies,
            'programmesParCompagnie' => $programmesParCompagnie,
            'heroStats' => $heroStats,
            'villes' => Programme::villesDisponibles(),
            'heroSlides' => $heroSlides,
        ]);
    }
}
