<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PartenaireCompte;
use App\Models\PartenaireMessage;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/site/EspacePartenaire.php — connexion/
 * inscription (guard `partenaire`, voir config/auth.php) + messagerie avec l'admin.
 * L'inscription crée le compte directement, pas d'étape d'approbation côté legacy.
 */
class PartenaireController extends Controller
{
    public function login(): View|RedirectResponse
    {
        if (Auth::guard('partenaire')->check()) {
            return redirect()->route('site.partenaire.discussion');
        }

        return view('site.partenaire.login');
    }

    public function connexion(Request $request): RedirectResponse
    {
        $email = trim((string) $request->input('email'));
        $motDePasse = (string) $request->input('mot_de_passe');

        if (! Auth::guard('partenaire')->attempt(['email' => $email, 'password' => $motDePasse])) {
            Flash::set('Email ou mot de passe incorrect.', 'danger');

            return redirect()->route('site.partenaire.login');
        }

        return redirect()->route('site.partenaire.discussion');
    }

    public function inscription(Request $request): RedirectResponse
    {
        $nomCompagnie = trim((string) $request->input('nom_compagnie'));
        $email = trim((string) $request->input('email_inscription'));
        $telephone = trim((string) $request->input('telephone'));
        $motDePasse = (string) $request->input('mot_de_passe_inscription');

        if ($nomCompagnie === '' || $email === '' || $motDePasse === '') {
            Flash::set('Veuillez remplir tous les champs obligatoires.', 'danger');

            return redirect()->route('site.partenaire.login');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set("L'adresse email n'est pas valide.", 'danger');

            return redirect()->route('site.partenaire.login');
        }

        if (PartenaireCompte::where('email', $email)->exists()) {
            Flash::set('Un compte existe déjà avec cet email. Connectez-vous plutôt.', 'danger');

            return redirect()->route('site.partenaire.login');
        }

        $partenaire = PartenaireCompte::create([
            'nom_compagnie' => $nomCompagnie,
            'email' => $email,
            'mot_de_passe' => Hash::make($motDePasse),
            'telephone' => $telephone ?: null,
            'date_creation' => now(),
        ]);

        Auth::guard('partenaire')->login($partenaire);

        return redirect()->route('site.partenaire.discussion');
    }

    public function discussion(): View
    {
        $partenaire = Auth::guard('partenaire')->user();

        return view('site.partenaire.discussion', [
            'partenaire' => $partenaire,
            'messages' => PartenaireMessage::where('id_partenaire', $partenaire->id_partenaire)
                ->orderBy('date_envoi')
                ->get(),
        ]);
    }

    public function envoyerMessage(Request $request): RedirectResponse
    {
        $texte = trim((string) $request->input('message'));

        if ($texte !== '') {
            PartenaireMessage::create([
                'id_partenaire' => Auth::guard('partenaire')->id(),
                'auteur' => 'partenaire',
                'message' => $texte,
                'date_envoi' => now(),
            ]);
        }

        return redirect()->route('site.partenaire.discussion');
    }

    public function deconnexion(): RedirectResponse
    {
        Auth::guard('partenaire')->logout();

        return redirect()->route('site.compagnies');
    }
}
