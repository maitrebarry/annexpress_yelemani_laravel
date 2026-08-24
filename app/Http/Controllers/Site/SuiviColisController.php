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
 * colis par code.
 *
 * Le site public est dédié à une seule compagnie (voir App\Models\Compagnie::site() /
 * config/site.php, décision du 2026-08-24) : la sélection de compagnie a disparu du
 * formulaire (il n'y en a plus qu'une), `id_compagnie` est forcé côté serveur plutôt que
 * lu depuis la requête — même garde-fou qu'avant (le lookup vérifie toujours code + compagnie,
 * jamais le code seul), juste avec une seule compagnie possible désormais.
 */
class SuiviColisController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $idCompagnie = Compagnie::site()->id_compagnie;
        $colis = null;
        $erreur = null;

        // Étape 1 (soumission du formulaire) : vérifie l'appartenance puis redirige vers
        // ?show_code=... (PRG — évite le renvoi de formulaire au rechargement et donne une
        // URL de résultat propre/partageable).
        if ($request->filled('code_colis')) {
            $codeColis = trim((string) $request->query('code_colis'));

            $existe = Colis::where('code_colis', $codeColis)->where('id_compagnie', $idCompagnie)->exists();

            if ($existe) {
                return redirect()->route('site.suivi-colis', ['show_code' => $codeColis]);
            }

            $erreur = "Ce colis n'existe pas.";
        }

        // Étape 2 (URL de résultat) : revérifie l'appartenance à la compagnie (jamais un
        // simple lookup par code seul) avant d'afficher les détails.
        if ($request->filled('show_code')) {
            $colis = Colis::query()
                ->avecDetails()
                ->where('colis.code_colis', trim((string) $request->query('show_code')))
                ->where('colis.id_compagnie', $idCompagnie)
                ->first();
        }

        return view('site.suivi-colis', [
            'colis' => $colis,
            'erreur' => $erreur,
            'codeColisSaisi' => $request->query('code_colis'),
        ]);
    }
}
