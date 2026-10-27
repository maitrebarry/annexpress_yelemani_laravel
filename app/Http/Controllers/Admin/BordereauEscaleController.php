<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Compagnie;
use App\Models\Utilisateur;
use App\Services\BordereauEscaleService;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Bordereau_escales.php +
 * app/models/Bordereau_escale.php — saisie dans le système des bordereaux d'escale
 * papier (escales isolées sans électricité ni réseau). Réutilise entièrement les écrans
 * caisse existants (Ma Caisse, Supervision Escale, Rapport Compagnie, versement) : la
 * caisse créée ici y apparaît comme n'importe quelle caisse fermée, sans aucune
 * modification de ces écrans.
 */
class BordereauEscaleController extends Controller
{
    // Même logique que CaisseController::resolveGareEscale() — dupliquée ici comme dans
    // le legacy (Bordereau_escales::resolveGareEscale() y duplique déjà
    // Caisse::resolveGareEscale() à l'identique) : Admin choisit la gare dans le
    // formulaire/la requête, les autres rôles sont limités à leur propre gare de session.
    private function resolveGareEscale(Utilisateur $user, Request $request): array
    {
        if ($user->droit !== 'Admin') {
            return [$user->id_agence, false, collect()];
        }

        $listeAgences = Agence::where('id_compagnie', $user->id_compagnie)->orderBy('localite')->get();

        $idAgencePoste = (int) ($request->input('id_agence') ?? 0);
        if ($idAgencePoste && ! $listeAgences->contains('idAgence', $idAgencePoste)) {
            $idAgencePoste = 0;
        }

        return [$idAgencePoste ?: null, true, $listeAgences];
    }

    public function index(Request $request, BordereauEscaleService $service): View
    {
        $user = Auth::guard('staff')->user();
        [$idAgence, $estAdmin, $listeAgences] = $this->resolveGareEscale($user, $request);
        $date = $request->input('date');

        return view('admin.bordereau-escale.index', [
            'bordereaux' => $service->getBordereaux($user->id_compagnie, $idAgence, $date),
            'date' => $date,
            'estAdmin' => $estAdmin,
            'listeAgences' => $listeAgences,
            'idAgenceSelectionnee' => $idAgence,
        ]);
    }

    public function ajouter(Request $request): View
    {
        $user = Auth::guard('staff')->user();
        [, $estAdmin, $listeAgences] = $this->resolveGareEscale($user, $request);

        return view('admin.bordereau-escale.ajouter', [
            'estAdmin' => $estAdmin,
            'listeAgences' => $listeAgences,
        ]);
    }

    public function enregistrer(Request $request, BordereauEscaleService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $idAgence = $user->droit === 'Admin' ? $request->input('id_agence') : $user->id_agence;

        $resultat = $service->saveBordereau($user, array_merge($request->all(), ['id_agence' => $idAgence]));

        if (! $resultat['ok']) {
            Flash::set($resultat['message'], 'danger');

            return redirect()->route('admin.bordereau-escale.ajouter');
        }

        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.bordereau-escale.detail', $resultat['idBordereau']);
    }

    public function detail(int $id, BordereauEscaleService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        $bordereau = $service->getBordereauDetail($id, $user->id_compagnie);

        if (! $bordereau) {
            Flash::set('Bordereau introuvable.', 'danger');

            return redirect()->route('admin.bordereau-escale.index');
        }

        return view('admin.bordereau-escale.detail', ['bordereau' => $bordereau]);
    }

    // Bordereau VIERGE (lignes vides) à imprimer et distribuer aux escales sans réseau,
    // pour saisie ultérieure via enregistrer(). Même pattern dompdf A4 que
    // SalaireController::telechargerBulletin().
    public function telechargerModele(): \Illuminate\Http\Response
    {
        $user = Auth::guard('staff')->user();
        $compagnie = Compagnie::find($user->id_compagnie);

        $logoPath = null;
        if ($compagnie?->logo) {
            $candidat = public_path('images/logos/'.$compagnie->logo);
            if (is_file($candidat)) {
                $logoPath = $candidat;
            }
        }

        $html = view('admin.pdf.bordereau-escale-modele', ['compagnie' => $compagnie, 'logoPath' => $logoPath])->render();

        $options = new \Dompdf\Options();
        $options->setChroot(public_path());
        $options->setIsRemoteEnabled(true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="bordereau_escale_modele.pdf"',
        ]);
    }
}
