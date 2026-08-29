<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Billet;
use App\Models\Compagnie;
use App\Models\Programme;
use App\Services\ReservationEnLigneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/site/Reservation_formulaire.php — réservation
 * de billet en ligne (sans compte, sans opérateur/caisse). Le legacy affichait le
 * formulaire en pleine page ; ici le formulaire est un modal ouvert depuis Recherche/
 * Compagnie-trajets (voir site/partials/reservation-modal.blade.php, à la demande de
 * l'utilisateur — "je veux que le formulaire de reservation soit modal pour que ça soit
 * rapide"), donc index()/redirection PRG n'existent plus : `donnees()` alimente le
 * modal en AJAX et `store()` répond en JSON plutôt que par redirection.
 */
class ReservationController extends Controller
{
    // Données d'un trajet pour préremplir le modal de réservation (AJAX, voir
    // site/partials/reservation-modal.blade.php::openReservationModal()).
    public function donnees(int $id): JsonResponse
    {
        $trajet = Programme::trouverAvecEscales($id);

        // Le site public est dédié à une seule compagnie (voir App\Models\Compagnie::site() /
        // config/site.php) : un trajet d'une autre compagnie est traité comme introuvable,
        // même si son id existe en base (défense en profondeur — en pratique aucun trajet
        // d'une autre compagnie ne devrait être sélectionnable depuis l'UI).
        if (! $trajet || $trajet->id_compagnie !== Compagnie::site()->id_compagnie) {
            return response()->json(['ok' => false, 'message' => 'Trajet introuvable.'], 404);
        }

        $escales = [];
        if ($trajet->escales_avec_frais) {
            foreach (explode(', ', $trajet->escales_avec_frais) as $escale) {
                if (preg_match('/^(.*?)\s*\((\d+)\s*FCFA\)/', $escale, $matches)) {
                    $escales[] = ['ville' => trim($matches[1]), 'prix' => (int) $matches[2]];
                }
            }
        }

        return response()->json([
            'ok' => true,
            'idProgrammer' => $trajet->idProgrammer,
            'id_compagnie' => $trajet->id_compagnie,
            'departLocalite' => $trajet->departLocalite,
            'destinationLocalite' => $trajet->destinationLocalite,
            'numeroGare1' => $trajet->numeroGare1,
            'numeroGare2' => $trajet->numeroGare2,
            'heureDepart' => $trajet->heureDepart,
            'prix' => (int) $trajet->prix,
            'escales' => $escales,
        ]);
    }

    public function store(Request $request, ReservationEnLigneService $service): JsonResponse
    {
        try {
            $resultat = $service->creerReservation([
            'id_programme' => $request->input('id_programme'),
            'departId' => $request->input('departId'),
            'destinationId' => $request->input('destinationId'),
            'id_compagnie' => $request->input('id_compagnie'),
            'numeroGare' => $request->input('numeroGare'),
            'escale_finale' => $request->input('escale_finale'),
            'jourVoyage' => $request->input('jourVoyage'),
            'heureDepart' => $request->input('Heur_departs'),
            'Client' => $request->input('Client'),
            'nombrePassages' => $request->input('nombrePassages'),
            'numeroClient' => $request->input('numeroClient'),
            'numeroPaiement' => $request->input('numeroPaiement'),
            'emailClient' => $request->input('emailClient'),
        ]);

        if (! $resultat['ok']) {
            return response()->json(['ok' => false, 'message' => $resultat['message']], 422);
        }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if ($msg === 'AUCUN_CAR_PROGRAMME') {
                $erreur = "Aucun car n'est encore programmé pour ce trajet aujourd'hui à cette heure. Veuillez choisir un autre trajet ou essayer plus tard.";
            } elseif (str_starts_with($msg, 'PLACES_INSUFFISANTES')) {
                $erreur = "Il n'y a plus assez de places disponibles pour ce trajet.";
            } elseif ($msg === 'CAR_INTROUVABLE') {
                $erreur = "Le car affecté à ce trajet est introuvable.";
            } elseif ($msg === 'PLACE_MINIMALE_NON_DEFINIE') {
                $erreur = "La configuration des places (quota en ligne) n'est pas définie pour cette compagnie.";
            } else {
                // Pour le débogage (ex: SQL errors) si besoin, on le retourne
                $erreur = "Une erreur est survenue : " . $msg;
            }
            return response()->json(['ok' => false, 'message' => $erreur], 422);
        }

        $message = match ($resultat['email_envoye']) {
            true => "Un email de confirmation vous a été envoyé. Payez via Orange Money dans les 30 minutes, sinon la réservation sera automatiquement annulée.",
            false => "L'email de confirmation n'a pas pu être envoyé. Payez via Orange Money dans les 30 minutes, sinon la réservation sera automatiquement annulée.",
            default => "Payez via Orange Money dans les 30 minutes, sinon la réservation sera automatiquement annulée.",
        };

        return response()->json([
            'ok' => true,
            'message' => $message,
            'numeroBillets' => $resultat['numeroBillets'],
            'redirect' => route('site.billet', $resultat['numeroBillets']),
        ]);
    }

    // Fiche publique du billet (numéro de billet = clé, comme un ticket de cinéma —
    // aucune vérification d'identité supplémentaire, fidèle au legacy). Sert de page de
    // confirmation après réservation et de lien "billet_pdf" envoyé par email/WhatsApp.
    public function billet(string $numeroBillets): View
    {
        $billet = Billet::with('client')
            ->where('numeroBillets', $numeroBillets)
            ->firstOrFail();

        $compagnie = \App\Models\Compagnie::find($billet->id_compagnie);

        return view('site.billet', ['billet' => $billet, 'compagnie' => $compagnie]);
    }
}
