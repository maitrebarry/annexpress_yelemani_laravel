<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GaresController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('staff')->user();

        if ($user->isSuperAdmin()) {
            $listes = Agence::all();
        } elseif (in_array($user->droit, ['Admin', 'PDG'], true) && $user->id_compagnie) {
            $listes = Agence::where('id_compagnie', $user->id_compagnie)->get();
        } else {
            Flash::set('Accès restreint ou données manquantes', 'danger');
            $listes = collect();
        }

        // Rejoue la modal d'ajout pré-remplie après une tentative en échec (POST -> Redirect
        // -> GET) : les lignes soumises sont passées une seule fois via la session flash.
        $lignesEnErreur = $request->session()->get('gares_lignes_en_erreur', []);

        return view('admin.gares.index', ['listes' => $listes, 'lignesEnErreur' => $lignesEnErreur]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->has('enregistre')) {
            $lignesEnErreur = $this->saveGares($request);
            if (! empty($lignesEnErreur)) {
                $request->session()->flash('gares_lignes_en_erreur', $lignesEnErreur);
            }
        }

        return redirect('/admin/Liste_gares');
    }

    private function saveGares(Request $request): array
    {
        $user = Auth::guard('staff')->user();
        $idCompagnie = $user->id_compagnie;

        $localites = (array) $request->input('localite', []);
        $numeroGares = (array) $request->input('numeroGare', []);
        $codes = (array) $request->input('code', []);
        $tels = (array) $request->input('tel', []);

        $lignes = [];
        $combosVues = [];
        $telsVus = [];
        $codesVus = [];
        $auMoinsUneLigneSaisie = false;

        foreach ($localites as $index => $localite) {
            $localite = trim($localite);
            $numeroGare = trim($numeroGares[$index] ?? '');
            $code = trim($codes[$index] ?? '');
            $tel = trim($tels[$index] ?? '');

            if ($localite === '' && $numeroGare === '' && $code === '' && $tel === '') {
                continue;
            }
            $auMoinsUneLigneSaisie = true;

            $champsEnErreur = [];
            $messages = [];

            foreach (compact('localite', 'numeroGare', 'code', 'tel') as $champ => $valeur) {
                if ($valeur === '') {
                    $champsEnErreur[] = $champ;
                }
            }
            if (! empty($champsEnErreur)) {
                $messages[] = 'Tous les champs sont obligatoires.';
            }

            if (empty($champsEnErreur)) {
                $combo = $localite.'|'.$numeroGare;

                if (str_starts_with($code, '-')) {
                    $champsEnErreur[] = 'code';
                    $messages[] = 'Le code marchand ne peut pas commencer par un signe négatif.';
                }
                if (in_array($combo, $combosVues, true)) {
                    $champsEnErreur[] = 'localite';
                    $champsEnErreur[] = 'numeroGare';
                    $messages[] = "« $localite / $numeroGare » est en double dans les lignes saisies.";
                }
                if (in_array($tel, $telsVus, true)) {
                    $champsEnErreur[] = 'tel';
                    $messages[] = "Le numéro « $tel » est en double dans les lignes saisies.";
                }
                if (in_array($code, $codesVus, true)) {
                    $champsEnErreur[] = 'code';
                    $messages[] = "Le code marchand « $code » est en double dans les lignes saisies.";
                }

                if (empty($champsEnErreur)) {
                    $existeCombo = Agence::where('localite', $localite)
                        ->where('numeroGare', $numeroGare)
                        ->where('id_compagnie', $idCompagnie)
                        ->exists();
                    if ($existeCombo) {
                        $champsEnErreur[] = 'localite';
                        $champsEnErreur[] = 'numeroGare';
                        $messages[] = "« $localite / $numeroGare » existe déjà dans cette localité.";
                    }
                    if (Agence::where('tel', $tel)->exists()) {
                        $champsEnErreur[] = 'tel';
                        $messages[] = "Le numéro « $tel » est déjà utilisé.";
                    }
                    if (Agence::where('code', $code)->exists()) {
                        $champsEnErreur[] = 'code';
                        $messages[] = "Le code marchand « $code » est déjà utilisé.";
                    }
                }

                if (empty($champsEnErreur)) {
                    $combosVues[] = $combo;
                    $telsVus[] = $tel;
                    $codesVus[] = $code;
                }
            }

            $lignes[] = [
                'localite' => $localite,
                'numeroGare' => $numeroGare,
                'code' => $code,
                'tel' => $tel,
                'champs_en_erreur' => array_values(array_unique($champsEnErreur)),
                'erreur' => implode(' ', array_unique($messages)),
            ];
        }

        if (! $auMoinsUneLigneSaisie) {
            Flash::set('Aucune gare à ajouter.', 'danger');

            return [];
        }

        $yADesErreurs = collect($lignes)->contains(fn ($ligne) => ! empty($ligne['champs_en_erreur']));

        if ($yADesErreurs) {
            Flash::set("Corrigez les champs en rouge avant d'enregistrer.", 'danger');

            return $lignes;
        }

        try {
            DB::transaction(function () use ($lignes, $idCompagnie) {
                foreach ($lignes as $ligne) {
                    Agence::create([
                        'code' => $ligne['code'],
                        'localite' => $ligne['localite'],
                        'numeroGare' => $ligne['numeroGare'],
                        'tel' => $ligne['tel'],
                        'id_compagnie' => $idCompagnie,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Flash::set("Échec de l'enregistrement, rien n'a été ajouté. Réessayez.", 'danger');

            return $lignes;
        }

        $nb = count($lignes);
        Flash::set($nb > 1 ? "$nb gares ajoutées avec succès." : 'Gare ajoutée avec succès.', 'success');

        return [];
    }

    public function update(Request $request): RedirectResponse
    {
        if ($request->has('edit')) {
            $user = Auth::guard('staff')->user();

            $query = Agence::where('idAgence', $request->input('idAgence'));
            if (! $user->isSuperAdmin()) {
                $query->where('id_compagnie', $user->id_compagnie);
            }

            $modification = $query->update([
                'numeroGare' => $request->input('numeroGare'),
                'localite' => $request->input('localite'),
                'code' => $request->input('code'),
                'tel' => $request->input('tel'),
            ]);

            Flash::set(
                $modification ? 'Modification effectuée avec succès' : 'Echec de la modification',
                $modification ? 'primary' : 'danger'
            );
        }

        return redirect('/admin/Liste_gares');
    }

    public function suspend(int $idAgence): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $query = Agence::where('idAgence', $idAgence);
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $agence = $query->first();

        if ($agence) {
            $newStatus = $agence->status == 1 ? 0 : 1;
            $agence->update(['status' => $newStatus]);

            Flash::set(
                $newStatus == 1 ? 'Gare activée avec succès.' : 'Gare suspendue avec succès.',
                $newStatus == 1 ? 'success' : 'warning'
            );
        }

        return redirect('/admin/Liste_gares');
    }

    public function destroy(int $idAgence): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $query = Agence::where('idAgence', $idAgence);
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $agence = $query->first();

        if (! $agence) {
            Flash::set('Gare introuvable.', 'danger');

            return redirect('/admin/Liste_gares');
        }

        $hasBillets = DB::table('billets')->where('num_gare', $agence->numeroGare)->exists();
        $hasColis = DB::table('colis')->where('num_gare', $agence->numeroGare)->orWhere('id_agence', $idAgence)->exists();
        $hasCaisse = DB::table('caisse')->where('id_agence', $idAgence)->exists();

        if (! $hasBillets && ! $hasColis && ! $hasCaisse) {
            $agence->delete();
            Flash::set('Gare supprimée avec succès.', 'success');
        } else {
            Flash::set('Impossible de supprimer cette gare car elle a déjà des actions enregistrées.', 'danger');
        }

        return redirect('/admin/Liste_gares');
    }
}
