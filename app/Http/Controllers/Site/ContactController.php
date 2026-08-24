<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Compagnie;
use App\Models\Programme;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/site/Contact.php — page "À propos & Contact"
 * du site public. Le formulaire de contact reste décoratif (le legacy ne le traite pas
 * non plus côté serveur) ; seuls les chiffres du bandeau sont dynamiques.
 *
 * Le site public est dédié à une seule compagnie (voir App\Models\Compagnie::site() /
 * config/site.php, décision du 2026-08-24) : les statistiques sont scopées à cette
 * compagnie plutôt qu'agrégées sur toutes.
 */
class ContactController extends Controller
{
    public function index(): View
    {
        $compagnie = Compagnie::site();

        $stats = [
            'destinations' => Programme::pourVitrine($compagnie->id_compagnie)->pluck('destinationLocalite')->unique()->count(),
            'trajets' => Programme::where('id_compagnie', $compagnie->id_compagnie)->count(),
            'clients' => Client::where('id_compagnie', $compagnie->id_compagnie)->count(),
        ];

        return view('site.contact', compact('stats', 'compagnie'));
    }
}
