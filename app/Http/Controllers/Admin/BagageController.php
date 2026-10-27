<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bagage;
use App\Models\Billet;
use App\Services\CaisseUtilisateurService;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Bagages.php +
 * app/models/Bagage.php::ajouterApresCoup() — ajout de bagage(s) à un billet DÉJÀ vendu
 * (depuis la liste/l'historique des billets, pour un passager qui revient avec des
 * bagages). Crédite la caisse de l'agent CONNECTÉ, pas forcément celui qui a vendu le
 * billet initial — mirroir de TransfertGareController (transaction autonome, pas de
 * service dédié pour une opération aussi ponctuelle).
 */
class BagageController extends Controller
{
    public function ajouter(int $idBillets): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $billet = Billet::query()
            ->join('client', 'billets.id_client', '=', 'client.idClient')
            ->where('billets.idBillets', $idBillets)
            ->where('billets.id_compagnie', $user->id_compagnie)
            ->first(['billets.*', 'client.Client']);

        if (! $billet) {
            Flash::set('Billet introuvable.', 'danger');

            return redirect()->route('admin.billet.index');
        }

        return view('admin.bagage.ajouter', [
            'billet' => $billet,
            'bagages' => Bagage::getBagagesPourBillet($idBillets, $user->id_compagnie),
            'totalBagages' => Bagage::getTotalBagages($idBillets, $user->id_compagnie),
        ]);
    }

    public function enregistrer(Request $request, int $idBillets, CaisseUtilisateurService $caisseService): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $billet = Billet::where('idBillets', $idBillets)->where('id_compagnie', $user->id_compagnie)->first();
        if (! $billet) {
            Flash::set('Billet introuvable.', 'danger');

            return redirect()->route('admin.billet.index');
        }

        $descriptions = (array) $request->input('bagage_description', []);
        $poidsListe = (array) $request->input('bagage_poids', []);
        $prixListe = (array) $request->input('bagage_prix', []);

        $lignesValides = [];
        foreach ($prixListe as $i => $prix) {
            if ($prix === '' || $prix === null) {
                continue;
            }
            $prix = (float) $prix;
            if ($prix < 0) {
                Flash::set("Le prix d'un bagage ne peut pas être négatif.", 'danger');

                return redirect()->route('admin.bagage.ajouter', $idBillets);
            }
            $poids = $poidsListe[$i] ?? null;
            if ($poids !== null && $poids !== '' && (float) $poids < 0) {
                Flash::set("Le poids d'un bagage ne peut pas être négatif.", 'danger');

                return redirect()->route('admin.bagage.ajouter', $idBillets);
            }
            $lignesValides[] = [
                'description' => $descriptions[$i] ?? null,
                'poids' => ($poids !== null && $poids !== '') ? $poids : null,
                'prix' => $prix,
            ];
        }

        if (empty($lignesValides)) {
            Flash::set('Veuillez saisir au moins un prix de bagage.', 'danger');

            return redirect()->route('admin.bagage.ajouter', $idBillets);
        }

        try {
            DB::transaction(function () use ($lignesValides, $billet, $user, $caisseService) {
                $total = 0.0;
                foreach ($lignesValides as $ligne) {
                    Bagage::create([
                        'idBillets' => $billet->idBillets,
                        'description' => $ligne['description'],
                        'poids' => $ligne['poids'],
                        'prix' => $ligne['prix'],
                        'id_compagnie' => $user->id_compagnie,
                        'id_utilisateur' => $user->idUser,
                    ]);
                    $total += $ligne['prix'];
                }

                $creditOk = $caisseService->crediterBagage($user->idUser, $total, $billet->numeroBillets, $billet->idBillets);
                if ($creditOk === false) {
                    throw new \RuntimeException('CAISSE_FERMEE');
                }
            });
        } catch (\RuntimeException $e) {
            Flash::set("Opération bloquée : Aucune caisse ouverte pour votre compte. Veuillez ouvrir votre caisse d'abord.", 'danger');

            return redirect()->route('admin.bagage.ajouter', $idBillets);
        }

        Flash::set('Bagage(s) ajouté(s) et caisse alimentée.', 'success');

        return redirect()->route('admin.bagage.ajouter', $idBillets);
    }
}
