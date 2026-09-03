<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Billet;
use App\Models\Compagnie;
use Illuminate\Http\Request;
use Illuminate\View\View;

// "Mon compte" : pas de vrai compte/mot de passe (décision du 2026-09-03) — juste un
// moyen de retrouver un billet déjà réservé, sur le même principe que le suivi de colis
// (numéro + une donnée de vérification, ici le téléphone saisi à la réservation).
class MonCompteController extends Controller
{
    public function index(Request $request): View
    {
        $compagnie = Compagnie::site();
        $erreur = null;

        if ($request->filled('numero') && $request->filled('telephone')) {
            $numero = trim((string) $request->query('numero'));
            $telephoneSaisi = preg_replace('/\D+/', '', (string) $request->query('telephone'));

            $billet = Billet::with('client')
                ->where('numeroBillets', $numero)
                ->where('id_compagnie', $compagnie->id_compagnie)
                ->first();

            $telephoneClient = $billet?->client
                ? preg_replace('/\D+/', '', (string) $billet->client->numeroClient)
                : null;

            if ($billet && $telephoneClient !== null && $telephoneClient !== '' && $telephoneClient === $telephoneSaisi) {
                return redirect()->route('site.billet', $billet->numeroBillets);
            }

            $erreur = "Aucun billet ne correspond à ce numéro et à ce téléphone.";
        }

        return view('site.mon-compte', [
            'compagnie' => $compagnie,
            'erreur' => $erreur,
            'numeroSaisi' => $request->query('numero'),
        ]);
    }
}
