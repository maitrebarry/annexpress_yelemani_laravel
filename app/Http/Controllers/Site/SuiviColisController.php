<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Colis;
use App\Models\Compagnie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/site/Suivis_colis.php — suivi public d'un
 * colis par code + compagnie (les deux sont requis : le code seul ne suffit pas à
 * retrouver un colis, pour éviter qu'un visiteur énumère les colis d'une compagnie qu'il
 * n'a pas choisie).
 */
class SuiviColisController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $compagnies = Compagnie::orderBy('nom_compagnie')->get();
        $colis = null;
        $erreur = null;

        // Étape 1 (soumission du formulaire) : vérifie l'appartenance puis redirige vers
        // ?show_code=...&id_compagnie=... (PRG — évite le renvoi de formulaire au rechargement
        // et donne une URL de résultat propre/partageable).
        if ($request->filled('code_colis') && $request->filled('id_compagnie')) {
            $codeColis = trim((string) $request->query('code_colis'));
            $idCompagnie = (int) $request->query('id_compagnie');

            $existe = Colis::where('code_colis', $codeColis)->where('id_compagnie', $idCompagnie)->exists();

            if ($existe) {
                return redirect()->route('site.suivi-colis', ['show_code' => $codeColis, 'id_compagnie' => $idCompagnie]);
            }

            $erreur = "Ce colis n'appartient pas à la compagnie sélectionnée.";
        }

        // Étape 2 (URL de résultat) : revérifie l'appartenance à la compagnie (jamais un
        // simple lookup par code seul) avant d'afficher les détails.
        if ($request->filled('show_code') && $request->filled('id_compagnie')) {
            $colis = Colis::query()
                ->avecDetails()
                ->where('colis.code_colis', trim((string) $request->query('show_code')))
                ->where('colis.id_compagnie', (int) $request->query('id_compagnie'))
                ->first();
        }

        return view('site.suivi-colis', [
            'compagnies' => $compagnies,
            'colis' => $colis,
            'erreur' => $erreur,
            'idCompagnieSelectionnee' => $request->query('id_compagnie'),
            'codeColisSaisi' => $request->query('code_colis'),
        ]);
    }
}
