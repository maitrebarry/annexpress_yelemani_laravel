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
 */
class ContactController extends Controller
{
    public function index(): View
    {
        $compagnies = Compagnie::orderBy('nom_compagnie')->get();

        $destinationsGlobales = collect();
        foreach ($compagnies as $compagnie) {
            $destinationsGlobales = $destinationsGlobales->merge(
                Programme::pourVitrine($compagnie->id_compagnie)->pluck('destinationLocalite')
            );
        }

        $stats = [
            'destinations' => $destinationsGlobales->unique()->count(),
            'compagnies' => $compagnies->count(),
            'clients' => Client::count(),
        ];

        return view('site.contact', compact('stats'));
    }
}
