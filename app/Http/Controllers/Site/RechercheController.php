<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Compagnie;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/site/Recherche.php — moteur de recherche de
 * trajets.
 *
 * Le site public est dédié à une seule compagnie (voir App\Models\Compagnie::site() /
 * config/site.php, décision du 2026-08-24) : le filtre compagnie n'est donc plus lu depuis
 * la requête (il n'y a rien d'autre à choisir), il est forcé côté serveur.
 */
class RechercheController extends Controller
{
    public function index(Request $request): View
    {
        $compagnie = Compagnie::site();

        $depart = trim((string) $request->query('depart', ''));
        $destination = trim((string) $request->query('destination', ''));
        $date = trim((string) $request->query('date', ''));

        return view('site.recherche', [
            'resultats' => Programme::rechercher($depart, $destination, (string) $compagnie->id_compagnie),
            'depart' => $depart,
            'destination' => $destination,
            'date' => $date,
            'compagnie' => $compagnie,
            'villes' => Programme::villesDisponibles($compagnie->id_compagnie),
        ]);
    }
}
