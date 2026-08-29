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
 *
 * Le site public est dédié à une seule compagnie (voir App\Models\Compagnie::site() /
 * config/site.php, décision du 2026-08-24) — cette page n'a donc plus de logique
 * multi-compagnies (catalogue, onglets par transporteur), contrairement à sa version
 * d'origine côté legacy et à ce port avant cette date.
 */
class HomeController extends Controller
{
    public function index(): View
    {
        $compagnie = Compagnie::site();

        // Une même destination desservie à plusieurs heures ne donne qu'une seule carte
        // (toutes les heures affichées dessus), avec le prix le plus bas parmi ces horaires.
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
        }

        foreach ($destinationsUniques as $d) {
            $d->heures = array_values(array_unique($d->heures));
            sort($d->heures);
        }

        $heroStats = [
            'destinations' => count($destinationsUniques),
            'clients' => Client::where('id_compagnie', $compagnie->id_compagnie)->count(),
            'trajets' => Programme::where('id_compagnie', $compagnie->id_compagnie)->count(),
        ];

        return view('site.home', [
            'compagnie' => $compagnie,
            'destinations' => array_values($destinationsUniques),
            'heroStats' => $heroStats,
            'villes' => Programme::villesDisponibles($compagnie->id_compagnie),
        ]);
    }
}
