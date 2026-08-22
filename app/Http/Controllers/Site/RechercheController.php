<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Compagnie;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/site/Recherche.php — moteur de recherche de
 * trajets, tous compagnies confondues.
 */
class RechercheController extends Controller
{
    public function index(Request $request): View
    {
        $depart = trim((string) $request->query('depart', ''));
        $destination = trim((string) $request->query('destination', ''));
        $idCompagnie = trim((string) $request->query('compagnie', ''));
        $date = trim((string) $request->query('date', ''));

        return view('site.recherche', [
            'resultats' => Programme::rechercher($depart, $destination, $idCompagnie),
            'depart' => $depart,
            'destination' => $destination,
            'idCompagnie' => $idCompagnie,
            'date' => $date,
            'compagnies' => Compagnie::orderBy('nom_compagnie')->get(),
            'villes' => Programme::villesDisponibles(),
        ]);
    }
}
