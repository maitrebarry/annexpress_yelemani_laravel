<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartenaireCompte;
use App\Models\PartenaireMessage;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port de la partie admin de Projets_licence/app/models/Partenaire.php
 * (getTousAvecApercu/countEnAttenteReponse) + Partenaire_message — écran "Demandes de
 * partenariat" du sidebar (lien déjà présent avant ce chantier, jamais construit :
 * `App\Services\NavAlertsService::countPartenairesEnAttente()` existait déjà côté badge,
 * mais `/admin/Partenariats` menait à un 404). Contrepartie côté admin de
 * Site\PartenaireController — sans cet écran, un partenaire pourrait s'inscrire et
 * écrire mais personne ne pourrait jamais lui répondre depuis l'interface.
 *
 * Réservé super_admin, comme dans le sidebar (pas de permission dédiée : le legacy non
 * plus n'avait aucune permission spécifique pour cet écran).
 */
class PartenariatController extends Controller
{
    public function index(): View
    {
        $partenaires = PartenaireCompte::query()
            ->withCount('messages')
            ->get()
            ->map(function (PartenaireCompte $p) {
                $dernier = $p->dernierMessage();
                $p->dernier_message = $dernier?->message;
                $p->dernier_auteur = $dernier?->auteur;
                $p->date_dernier_message = $dernier?->date_envoi;
                $p->en_attente_reponse = $dernier?->auteur === 'partenaire';

                return $p;
            })
            ->sortByDesc(fn (PartenaireCompte $p) => $p->date_dernier_message ?? $p->date_creation)
            ->values();

        return view('admin.partenariat.index', ['partenaires' => $partenaires]);
    }

    public function repondre(Request $request): RedirectResponse
    {
        $idPartenaire = (int) $request->input('id_partenaire');
        $texte = trim((string) $request->input('message'));

        if (! PartenaireCompte::where('id_partenaire', $idPartenaire)->exists()) {
            Flash::set('Partenaire introuvable.', 'danger');

            return redirect()->route('admin.partenariat.index');
        }

        if ($texte === '') {
            Flash::set('Le message ne peut pas être vide.', 'danger');

            return redirect()->route('admin.partenariat.index');
        }

        PartenaireMessage::create([
            'id_partenaire' => $idPartenaire,
            'auteur' => 'admin',
            'message' => $texte,
            'date_envoi' => now(),
        ]);

        Flash::set('Réponse envoyée avec succès.', 'success');

        return redirect()->route('admin.partenariat.index');
    }
}
