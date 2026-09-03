<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Compagnie;
use App\Models\MessageContact;
use App\Models\Programme;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/site/Contact.php — page "À propos & Contact"
 * du site public.
 *
 * Le formulaire était historiquement décoratif (le legacy ne le traite pas non plus côté
 * serveur) ; il enregistre désormais réellement le message (table messages_contact,
 * visible depuis Configuration → Messages reçus) — décision du 2026-09-03, ce même
 * enregistrement alimente aussi le widget flottant "Besoin d'aide ?" (voir store()).
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

    public function store(Request $request): RedirectResponse
    {
        $compagnie = Compagnie::site();

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:150'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:2000'],
            'origine' => ['nullable', 'in:contact,aide'],
        ]);

        MessageContact::create([
            'id_compagnie' => $compagnie->id_compagnie,
            'nom' => $data['nom'],
            'email' => $data['email'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'message' => $data['message'],
            'origine' => $data['origine'] ?? 'contact',
        ]);

        Flash::set('Votre message a bien été envoyé, nous vous répondrons rapidement.', 'success');

        return back();
    }
}
