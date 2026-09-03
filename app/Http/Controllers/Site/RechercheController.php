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
        $dateRetour = trim((string) $request->query('date_retour', ''));

        // "Aller-retour" : pas de billet combiné, juste une seconde recherche affichée à
        // côté (trajet retour = destination -> départ), réservable indépendamment via la
        // même modale — le legacy ne gère pas non plus les billets aller-retour combinés.
        $resultatsRetour = $dateRetour !== ''
            ? Programme::rechercher($destination, $depart, (string) $compagnie->id_compagnie)
            : null;

        return view('site.recherche', [
            'resultats' => Programme::rechercher($depart, $destination, (string) $compagnie->id_compagnie),
            'resultatsRetour' => $resultatsRetour,
            'depart' => $depart,
            'destination' => $destination,
            'date' => $date,
            'dateRetour' => $dateRetour,
            'compagnie' => $compagnie,
            'villes' => Programme::villesDisponibles($compagnie->id_compagnie),
        ]);
    }
}
